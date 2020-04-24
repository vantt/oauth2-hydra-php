<?php

namespace Vantt\OAuth2\Client\Provider;

use League\OAuth2\Client\Provider\ResourceOwnerInterface;

/**
 * Class HydraResourceOwner
 * @package ChrisHemmings\OAuth2\Client\Provider
 *
 * @see https://www.ory.sh/hydra/docs/reference/api#openid-connect-userinfo
 * @see https://www.ory.sh/hydra/docs/reference/api#schemauserinforesponse
 *
 *      {
            "birthdate": "string",
            "email": "string",
            "email_verified": true,
            "family_name": "string",
            "given_name": "string",
            "gender": "string",
            "locale": "string",
            "middle_name": "string",
            "name": "string",
            "nickname": "string",
            "phone_number": "string",
            "phone_number_verified": true,
            "picture": "string",
            "preferred_username": "string",
            "profile": "string",
            "sub": "string",
            "updated_at": 0,
            "website": "string",
            "zoneinfo": "string"
            }
 */
class OryHydraResourceOwner implements ResourceOwnerInterface {

    /**
     * Raw response
     *
     * @var
     */
    protected $response;

    /**
     * Creates new resource owner.
     *
     * @param array $response
     */
    public function __construct(array $response) {
        $this->response = $response;
    }

    public function getPicture() :?string {
        return $this->response['picture'] ?: null;
    }
    /**
     * Get resource owner id
     *
     * @return string
     */
    public function getId(): string  {
        return $this->response['sub'];
    }

    /**
     * Return all of the owner details available as an array.
     *
     * @return array
     */
    public function toArray(): array {
        return $this->response;
    }

    /**
     * Get emailaddress
     *
     * @return string|null
     */
    public function getEmail() : ?string {
        return $this->response['email'] ?: null;
    }

    /**
     * Get email verified
     *
     * @return bool
     */
    public function isEmailVerified() :bool{
        return (bool)$this->response['email_verified'] ?: false;
    }

    /**
     * @return string|null
     */
    public function getFirstName(): ?string {
        return $this->response['given_name'] ?: null;
    }

    /**
     * @return string|null
     */
    public function getLastName(): ?string {
        return $this->response['family_name'] ?: null;
    }

    /**
     * @return string|null
     */
    public function getGender(): ?string {
        return $this->response['gender'] ?: null;
    }

    /**
     * Get name
     *
     * @return string|null
     */
    public function getName(): ?string  {
        return $this->response['name'] ?: null;
    }

    /**
     * Get name
     *
     * @return string|null
     */
    public function getPreferredUsername(): ?string  {
        return $this->response['preferred_username'] ?: null;
    }

    /**
     * Get name
     *
     * @return string|null
     */
    public function getLocale(): ?string  {
        return $this->response['locale'] ?: null;
    }
    /**
     * Get name
     *
     * @return string|null
     */
    public function getZoneInfo(): ?string {
        return $this->response['zoneinfo'] ?: null;
    }
}