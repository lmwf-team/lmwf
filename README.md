[![PSR-12](https://github.com/lmwf-team/lmwf/actions/workflows/phpcs.yml/badge.svg)](https://github.com/lmwf-team/lmwf/actions/workflows/phpcs.yml)
[![PHPUnit](https://github.com/lmwf-team/lmwf/actions/workflows/phpunit.yml/badge.svg)](https://github.com/lmwf-team/lmwf/actions/workflows/phpunit.yml)
[![PHPStan](https://github.com/lmwf-team/lmwf/actions/workflows/phpstan.yml/badge.svg)](https://github.com/lmwf-team/lmwf/actions/workflows/phpstan.yml)
![Code Coverage Badge](https://img.shields.io/endpoint?url=https://lmwf-team.github.io/lmwf/coverage.json)

# LMWF

**[Documentation](https://lmwf-team.github.io/lmwf/phpdoc/)**

Lightweight web framework that doesn’t require tons of dependencies and keeps you in control.

<p align="center">
<img width="256px" height="auto" src="./logo.png">
</p>

## Overview

LMWF is composed of multiple modules. The following image is an overview of its modules. As it is dynamically generated, it is guaranteed to be up-to-date.

![Modules of LMWF](https://lmwf-team.github.io/lmwf/diagram.png)

# Security

 - Deactivate `display_errors` and `display_warnings` in PHP settings. LMWF does not take care of that for you.

# Style

## Exceptions

We avoid using exceptions to handle logics as much as possible. This means that when the code throws exception, it is not meant to be handled, but rather signifies an issue that needs to be fixed with the code (for instance, instantiating a struct with invalid paremeters). But this is still WIP.

When we do throw exception, we usually rely on PHP built-in exception with a custom code defined in the enum ExceptionCode. Each thrown exception thus has a unique code that can be referenced against in Exceptioncode.