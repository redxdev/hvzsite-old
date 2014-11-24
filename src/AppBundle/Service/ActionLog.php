<?php

namespace AppBundle\Service;

use AppBundle\Entity\ActionLogEntry;
use Doctrine\ORM\EntityManager;

class ActionLogService
{
    const TYPE_GAME = "game";
    const TYPE_ADMIN = "admin";
    const TYPE_AUTH = "auth";
    const TYPE_PROFILE = "profile";

    private $entityManager;

    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function record($type, $user, $action, $flush = true)
    {
        $entry = new ActionLogEntry();
        $entry->setType($type);
        $entry->setUser($user);
        $entry->setAction($action);

        $this->entityManager->persist($entry);
        if($flush)
            $this->entityManager->flush();
    }
}