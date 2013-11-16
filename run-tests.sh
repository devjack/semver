#!/bin/sh

mkdir -p build/logs
vendor/bin/phpcs --standard=PSR2 src/
vendor/bin/phpcs --standard=PSR2 tests/
vendor/bin/phpunit
vendor/bin/phpunit --coverage-html build/coverage