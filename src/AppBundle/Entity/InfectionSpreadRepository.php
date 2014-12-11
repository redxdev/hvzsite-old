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

    public function findByZombieOrderedByTime(User $user)
    {
        $qb = $this->getEntityManager()->createQueryBuilder();
        $query = $qb->select('s')
            ->from('AppBundle:InfectionSpread', 's')
            ->where('s.zombie = :user')
            ->orderBy('s.time', 'DESC')
            ->setParameter('user', $user)
            ->getQuery();

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

    public function findForKillstreak($zombie)
    {
        $qb = $this->getEntityManager()->createQueryBuilder();
        $query = $qb->select('s')
            ->from('AppBundle:InfectionSpread', 's')
            ->where('s.zombie = :zombie')
            ->andWhere('s.time BETWEEN :start AND :end')
            ->setParameter('zombie', $zombie)
            ->setParameter('start', new \DateTime('-1 hour'))
            ->setParameter('end', new \DateTime())
            ->getQuery();

        return $query->getResult();
    }

    public function findCountGroupedByTime()
    {
        $qb = $this->getEntityManager()->createQueryBuilder();
        $query = $qb->select('count(s) as totalCount, s.time, substring(s.time, 1, 13) as HIDDEN timeStr')
            ->from('AppBundle:InfectionSpread', 's')
            ->groupBy('timeStr')
            ->orderBy('s.time', 'ASC')
            ->getQuery();

        return $query->getResult();
    }
}
