<?php

namespace Hvz\GameBundle\Services;

require_once __DIR__ . '/../../../../vendor/google/apiclient/src/Google/Client.php';

class GoogleOAuthService
{
	private $settings;

	public function __construct($settings)
	{
		$this->settings = $settings;
	}

	public function createClient($uri)
	{
		$client = new \Google_Client();
		$client->setClientId($this->settings->getClientId());
		$client->setClientSecret($this->settings->getClientSecret());
		$client->setApplicationName($this->settings->getApplicationName());
		$client->setScopes("https://www.googleapis.com/auth/userinfo.email https://www.googleapis.com/auth/userinfo.profile");
		$client->setRedirectUri($uri);
		return $client;
	}
}
