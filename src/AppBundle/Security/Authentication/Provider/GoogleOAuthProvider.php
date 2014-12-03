<?php

namespace AppBundle\Security\Authentication\Provider;

use AppBundle\Security\Authentication\Token\GoogleOAuthToken;
use AppBundle\Service\GoogleOAuthClient;
use Symfony\Component\Security\Core\Authentication\Provider\AuthenticationProviderInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class GoogleOAuthProvider implements AuthenticationProviderInterface
{
    private $userProvider;
    private $googleAuth;

    public function __construct(UserProviderInterface $userProvider, GoogleOAuthClient $googleAuth)
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

        if($user && $user->getEmail() == filter_var($userInfo['email'], FILTER_SANITIZE_EMAIL)) {
            return $token;
        }

        throw new AuthenticationException("Google OAuth failed");
    }

    public function supports(Tokeninterface $token)
    {
        return $token instanceof GoogleOAuthToken;
    }
}