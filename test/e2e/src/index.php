<?php

use GuzzleHttp\Client;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;
use Vantt\OAuth2\Client\Provider\OryHydraProvider;

require __DIR__ . '/vendor/autoload.php';

/**
 * Instantiate App
 *
 * In order for the factory to work you need to ensure you have installed
 * a supported PSR-7 implementation of your choice e.g.: Slim PSR-7 and a supported
 * ServerRequest creator (included with Slim PSR-7)
 */
$app = AppFactory::create();

// Add Routing Middleware
#$app->addRoutingMiddleware();

/**
 * Add Error Handling Middleware
 *
 * @param bool $displayErrorDetails -> Should be set to false in production
 * @param bool $logErrors -> Parameter is passed to the default ErrorHandler
 * @param bool $logErrorDetails -> Display error details in error log
 * which can be replaced by a callable of your choice.

 * Note: This middleware should be added last. It will not handle any exceptions/errors
 * for middleware added after it.
 */
$errorMiddleware = $app->addErrorMiddleware(true, true, true);

// Define app routes
$app->get('/', function (Request $request, Response $response, $args) {
    $response->getBody()->write("Hello there");
    return $response;
});

$app->get('/test-connect/client-credentials', function (Request $request, Response $response, $args) {
    $provider = new OryHydraProvider([
                                       'baseUrl'      => 'http://hydra:4444',
                                       'clientId'     => 'machine',
                                       // The client ID assigned to you by the provider
                                       'clientSecret' => 'some-secret',
                                     ]
    );

    $provider->setHttpClient(new Client(['verify' => false]));

    try {
        // Try to get an access token using the client credentials grant.
        $accessToken = $provider->getAccessToken('client_credentials');

        $response = $response->withHeader("Content-Type", "application/json");
        $response->getBody()->write(json_encode($accessToken));
        return $response;
    } catch (IdentityProviderException $e) {
        // Failed to get the access token
        exit($e->getMessage());
    }
});



// Run app
$app->run();