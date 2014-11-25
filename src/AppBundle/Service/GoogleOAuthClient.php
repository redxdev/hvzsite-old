<?php

namespace AppBundle\Service;

class GoogleOAuthClient
{
    private $clientId;
    private $clientSecret;
    private $applicationName;

    public function __construct($clientId, $clientSecret, $applicationName)
    {
        $this->clientId = $clientId;
        $this->clientSecret = $clientSecret;
        $this->applicationName = $applicationName;
    }

    public function createClient($redirectUri)
    {
        $client = new \Google_Client();
        $client->setClientId($this->clientId);
        $client->setClientSecret($this->clientSecret);
        $client->setApplicationName($this->applicationName);
        $client->setScopes("https://www.googleapis.com/auth/userinfo.email https://www.googleapis.com/auth/userinfo.profile");
        $client->setRedirectUri($redirectUri);

        return $client;
    }
}