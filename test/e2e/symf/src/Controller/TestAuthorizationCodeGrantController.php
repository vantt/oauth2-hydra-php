<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Routing\Annotation\Route;
use Vantt\OAuth2\Client\Provider\OryHydraProvider;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;

class TestAuthorizationCodeGrantController extends AbstractController {

    const REDIRECT_URL = '/test-connect/authorization-code';
    /**
     * Link to this controller to start the "connect" process
     *
     * @Route("/test-connect/authorization-code", name="connect_hydra_authorization_code")
     *
     * @see https://github.com/thephpleague/oauth2-client#authorization-code-grant
     */
    public function connectAuthorizationCode(Request $request, SessionInterface $session): Response {
        // the scopes you want to access
        $scopes   = ['photos.read', 'account.profile', 'openid', 'offline', 'offline_access'];
        $provider = new OryHydraProvider([
                                           'baseUrl'      => $this->getParameter('HYDRA_PUBLIC_HOST'),
                                           'clientId'     => 'user_authorization_code1',
                                           'clientSecret' => 'some-secret',
                                           'redirect_uri' => self::REDIRECT_URL,
                                           'scope'        => $scopes,
                                         ]
        );

        $code  = $request->get('code', null);
        $state = $request->get('state', null);

        // If we don't have an authorization code then get one
        if (null === $code) {

            // Get the state generated for you and store it to the session.
            $session->set('oauth2state', $provider->getState());

            // Fetch the authorization URL from the provider; this returns the
            // urlAuthorize option and generates and applies any necessary parameters
            // (e.g. state).
            //
            // Redirect the user to the authorization URL.
            return new RedirectResponse($provider->getAuthorizationUrl());
        }

        // Check given state against previously stored one to mitigate CSRF attack
        if (empty($state) || ($state !== $session->get('oauth2state', null))) {
            $session->remove('oauth2state');

            throw new HttpException('Invalid state', 500);
        }

        try {
            // Try to get an access token using the authorization code grant.
            $accessToken = $provider->getAccessToken('authorization_code', ['code' => $code]);
            $resourceOwner = $provider->getResourceOwner($accessToken);

            return new JsonResponse(['accessToken' => $accessToken, 'resourceOwner' => $resourceOwner]);

        } catch (IdentityProviderException $e) {
            throw new HttpException($e->getMessage(), 500);
        }
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