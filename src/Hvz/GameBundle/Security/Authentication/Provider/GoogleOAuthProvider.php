<?php
// src/Hvz/GameBundle/Security/Authentication/Provider/GoogleOAuthProvider.php
namespace Hvz\GameBundle\Security\Authentication\Provider;

use Symfony\Component\Security\Core\Authentication\Provider\AuthenticationProviderInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\NonceExpiredException;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

use Hvz\GameBundle\Security\Authentication\Token\GoogleOAuthToken;
use Hvz\GameBundle\Controller\AuthController;

require_once __DIR__ . '/../../../../../../vendor/google/apiclient/src/Google/Service/OAuth2.php';

class GoogleOAuthProvider implements AuthenticationProviderInterface
{
    private $userProvider;
    private $googleAuth;

    public function __construct(UserProviderInterface $userProvider, $googleAuth)
    {
        $this->userProvider = $userProvider;
        $this->googleAuth = $googleAuth;
    }

    public function authenticate(TokenInterface $token)
    {
        $user = $this->userProvider->loadUserByUsername($token->getUsername());

        $client = $this->googleAuth->createClient($token->getRedirectUri());
        $oauth = new \Google_Service_Oauth2($client);
        $client->setAccessToken($token->getAccessToken());
		$userInfo = $oauth->userinfo->get();

        if ($user && $user->getEmail() == filter_var($userInfo['email'], FILTER_SANITIZE_EMAIL)) {
            return $token;
        }

        throw new AuthenticationException('Google OAuth authentication failed.');

        return $token;
    }

    public function supports(TokenInterface $token)
    {
        return $token instanceof GoogleOauthToken;
    }
}
