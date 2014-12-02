<?php

namespace AppBundle\Entity;

use Doctrine\ORM\EntityRepository;

class RulesetRepository extends EntityRepository
{
    public function findAllOrderedByPosition()
    {
        $qb = $this->getEntityManager()->createQueryBuilder();
        $query = $qb->select('r')
            ->from('AppBundle:Ruleset', 'r')
            ->orderBy('r.position', 'ASC')
            ->getQuery();

        return $query->getResult();
    }
}
