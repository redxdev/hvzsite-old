<?php

namespace AppBundle\Entity;

use Doctrine\ORM\EntityRepository;

class InfectionSpreadRepository extends EntityRepository
{
    public function findPageOrderedByTime($page, $maxPerPage = 10)
    {
        $qb = $this->getEntityManager()->createQueryBuilder();
        $query = $qb->select('s')
            ->from('AppBundle:InfectionSpread', 's')
            ->orderBy('s.time', 'DESC')
            ->getQuery()
            ->setMaxResults($maxPerPage)
            ->setFirstResult($page * $maxPerPage);

        return $query->getResult();
    }

    public function findCount()
    {
        $qb = $this->getEntityManager()->createQueryBuilder();
        $query = $qb->select('count(s)')
            ->from('AppBundle:InfectionSpread', 's')
            ->getQuery();

        return $query->getSingleScalarResult();
    }
}
