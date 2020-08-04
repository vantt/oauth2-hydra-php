<?php

namespace App\Controller;

use GuzzleHttp\Client;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Vantt\OAuth2\Client\Provider\OryHydraProvider;

class TestClientCredentialGrantController extends AbstractController {

    /**
     * Link to this controller to start the "connect" process
     *
     * @Route("/test-connect/client-credentials", name="connect_hydra_client_credentials")
     * @see https://github.com/thephpleague/oauth2-client#client-credentials-grant
     */
    public function connectClientCredentials(): Response {
        // Note: the GenericProvider requires the `urlAuthorize` option, even though
        // it's not used in the OAuth 2.0 client credentials grant type.

        $provider = new OryHydraProvider([
                                           'baseUrl'      => $this->getParameter('hydra_public_host'),
                                           'clientId'     => 'user_client_credential',
                                           'clientSecret' => 'some-secret',
                                         ]
        );

        $provider->setHttpClient(new Client(['verify' => false]));

        try {
            // Try to get an access token using the client credentials grant.
            $accessToken = $provider->getAccessToken('client_credentials');

            return new JsonResponse($accessToken);
        } catch (IdentityProviderException $e) {
            // Failed to get the access token
            exit($e->getMessage());
        }
    }
}