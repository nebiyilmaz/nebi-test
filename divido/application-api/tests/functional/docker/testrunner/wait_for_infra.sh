#!/bin/sh


check_container_state()
{
	CONTAINER_NAME=$1
	CONTAINER_STATE=$2
	MAX_CHECKS=10
	TIME_BETWEEN_CHECKS=2

	CONTAINER_READY=0

	echo "waiting for ${CONTAINER_NAME} to have a state of \"${CONTAINER_STATE}\"..."
	INCR=0

	until [ ! $CONTAINER_READY -eq 0 ]; do
		INCR=$((INCR+1))
		#echo "attempt ${INCR} of ${MAX_CHECKS}..."

		state=$(curl -s --unix-socket /var/run/docker.sock http:/docker/containers/${CONTAINER_NAME}/json | jq -r '.State.Status')
		echo $state
		if [ "$state" = "$CONTAINER_STATE" ]; then
  			CONTAINER_READY=1
		fi

		if [ $INCR -gt $MAX_CHECKS ]; then
  			echo "not ready after ${$MAX_CHECKS} checks... exiting"
  			exit 1
  		fi

  		if [ ! "$state" = "$CONTAINER_STATE" ]; then
  			sleep ${TIME_BETWEEN_CHECKS}
		fi

	done
}

check_container_health()
{
	CONTAINER_NAME=$1
	CONTAINER_READY=0
	MAX_CHECKS=10
	TIME_BETWEEN_CHECKS=2

	echo "waiting for ${CONTAINER_NAME} to be healthy..."
	INCR=0

	until [ ! $CONTAINER_READY -eq 0 ]; do
		INCR=$((INCR+1))
		#echo "attempt ${INCR} of 10..."

		health=$(curl -s --unix-socket /var/run/docker.sock http:/docker/containers/${CONTAINER_NAME}/json | jq -r '.State.Health.Status')

		if [ "$health" = "healthy" ]; then
  			CONTAINER_READY=1
  			break
		fi

		if [ $INCR -gt $MAX_CHECKS ]; then
  			echo "not healthy after ${MAX_CHECKS} checks... exiting"
  			exit 1
  		fi

		if [ ! "$health" = "healthy" ]; then
  			sleep ${TIME_BETWEEN_CHECKS}
		fi

	done
}

check_container_state dft-application-api-app running

check_container_state dft-application-api-fake-server running

check_container_state dft-application-api-platform-db running

check_container_state dft-application-api-platform-flyway exited

cd /opt/divido/app && ./vendor/phpunit/phpunit/phpunit tests --stop-on-error --stop-on-failure --coverage-text=/opt/divido/app/tmp/coverage.out || exit 1
echo 'DONE';
