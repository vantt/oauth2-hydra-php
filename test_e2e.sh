#!/bin/bash

cd test/e2e/docker
docker-compose -f hydra-compose.yml -f php-compose.yml -f cypress-compose.yml  up