# What is this?

[![MIT licensed](https://img.shields.io/badge/license-MIT-blue.svg)](LICENSE)
[![CI](https://github.com/tarlepp/symfony-flex-backend/workflows/CI/badge.svg)](https://github.com/tarlepp/symfony-flex-backend/actions?query=workflow%3ACI)
[![Coverage Status](https://coveralls.io/repos/github/tarlepp/symfony-flex-backend/badge.svg?branch=master)](https://coveralls.io/github/tarlepp/symfony-flex-backend?branch=master)
[![Psalm coverage](https://shepherd.dev/github/tarlepp/symfony-flex-backend/coverage.svg)](https://shepherd.dev/github/tarlepp/symfony-flex-backend)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/tarlepp/symfony-flex-backend/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/tarlepp/symfony-flex-backend/?branch=master)
[![Code Intelligence Status](https://scrutinizer-ci.com/g/tarlepp/symfony-flex-backend/badges/code-intelligence.svg?b=master)](https://scrutinizer-ci.com/code-intelligence)
[![Maintainability](https://api.codeclimate.com/v1/badges/69d6dc6b9fb4791e6b92/maintainability)](https://codeclimate.com/github/tarlepp/symfony-flex-backend/maintainability)
[![Sonarcloud Quality Gate](https://sonarcloud.io/api/project_badges/measure?project=github.com.tarlepp.symfony-flex-backend&metric=alert_status)](https://sonarcloud.io/dashboard?id=github.com.tarlepp.symfony-flex-backend)
[![Sonarcloud Vulnerabilities](https://sonarcloud.io/api/project_badges/measure?project=github.com.tarlepp.symfony-flex-backend&metric=security_rating)](https://sonarcloud.io/dashboard?id=github.com.tarlepp.symfony-flex-backend)
[![Sonarcloud Vulnerabilities](https://sonarcloud.io/api/project_badges/measure?project=github.com.tarlepp.symfony-flex-backend&metric=sqale_rating)](https://sonarcloud.io/dashboard?id=github.com.tarlepp.symfony-flex-backend)
[![Sonarcloud Vulnerabilities](https://sonarcloud.io/api/project_badges/measure?project=github.com.tarlepp.symfony-flex-backend&metric=reliability_rating)](https://sonarcloud.io/dashboard?id=github.com.tarlepp.symfony-flex-backend)
[![SymfonyInsight](https://insight.symfony.com/projects/e59c1ed3-b870-457a-971e-570a27a04784/mini.svg)](https://insight.symfony.com/projects/e59c1ed3-b870-457a-971e-570a27a04784)

JSON REST API which is build on top of [Symfony](https://symfony.com/)
framework.

This application is mean to use as an `API` that some [frontend](#frontend-smallsuptable-of-contentssupsmall)
application(s) or different backend application(s) uses as they like.

## Table of Contents

* [What is this](#what-is-this)
  * [Table of Contents](#table-of-contents)
  * [Requirements](#requirements-smallsuptable-of-contentssupsmall)
    * [Recommendations](#recommendations-smallsuptable-of-contentssupsmall)
  * [Installation](#installation-smallsuptable-of-contentssupsmall)
    * [1. Clone repository](#1-clone-repository-smallsuptable-of-contentssupsmall)
    * [2. Start containers](#2-start-containers-smallsuptable-of-contentssupsmall)
    * [3. Using application](#3-using-application-smallsuptable-of-contentssupsmall)
    * [4. Getting shell to container](#4-getting-shell-to-container-smallsuptable-of-contentssupsmall)
    * [5. Building containers](#5-building-containers-smallsuptable-of-contentssupsmall)
  * [Frontend?](#frontend-smallsuptable-of-contentssupsmall)
  * [Resources](#resources-smallsuptable-of-contentssupsmall)
  * [External links / resources](#external-links--resources-smallsuptable-of-contentssupsmall)
  * [Authors](#authors-smallsuptable-of-contentssupsmall)
  * [License](#license-smallsuptable-of-contentssupsmall)

## Requirements <small><sup>[▲](#table-of-contents)</sup></small>

* [docker-compose](https://docs.docker.com/compose/install/)
* If you are not using docker / docker-compose then follow [this](doc/INSTALLATION_WITHOUT_DOCKER.md)

### Recommendations <small><sup>[▲](#table-of-contents)</sup></small>

* `*nix platform` - most likely you're going to host your application on *nix
  platform - so I would recommend to do development also on that platform.
* `Makefile` support - if you don't have this you need to look `Makefile` file
  to see what each `make` command is doing.

## Installation <small><sup>[▲](#table-of-contents)</sup></small>

This installation guide expects that you're using docker-compose.

### 1. Clone repository <small><sup>[▲](#table-of-contents)</sup></small>

Use your favorite IDE and get checkout from GitHub or just use following
command

```bash
git clone https://github.com/tarlepp/symfony-flex-backend.git
```

### 2. Start containers <small><sup>[▲](#table-of-contents)</sup></small>

For this just run following command:

```bash
make start
```

This command will create three (3) containers to run this backend application.
Those containers are following:

* php (this is for actual application)
* nginx (this will serve application)
* mysql (MySQL 5.7 which will store all the data of application)

### 3. Using application <small><sup>[▲](#table-of-contents)</sup></small>

By default `make start` / `docker-compose up` command starts those three
containers and exposes following ports on `localhost`:

* 8000 (nginx + php-fpm)
* 3310 (mysql)

And this application is usable within your browser on `http://localhost:8000`
address.

MySQL credentials:

```bash
user: root
password: password
```

### 4. Getting shell to container <small><sup>[▲](#table-of-contents)</sup></small>

After you've run `make start` / `docker-compose up` command you can list all
running containers with `docker ps` command.

And to eg. get shell access inside one of those containers you can run following
command:

```bash
make bash
```

Where that `php` is that actual container where this backend application is
running.

### 5. Building containers <small><sup>[▲](#table-of-contents)</sup></small>

For time to time you probably need to build containers again. This is something
that you should do everytime if you have some problems to get containers up and
running. This you can do with following command:

```bash
make start-build
```

## Frontend? <small><sup>[▲](#table-of-contents)</sup></small>

So this is an API backend what about frontend then? No worries I've made simple
Angular NgRx powered template frontend which work with this backend just out of
the box.

[Angular NgRx powered frontend template for Symfony backend](https://github.com/tarlepp/angular-ngrx-frontend)

With these two _template_ applications it should be quite easy to start to
build _your_ own application - right?

## Resources <small><sup>[▲](#table-of-contents)</sup></small>

* [Resource index](doc/README.md)
* [Application commands](doc/COMMANDS.md)
* [Concepts and features](doc/CONCEPTS_AND_FEATURES.md)
* [Custom configuration](doc/CUSTOM_CONFIGURATION.md)
* [Development guide](doc/DEVELOPMENT.md)
* [Installation without docker](doc/INSTALLATION_WITHOUT_DOCKER.md)
* [PhpStorm configuration](doc/PHPSTORM.md)
* [Speed problems with docker-compose?](doc/SPEED_UP_DOCKER_COMPOSE.md)
* [Testing guide](doc/TESTING.md)
* [Usage checklist](doc/USAGE_CHECKLIST.md)
* [Using Xdebug](doc/XDEBUG.md)

## External links / resources <small><sup>[▲](#table-of-contents)</sup></small>

* [Symfony Flex set to enable RAD (Rapid Application Development)](https://www.symfony.fi/entry/symfony-flex-to-enable-rad-rapid-application-development)
* [Symfony 4: A quick Demo](https://medium.com/@fabpot/symfony-4-a-quick-demo-da7d32be323)
* [composer-version](https://github.com/vutran/composer-version)
* [Symfony Recipes Server](https://symfony.sh/)

## Authors <small><sup>[▲](#table-of-contents)</sup></small>

* [Tarmo Leppänen](https://github.com/tarlepp)

## License <small><sup>[▲](#table-of-contents)</sup></small>

[The MIT License (MIT)](LICENSE)

Copyright © 2021 Tarmo Leppänen
