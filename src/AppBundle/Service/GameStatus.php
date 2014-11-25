<?php

namespace AppBundle\Service;

use Doctrine\ORM\EntityManager;

class GameStatus
{
    private $entityManager;

    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function getOverview()
    {

    }
}