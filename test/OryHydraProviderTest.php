<?php

namespace Vantt\OAuth2\Client\Test\Provider;

use GuzzleHttp\ClientInterface;
use Psr\Http\Message\ResponseInterface;
use Vantt\OAuth2\Client\Provider\OryHydraProvider;
use League\OAuth2\Client\Tool\QueryBuilderTrait;
use PHPUnit\Framework\TestCase;
use Mockery as m;

class OryHydraProviderTest extends TestCase {
    use QueryBuilderTrait;

    protected $provider;

    protected $provider_data = [
      'baseUrl'      => 'http://baseUrl.com',
      'clientId'     => 'mock_client_id',
      'clientSecret' => 'mock_secret',
      'redirectUri'  => 'mock_redirect_uri',
    ];

    protected function setUp(): void {
        $this->provider = new OryHydraProvider($this->provider_data);
    }

    public function tearDown(): void {
        m::close();
        parent::tearDown();
    }

    public function test_getBaseUrl(): void {
        $url = $this->provider->getBaseUrl();
        $this->assertEquals('http://baseUrl.com', $url);

        $uri = parse_url($url);
        $this->assertEquals('http', $uri['scheme']);
        $this->assertEquals('baseUrl.com', $uri['host']);
    }


    public function test_getBaseAuthorizationUrl_Path_Correctly(): void {
        $url = $this->provider->getBaseAuthorizationUrl();
        $uri = parse_url($url);

        $this->assertEquals('/oauth2/auth', $uri['path']);
    }

    public function test_getBaseAccessTokenUrl_Path_Correctly(): void {
        $params = [];

        $url = $this->provider->getBaseAccessTokenUrl($params);
        $uri = parse_url($url);

        $this->assertEquals('/oauth2/token', $uri['path']);
    }

    public function test_getAuthorizationUrl_EnoughParams_Existed(): void {
        $url = $this->provider->getAuthorizationUrl();
        $uri = parse_url($url);

        parse_str($uri['query'], $query);

        $this->assertArrayHasKey('client_id', $query);
        // ??? $this->assertArrayHasKey('client_secret', $query);
        $this->assertArrayHasKey('redirect_uri', $query);
        $this->assertArrayHasKey('state', $query);
        $this->assertArrayHasKey('response_type', $query);
        $this->assertArrayHasKey('approval_prompt', $query);
        $this->assertArrayHasKey('scope', $query);

    }

    public function test_getAuthorizationUrl_DefaultValues_CorrectlySet(): void {
        $url = $this->provider->getAuthorizationUrl();
        $uri = parse_url($url);

        parse_str($uri['query'], $query);

        $this->assertEquals($this->provider_data['clientId'], $query['client_id']);
        // ??? $this->assertArrayHasKey('client_secret', $query);
        $this->assertEquals($this->provider_data['redirectUri'], $query['redirect_uri']);
        $this->assertNotNull($this->provider->getState());
        $this->assertEquals('code', $query['response_type']);
        $this->assertEquals('auto', $query['approval_prompt']);
        $this->assertEquals('offline offline_access', $query['scope']); // default scopes
    }

    public function test_getAuthorizationUrl_Scopes_CorrectlyEncoded() {
        $scopes       = ["dot.scope", uniqid() . "." . uniqid(), uniqid(), uniqid()];
        $urlWithScope = $this->provider->getAuthorizationUrl(['scope' => $scopes]);

        $scopeSeparator = ' ';
        $query          = ['scope' => implode($scopeSeparator, $scopes)];
        $encodedScope   = $this->buildQueryString($query);

        $this->assertStringContainsString($encodedScope, $urlWithScope);
    }

    public function test_isPKCE() {
        $provider = new OryHydraProvider($this->provider_data + ['pkce' => true]);
        $this->assertTrue($provider->isPKCE());

        $provider = new OryHydraProvider($this->provider_data + ['pkce' => false]);
        $this->assertFalse($provider->isPKCE());
    }

    public function test_getAccessToken() {
        $data = [
          'access_token' => uniqid(),
          'scope'        => uniqid() . "," . uniqid(),
          'token_type'   => 'bearer',
        ];

        $response = m::mock(ResponseInterface::class);
        $response->shouldReceive('getBody')->andReturn(json_encode($data));
        $response->shouldReceive('getHeader')->andReturn(['content-type' => 'json']);
        $response->shouldReceive('getStatusCode')->andReturn(200);

        $client = m::mock(ClientInterface::class);
        $client->shouldReceive('send')->times(1)->andReturn($response);
        $this->provider->setHttpClient($client);

        $token = $this->provider->getAccessToken('authorization_code', ['code' => 'mock_authorization_code']);

        $this->assertEquals($data['access_token'], $token->getToken());
        $this->assertNull($token->getExpires());
        $this->assertNull($token->getRefreshToken());
        $this->assertNull($token->getResourceOwnerId());
    }

    public function test_getResourceOwner() {
        $data = [
          'sub'   => uniqid(),
          'name'  => uniqid(),
          'email' => uniqid(),
          'login' => uniqid(),
        ];

        $postResponse = m::mock(ResponseInterface::class);
        $postResponse->shouldReceive('getBody')
                     ->andReturn('access_token=mock_access_token&expires=3600&refresh_token=mock_refresh_token&otherKey={1234}');
        $postResponse->shouldReceive('getHeader')->andReturn(['content-type' => 'application/x-www-form-urlencoded']);
        $postResponse->shouldReceive('getStatusCode')->andReturn(200);

        $userResponse = m::mock(ResponseInterface::class);
        $userResponse->shouldReceive('getBody')->andReturn(json_encode($data));
        $userResponse->shouldReceive('getHeader')->andReturn(['content-type' => 'json']);
        $userResponse->shouldReceive('getStatusCode')->andReturn(200);

        $client = m::mock(ClientInterface::class);
        $client->shouldReceive('send')
               ->times(2)
               ->andReturn($postResponse, $userResponse);
        $this->provider->setHttpClient($client);

        $token = $this->provider->getAccessToken('authorization_code', ['code' => 'mock_authorization_code']);
        $user  = $this->provider->getResourceOwner($token);

        $this->assertEquals($data, $user->toArray());
        $this->assertEquals($data['sub'], $user->getId());
        $this->assertEquals($data['name'], $user->getName());
        $this->assertEquals($data['email'], $user->getEmail());

        //        $this->assertEquals($nickname, $user->getNickname());
        //        $this->assertContains($nickname, $user->getUrl());
    }
}