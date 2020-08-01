<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Routing\Annotation\Route;

class TestAuthorizationCodeGrantController extends AbstractController {

    /**
     * Link to this controller to start the "connect" process
     *
     * @Route("/test-connect/authorization-code", name="connect_hydra_authorization_code")
     *
     * @see https://github.com/thephpleague/oauth2-client#authorization-code-grant
     */
    public function connectAuthorizationCode(): Response {
        // the scopes you want to access
        $scopes  = ['photos.read', 'account.profile', 'openid', 'offline', 'offline_access'];
        $options = [];

        // will redirect to Hydra!
        return $this->client->redirect($scopes, $options);
    }

    /**
     * Link to this controller to start the "connect" process
     *
     * @Route("/test-connect/authorization-code-pkce", name="connect_hydra_authorization_code_pkce")
     */
    public function connectAuthorizationCodePKCE(): Response {
        // the scopes you want to access
        $scopes  = ['photos.read', 'account.profile', 'openid', 'offline', 'offline_access'];
        $options = [];

        // will redirect to Hydra!
        return $this->client->redirect($scopes, $options);
    }


    /**
     * After going to Facebook, you're redirected back here
     * because this is the "redirect_route" you configured
     * in config/packages/knpu_oauth2_client.yaml
     *
     * @Route("/test-connect/hydra/check", name="connect_hydra_check", schemes={"https"})
     *
     * @return Response
     */
    public function connectCheckAction(): Response {
        /** @var \KnpU\OAuth2ClientBundle\Client\Provider\FacebookClient $client */
        $client = $this->client;

        try {
            $accessToken = $client->getAccessToken();
            $user        = $client->fetchUserFromToken($accessToken);

            return new JsonResponse(['token' => $accessToken, 'user' => $user]);
        } catch (IdentityProviderException $e) {
            throw new HttpException(500, $e->getMessage());
        }
    }
}