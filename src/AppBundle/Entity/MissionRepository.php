<?php

namespace AppBundle\Entity;

use Doctrine\ORM\EntityRepository;

class MissionRepository extends EntityRepository
{
    public function findPostedByTeamOrderedByDate($team)
    {
        $qb = $this->getEntityManager()->createQueryBuilder();
        $query = $qb->select('m')
            ->from('AppBundle:Mission', 'm')
            ->where('m.team = :team')
            ->andWhere('m.postDate <= :date_now')
            ->orderBy('m.postDate', 'DESC')
            ->setParameter('team', $team)
            ->setParameter('date_now', new \DateTime())
            ->getQuery();

        return $query->getResult();
    }
}
