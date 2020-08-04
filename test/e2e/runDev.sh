#!/bin/bash

cd docker
docker-compose  -f hydra-compose.yml -f php-compose.yml  --env-file ".env.dev" up