#!/bin/bash

rm -rf ./coverage
mkdir -p ./coverage
chmod 777 ./coverage

# run with phpdbg for better coverage results
#phpdbg -qrr ../../vendor/bin/phpunit "$@" --configuration coverage_phpunit.xml ./

php ../vendor/bin/phpunit "$@" --configuration phpunit_coverage.xml ./
