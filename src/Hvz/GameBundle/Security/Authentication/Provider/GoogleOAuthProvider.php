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

require_once __DIR__ . '/../../../OAuth/Google_Client.php';
require_once __DIR__ . '/../../../OAuth/contrib/Google_Oauth2Service.php';

class GoogleOAuthProvider implements AuthenticationProviderInterface
{
    private $userProvider;

    public function __construct(UserProviderInterface $userProvider)
    {
        $this->userProvider = $userProvider;
    }

    public function authenticate(TokenInterface $token)
    {
        $user = $this->userProvider->loadUserByUsername($token->getUsername());

        $client = AuthController::getGoogleClient($token->getRedirectUri());
        $oauth = new \Google_Oauth2Service($client);
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