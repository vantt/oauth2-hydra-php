<?php

namespace Vantt\OAuth2\Client\Test\Provider;

use Vantt\OAuth2\Client\Provider\OryHydraResourceOwner;
use PHPUnit\Framework\TestCase;

class OryHydraResourceOwnerTest extends TestCase {

    public function testToArray() {
        // Mock
        $mock_data = [
          "sub"                   => "12345",

          "email"                 => "mock.name@example.com",
          "email_verified"        => true,

          "name"                  => "mock name",
          "family_name"           => "mock",
          "given_name"            => "mock",
          "middle_name"           => "string",
          "nickname"              => "string",

          "birthdate"             => "string",
          "gender"                => "string",
          "locale"                => "string",


          "phone_number"          => "string",
          "phone_number_verified" => true,
          "picture"               => "mock_image_url",
          "preferred_username"    => "string",
          "profile"               => "string",
          "updated_at"            => 0,
          "website"               => "string",
          "zoneinfo"              => "string",
        ];

        $user = new OryHydraResourceOwner($mock_data);
        $this->assertEquals($mock_data, $user->toArray());
    }

    public function testUserDefaults() {
        // Mock
        $mock_data = [
          "sub"                   => "12345",

          "email"                 => "mock.name@example.com",
          "email_verified"        => true,

          "name"                  => "mock name",
          "family_name"           => "mock",
          "given_name"            => "mock",
          "middle_name"           => "string",
          "nickname"              => "string",

          "birthdate"             => "string",
          "gender"                => "string",
          "locale"                => "string",


          "phone_number"          => "string",
          "phone_number_verified" => true,
          "picture"               => "mock_image_url",
          "preferred_username"    => "string",
          "profile"               => "string",
          "updated_at"            => 0,
          "website"               => "string",
          "zoneinfo"              => "string",
        ];

        $user = new OryHydraResourceOwner($mock_data);

        $this->assertEquals($mock_data['sub'], $user->getId());
        $this->assertEquals($mock_data['name'], $user->getName());
        $this->assertEquals($mock_data['preferred_username'], $user->getPreferredUsername());
        $this->assertEquals($mock_data['given_name'], $user->getFirstName());
        $this->assertEquals($mock_data['family_name'], $user->getLastName());
        $this->assertEquals($mock_data['gender'], $user->getGender());
        $this->assertEquals($mock_data['email'], $user->getEmail());
        $this->assertEquals($mock_data['email_verified'], $user->isEmailVerified());
        $this->assertEquals($mock_data['locale'], $user->getLocale());
        $this->assertEquals($mock_data['picture'], $user->getPicture());

    }

    public function testUserPartialData() {
        $user = new OryHydraResourceOwner([
                                            'sub'         => '12345',
                                            'name'        => 'mock name',
                                            'given_name'  => 'mock',
                                            'family_name' => 'name',
                                          ]
        );

    }

    public function testUserMinimalData() {
        $user = new OryHydraResourceOwner([
                                            'sub'  => '12345',
                                            'name' => 'mock name',
                                          ]
        );

        $this->assertEquals(null, $user->getEmail());
    }
}