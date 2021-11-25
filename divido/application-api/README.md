# application-api

[![Maintainability](https://api.codeclimate.com/v1/badges/129e06fe7f1398c4551c/maintainability)](https://codeclimate.com/repos/5f11aeeb7268d301a20152b0/maintainability)
[![Test Coverage](https://api.codeclimate.com/v1/badges/129e06fe7f1398c4551c/test_coverage)](https://codeclimate.com/repos/5f11aeeb7268d301a20152b0/test_coverage)

![coverage-badge-do-not-edit](https://img.shields.io/badge/Coverage-55.20%25-yellow.svg?longCache=true&style=flat)
![Build](https://github.com/dividohq/application-api/workflows/Build/badge.svg)

Core service for application interactions

## Introduction

The use-cases of `application-api` include:
- Creating, reading and updating related objects:
  - Applications
  - History
  - Submissions
  - Signatories
  - Refunds
  - Cancellations
  - Activations
  - Applicants
- Fetching Form Configuration
- Lender calls
- Sending notifications

## Run

Use `make` command to run a set of dev tools locally (including analyzer, formatting, tests and coverage). This application requires `$GITHUB_TOKEN` (https://github.com/settings/tokens) environment variable to be set.

In case there is a need to run it locally with **Microservices Environment**,
a couple of extra steps is required.

- run `make up` to spin up all necessary containers
- run `make kong-register` to register this service with Kong and be able to use `application-api.api.dev.divido.net` host name

## Usage

A list of endpoints can be found in the [Paw project](https://paw.cloud/account/teams/32249/projects/35205)

## Documentation

Run the `Makefile` command:

> make docs

This will generate the latest openapi-v3.json specification and spin up swagger on [localhost](http://localhost:80).

