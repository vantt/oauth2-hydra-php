# oauth2-hydra-php
Ory-Hydra Provider for the OAuth 2.0 Client

## Run Test
### Unit Test
```
php vendor/bin/phpunit -v
```

or

```
./tool/php vendor/bin/phpunit -v
```

### E2e Test
docker-compose -f hydra-compose.yml -f php-compose.yml -f cypress-compose.yml  up --exit-code-from cypress
docker-compose up --exit-code-from cypress
