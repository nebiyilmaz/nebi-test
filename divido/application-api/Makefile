SHELL:=/bin/bash

DOCKER_PASSWORD = $(shell docker run --rm \
							-e AWS_ACCESS_KEY_ID=${ORG_AWS_ACCESS_KEY_ID} \
							-e AWS_SECRET_ACCESS_KEY=${ORG_AWS_SECRET_ACCESS_KEY} \
							-e AWS_DEFAULT_REGION=eu-west-1 \
							amazon/aws-cli ecr get-login-password --region eu-west-1) 

export SERVICE_NAME:=application-api
export SERVICE_NAME_RPC:=$(SERVICE_NAME)-rpc
#export NETWORK:=$(SERVICE_NAME)-net
# The following network name is required so the service can be run and be used with Microservices Environment.
export NETWORK:=environment_divido-environment-platform
export DIVIDO_APPLICATION_ENVIRONMENT:=development
export DIVIDO_SHORT_APPLICATION_ENVIRONMENT:=dev
export MINIMUM_TEST_COVERAGE:=53


default: ci-analyze ci-test ci-check-coverage ci-clean

prepare: ci-clean
	docker network create $(NETWORK) || true
	@docker build -t $(SERVICE_NAME)-composer --target composer --build-arg GITHUB_TOKEN=$(GITHUB_TOKEN) -f .divido/docker/fpm/Dockerfile .

composer-install: prepare
	docker run -v $(PWD):/opt/divido/app $(SERVICE_NAME)-composer composer install

composer-update: prepare
	docker run -v $(PWD):/opt/divido/app $(SERVICE_NAME)-composer composer update

security-check:
	docker run --rm -v $(PWD):/app -w /app symfonycorp/cli check:security

ci-clean:
	docker-compose  \
		-f .divido/development/compose.yml \
		-f .divido/development/tests.yml \
		rm --force -v
	docker network rm $(NETWORK) || true
	rm -rf tmp/migrations || true

ci-fmt:
	docker run --rm -v $(PWD):/project \
		-e FOLDERS=src,tests \
		-e ALLOW_RISKY=yes \
		divido/devtools:php-fmt

ci-test: composer-install
	#cp -n ./config.development.json ./config.json || true
	#docker-compose -f .divido/development/compose.yml build
	#docker-compose -f .divido/development/compose.yml up --force-recreate --abort-on-container-exit
	# @TODO: The following is going to be removed once the tests orchestration is migrated to the main docker compose file.
	@docker network create dft-application-api-net || true
	cp ./tests/functional/config.test.jenkins.json ./tests/functional/config.json || true
	docker-compose -f ./tests/functional/docker/platform_db_subset.yml -p dft-application-api-platform-db up --build -d
	docker-compose -f ./tests/functional/docker/app.yml -p dft-application-api-app up --build -d
	docker-compose -f ./tests/functional/docker/fake_server.yml -p dft-application-api-fake-server up --build -d
	docker-compose -f ./tests/functional/docker/testrunner.yml -p dft-application-api-testrunner up --build --exit-code-from dft-application-api-testrunner

ci-check-coverage:
	@docker run --rm \
		-e MINIMUM_COVERAGE=${MINIMUM_TEST_COVERAGE} \
		-e README_FILE=/app/README.md \
		-e COVERAGE_FILE=/app/tmp/coverage.out \
		-e GITHUB_TOKEN=${GITHUB_TOKEN} \
		-v $(PWD):/app \
		divido/devtools:php-coverage

ci-analyze: composer-install security-check
	docker run --rm \
		-v $(PWD):/project \
		-w /project \
		phpstan/phpstan analyze -c phpstan.neon

# The following env variables are defined in the github action workflow when creating a new semver release.
# - RELEASE_VERSION
# - ECR_REGISTRY
# - ORG_AWS_ACCESS_KEY_ID
# - ORG_AWS_SECRET_ACCESS_KEY

ci-build:
	@docker build \
		--label "divido.com.container.version=${RELEASE_VERSION}" \
		--build-arg GITHUB_TOKEN=${GITHUB_TOKEN} \
		--target final \
		-t ${SERVICE_NAME}:${RELEASE_VERSION} \
		-f .divido/docker/fpm/Dockerfile \
		.
	@docker build \
		--label "divido.com.container.version=${RELEASE_VERSION}" \
		--build-arg GITHUB_TOKEN=${GITHUB_TOKEN} \
		--target final \
		-t ${SERVICE_NAME_RPC}:${RELEASE_VERSION} \
		-f .divido/docker/rpc/Dockerfile \
		.
	docker tag ${SERVICE_NAME}:${RELEASE_VERSION} ${ECR_REGISTRY}/${SERVICE_NAME}:${RELEASE_VERSION}
	docker tag ${SERVICE_NAME_RPC}:${RELEASE_VERSION} ${ECR_REGISTRY}/${SERVICE_NAME_RPC}:${RELEASE_VERSION}
	if [[ ${RELEASE_VERSION} =~ ^v[0-9]+.[0-9]+.[0-9]+$$ ]]; then \
		docker tag ${SERVICE_NAME}:${RELEASE_VERSION} ${ECR_REGISTRY}/${SERVICE_NAME}:latest; \
		docker tag ${SERVICE_NAME_RPC}:${RELEASE_VERSION} ${ECR_REGISTRY}/${SERVICE_NAME_RPC}:latest; \
	fi

ci-push: build-chart-templates
	@docker login --username AWS --password $(DOCKER_PASSWORD) ${ECR_REGISTRY}
	docker push ${ECR_REGISTRY}/${SERVICE_NAME}:${RELEASE_VERSION}
	docker push ${ECR_REGISTRY}/${SERVICE_NAME_RPC}:${RELEASE_VERSION}
	if [[ ${RELEASE_VERSION} =~ ^v[0-9]+.[0-9]+.[0-9]+$$ ]]; then \
		docker push ${ECR_REGISTRY}/${SERVICE_NAME}:latest; \
		docker push ${ECR_REGISTRY}/${SERVICE_NAME_RPC}:latest; \
	fi
	
	# Build & Release Helm Chart
	docker run --rm \
		-e "CHARTMUSEUM_TOKEN=${ORG_CHARTMUSEUM_JWT}" \
		-e "SERVICE_NAME=$(SERVICE_NAME)" \
		-e "RELEASE_VERSION=${RELEASE_VERSION}" \
		-e "HELM_REPOSITORY=internal" \
		-v ${PWD}/.divido/helm:/app ${ECR_REGISTRY}/service-chart-builder:1.0.0

	docker logout ${ECR_REGISTRY}
	
build-chart-templates: 
	mkdir .divido/helm/templates || true
	@docker login --username AWS --password $(DOCKER_PASSWORD) ${ECR_REGISTRY}
	docker run --rm \
		-e "SERMAN_RESOURCES=deployment,service,serviceaccount" \
		-v ${PWD}/.divido/helm/templates:/output \
		${ECR_REGISTRY}/serman:${SERMAN_VERSION}

#############################
# D O C U M E N T A T I O N #
#############################

generate-documentation:
	mkdir -p $(PWD)/.divido || true
	docker run -v $(PWD):/app -it tico/swagger-php src -o ./.divido/openapi-v3-0-0.json

docs: generate-documentation
	docker run --rm -it \
		-p 80:8080 \
		-v $(PWD)/.divido/openapi-v3-0-0.json:/usr/share/nginx/html/swagger.json \
		-v $(PWD)/src:/usr/share/nginx/html/src \
		-e URL=/swagger.json swaggerapi/swagger-ui

up: composer-install
	cp -n ./config.development.json ./config.json || true
	docker-compose \
		-f .divido/development/compose.yml \
		-p $(SERVICE_NAME) \
		up -d --force-recreate --build
	make kong-register

down:
	docker-compose \
		-f .divido/development/compose.yml \
		-p $(SERVICE_NAME) \
		down

kong-register: # Use this to register in Kong when run with Microservices Environment.
	.divido/development/kong/register.sh
