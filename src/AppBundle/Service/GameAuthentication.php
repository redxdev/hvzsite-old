<?php

namespace AppBundle\Service;

use AppBundle\Entity\User;
use AppBundle\Security\Authentication\Token\GoogleOAuthToken;
use Doctrine\ORM\EntityManager;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;

class GameAuthentication
{
    const DEFAULT_MAX_API_FAILURES = 100;

    private $googleAuthService;
    private $entityManager;
    private $actLog;
    private $idGenerator;
    private $tokenStorage;
    private $eventDispatcher;
    private $session;

    public function __construct(GoogleOAuthClient $googleAuthService, EntityManager $entityManager,
                                ActionLogService $actLog, IdGenerator $idGenerator, TokenStorageInterface $tokenStorage,
                                EventDispatcherInterface $eventDispatcher, SessionInterface $session)
    {
        $this->googleAuthService = $googleAuthService;
        $this->entityManager = $entityManager;
        $this->actLog = $actLog;
        $this->idGenerator = $idGenerator;
        $this->tokenStorage = $tokenStorage;
        $this->eventDispatcher = $eventDispatcher;
        $this->session = $session;
    }

    public function register($code, $url)
    {
        $client = $this->googleAuthService->createClient($url);
        $oauth = new \Google_Service_Oauth2($client);
        $client->authenticate($code);
        if(!$client->getAccessToken())
        {
            return [
                "status" => "error",
                "error" => "Invalid authentication token. Please try again."
            ];
        }

        $userInfo = $oauth->userinfo->get();
        $email = filter_var($userInfo["email"], FILTER_SANITIZE_EMAIL);

        $userRepo = $this->entityManager->getRepository("AppBundle:User");
        if($userRepo->findOneByEmail($email))
        {
            return [
                "status" => "error",
                "error" => "You are already registered! Try logging in instead."
            ];
        }

        if(!isset($userInfo['hd']) || ($userInfo['hd'] != 'g.rit.edu' && $userInfo != 'rit.edu'))
        {
            return [
                "status" => "error",
                "error" => "Only RIT google accounts can be registered."
            ];
        }

        $user = $this->idGenerator->generateUser(new User(), false);
        $user->setEmail($email);
        $user->setFullname($userInfo["given_name"] . ' ' . $userInfo["family_name"]);
        $this->entityManager->persist($user);

        $this->actLog->record(
            ActionLogService::TYPE_AUTH,
            $email,
            'registered',
            false
        );

        $this->entityManager->flush();

        $client->revokeToken();

        return [
            "status" => "ok"
        ];
    }

    public function login($code, $url, Request $request)
    {
        $client = $this->googleAuthService->createClient($url);
        $oauth = new \Google_Service_Oauth2($client);
        $client->authenticate($code);
        if(!$client->getAccessToken())
        {
            return [
                "status" => "error",
                "error" => "Invalid authentication token. Please try again."
            ];
        }

        $userInfo = $oauth->userinfo->get();
        $email = filter_var($userInfo["email"], FILTER_SANITIZE_EMAIL);

        $userRepo = $this->entityManager->getRepository("AppBundle:User");
        $user = $userRepo->findOneByEmail($email);
        if(!$user)
        {
            return [
                "status" => "error",
                "error" => "Unknown user. Have you registered?"
            ];
        }

        $isAdmin = in_array("ROLE_MOD", $user->getRoles()) || in_array("ROLE_ADMIN", $user->getRoles());
        if(!$isAdmin && !$user->getActive())
        {
            return [
                "status" => "error",
                "error" => "Your account hasn't been activated yet. Please talk to a moderator or administrator."
            ];
        }

        $token = new GoogleOAuthToken($user, $client->getAccessToken(), $url, 'user_area', $user->getRoles());
        $this->tokenStorage->setToken($token);

        $loginEvent = new InteractiveLoginEvent($request, $token);
        $this->eventDispatcher->dispatch('security.interactive_login', $loginEvent);

        return [
            "status" => "ok"
        ];
    }

    public function logout()
    {
        $this->tokenStorage->setToken(null);
        $this->session->invalidate();

        return [
            "status" => "ok"
        ];
    }

    public function processApiKey($apikey)
    {
        if(strlen($apikey) != 32)
            return ["status" => "error", "errors" => ["Invalid API key"]];

        $userRepo = $this->entityManager->getRepository('AppBundle:User');
        $user = $userRepo->findOneByApiKey($apikey);

        if(!$user)
            return ["status" => "error", "errors" => ["Invalid API key"]];

        if(!$user->getApiEnabled())
            return ["status" => "error", "errors" => ["API not enabled for user"]];

        if($user->getApiFails() >= $user->getMaxApiFails())
        {
            return ["status" => "error", "errors" => ["Maximum API usage hit due to failure rate"]];
        }

        return ["status" => "ok", "user" => $user];
    }
}