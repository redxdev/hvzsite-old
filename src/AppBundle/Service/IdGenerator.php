<?php

namespace AppBundle\Service;

use AppBundle\Entity\HumanId;
use AppBundle\Entity\User;
use Doctrine\ORM\EntityManager;

class IdGenerator
{
    const MAX_REGEN_COUNT = 10;

    private $entityManager;

    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function generateUser(User $user, $flush = true)
    {
        $user->setZombieId($this->generate());

        $id1 = new HumanId();
        $id1->setIdString($this->generate());
        $id1->setUser($user);
        $user->addHumanId($id1);
        $this->entityManager->persist($id1);

        $id2 = new HumanId();
        $id2->setIdString($this->generate());
        $id2->setUser($user);
        $user->addHumanId($id2);
        $this->entityManager->persist($id2);

        $user->setApiKey($this->generate(32));

        if($flush)
            $this->entityManager->flush();

        return $user;
    }

    public function generate($size = 8)
    {
        $str = $this->generateRandomString($size);
        $count = 0;

        while($this->isDuplicateHumanId($str) ||
            $this->isDuplicateZombieIdOrApiKey($str) ||
            $this->isDuplicateAntiVirusId($str) ||
            $this->isDuplicateClanCode($str))
        {
            if($count > IdGenerator::MAX_REGEN_COUNT)
            {
                throw new \LogicException("Number of regenerated ids exceeds MAX_REGEN_COUNT (" . IdGenerator::MAX_REGEN_COUNT . ")");
            }

            $str = $this->generateRandomString($size);
            $count++;
        }

        return $str;
    }

    private function generateRandomString($size)
    {
        $str = '';
        $charset = 'abcdefghikmnopqrstuvwxyz23456789';
        $count = strlen($charset) - 1;

        for($i = 0; $i < $size; $i++)
        {
            $str .= $charset[mt_rand(0, $count)];
        }

        return $str;
    }

    private function isDuplicateHumanId($id)
    {
        $query = $this->entityManager->createQueryBuilder()
            ->select("count(id.id)")
            ->from("AppBundle:HumanId", 'id')
            ->where("id.idString = :gen")
            ->setParameter("gen", $id)
            ->getQuery();

        return $query->getSingleScalarResult() != 0;
    }

    private function isDuplicateZombieIdOrApiKey($id)
    {
        $query = $this->entityManager->createQueryBuilder()
            ->select("count(user.id)")
            ->from("AppBundle:User", 'user')
            ->where("user.zombieId = :gen")
            ->orWhere("user.apiKey = :gen")
            ->setParameter("gen", $id)
            ->getQuery();

        return $query->getSingleScalarResult() != 0;
    }

    private function isDuplicateAntiVirusId($id)
    {
        $query = $this->entityManager->createQueryBuilder()
            ->select("count(av.id)")
            ->from("AppBundle:AntiVirusId", 'av')
            ->where("av.idString = :gen")
            ->setParameter("gen", $id)
            ->getQuery();

        return $query->getSingleScalarResult() != 0;
    }

    private function isDuplicateClanCode($id)
    {
        $query = $this->entityManager->createQueryBuilder()
            ->select("count(clan.id)")
            ->from("AppBundle:Clan", 'clan')
            ->where("clan.code = :gen")
            ->setParameter("gen", $id)
            ->getQuery();

        return $query->getSingleScalarResult() != 0;
    }
}