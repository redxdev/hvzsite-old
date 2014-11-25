<?php

namespace AppBundle\Service;

use Doctrine\ORM\EntityManager;

class IdGenerator
{
    const MAX_REGEN_COUNT = 10;

    private $entityManager;

    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function generate($size = 8)
    {
        $str = $this->generateRandomString($size);
        $count = 0;

        while($this->isDuplicateHumanId($str) ||
            $this->isDuplicateZombieId($str) ||
            $this->isDuplicateAntiVirusId($str))
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
            ->where("id.id_string = :gen")
            ->setParameter("gen", $id)
            ->getQuery();

        return $query->getSingleScalarResult() != 0;
    }

    private function isDuplicateZombieId($id)
    {
        $query = $this->entityManager->createQueryBuilder()
            ->select("count(user.id)")
            ->from("AppBundle:User", 'user')
            ->where("user.zombieId = :gen")
            ->setParameter("gen", $id)
            ->getQuery();

        return $query->getSingleScalarResult() != 0;
    }

    private function isDuplicateAntiVirusId($id)
    {
        $query = $this->entityManager->createQueryBuilder()
            ->select("count(av.id_string)")
            ->from("AppBundle:AntiVirusId", 'av')
            ->where("av.id_string = :gen")
            ->setParameter("gen", $id)
            ->getQuery();

        return $query->getSingleScalarResult() != 0;
    }
}