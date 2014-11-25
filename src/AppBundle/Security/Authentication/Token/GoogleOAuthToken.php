<?php

namespace AppBundle\Security\Authentication\Token;

use Symfony\Component\Security\Core\Authentication\Token\AbstractToken;

class GoogleOAuthToken extends AbstractToken implements \Serializable
{
    private $accessToken;
    private $redirectUri;
    private $providerKey;

    public function __construct($user, $accessToken, $redirectUri, $providerKey, array $roles = array())
    {
        parent::__construct($roles);

        if (empty($providerKey)) {
            throw new \InvalidArgumentException('providerKey must not be empty.');
        }

        $this->setUser($user);
        $this->accessToken = $accessToken;
        $this->redirectUri = $redirectUri;
        $this->providerKey = $providerKey;

        parent::setAuthenticated(count($roles) > 0);
    }

    public function getCredentials()
    {
        return '';
    }

    public function getAccessToken()
    {
        return $this->accessToken;
    }

    public function getProviderKey()
    {
        return $this->providerKey;
    }

    public function getRedirectUri()
    {
        return $this->redirectUri;
    }

    public function eraseCredentials()
    {
    }

    public function serialize()
    {
        return serialize(array(
            $this->accessToken,
            $this->redirectUri,
            $this->providerKey,
            parent::serialize()
        ));
    }

    public function unserialize($serialized)
    {
        list(
            $this->accessToken,
            $this->redirectUri,
            $this->providerKey,
            $parentStr
            ) = unserialize($serialized);
        parent::unserialize($parentStr);
    }
}