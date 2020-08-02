#!/bin/bash

cd docker
docker-compose -f hydra-compose.yml -f php-compose.yml up