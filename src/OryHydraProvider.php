<?php


namespace Vantt\OAuth2\Client\Provider;

use League\OAuth2\Client\Provider\AbstractProvider;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use League\OAuth2\Client\Token\AccessToken;
use League\OAuth2\Client\Tool\BearerAuthorizationTrait;
use League\OAuth2\Client\Provider\ResourceOwnerInterface;
use Psr\Http\Message\ResponseInterface;

class OryHydraProvider extends AbstractProvider {
    use BearerAuthorizationTrait;

    /**
     * @var bool
     */
    protected $pkce = false;

    /**
     * @var string
     */
    protected $baseUrl;

    /**
     * @var
     */
    protected $code_verifier;

    /**
     * Constructs an OAuth 2.0 service provider.
     *
     * @param array $options An array of options to set on this provider.
     *     Options include `clientId`, `clientSecret`, `redirectUri`, and `state`.
     *
     *     Individual providers may introduce more options, as needed.
     *     Extra Options:
     *          - pkce: bool , enable pkce code-challenge
     *
     * @param array $collaborators An array of collaborators that may be used to
     *     override this provider's default behavior. Collaborators include
     *     `grantFactory`, `requestFactory`, and `httpClient`.
     *
     *     Individual providers may introduce more collaborators, as needed.
     */
    public function __construct(array $options = [], array $collaborators = []) {
        parent::__construct($options, $collaborators);
    }


    /**
     * @return string
     */
    public function getBaseUrl(): string {
        return $this->baseUrl;
    }

    /**
     * @return bool
     */
    public function isPKCE(): bool {
        return (bool)$this->pkce;
    }

    public function enablePKCE(bool $value = false): void {
        $this->pkce = $value;
    }

    /**
     * @return mixed
     */
    public function getCodeVerifier() {
        if ($this->code_verifier) {
            return $this->code_verifier;
        }

        return $this->code_verifier = $this->getRandomState();
    }

    /**
     * Get provider url to run authorization
     *
     * @return string
     */
    public function getBaseAuthorizationUrl() {
        return $this->getBaseUrl() . '/oauth2/auth';
    }

    /**
     * Returns the base URL for requesting an access token.
     *
     * @param array $params
     *
     * @return string
     */
    public function getBaseAccessTokenUrl(array $params) {
        return $this->getBaseUrl() . '/oauth2/token';
    }

    /**
     * Get provider url to fetch user details
     *
     * @param AccessToken $token
     *
     * @return string
     */
    public function getResourceOwnerDetailsUrl(AccessToken $token) {
        return $this->getBaseUrl() . '/userinfo';
    }

    /**
     * Get the default scopes used by this provider.
     *
     * @return array
     */
    protected function getDefaultScopes() {
        return [
          'offline',
          'offline_access',  // allow return refresh-token from hydra
        ];
    }

    /**
     * Returns the string that should be used to separate scopes when building
     * the URL for requesting an access token.
     *
     * @return string Scope separator, defaults to ' '
     */
    protected function getScopeSeparator() {
        return ' ';
    }

    /**
     * Check a provider response for errors.
     *
     * @param ResponseInterface $response
     * @param array|string      $data
     *
     * @throws IdentityProviderException
     */
    protected function checkResponse(ResponseInterface $response, $data) {
        if ($response->getStatusCode() >= 400) {
            throw new IdentityProviderException(
              $data['error'] ?: $response->getReasonPhrase(),
              $response->getStatusCode(),
              $response
            );
        }
    }

    /**
     * Generate a user object from a successful user details request.
     *
     * @param array       $response
     * @param AccessToken $token
     *
     * @return OryHydraResourceOwner|ResourceOwnerInterface
     */
    protected function createResourceOwner(array $response, AccessToken $token): OryHydraResourceOwner {
        return new OryHydraResourceOwner($response);
    }
}