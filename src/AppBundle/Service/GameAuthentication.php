<?php

namespace AppBundle\Service;

use AppBundle\Entity\User;
use Doctrine\ORM\EntityManager;

class GameAuthentication
{
    private $googleAuthService;
    private $entityManager;
    private $actLog;
    private $idGenerator;

    public function __construct(GoogleOAuthClient $googleAuthService, EntityManager $entityManager, ActionLogService $actLog, IdGenerator $idGenerator)
    {
        $this->googleAuthService = $googleAuthService;
        $this->entityManager = $entityManager;
        $this->actLog = $actLog;
        $this->idGenerator = $idGenerator;
    }

    public function registerAccount($code, $url)
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
}