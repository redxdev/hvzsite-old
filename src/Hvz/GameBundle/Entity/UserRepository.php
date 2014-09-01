<?php

namespace Hvz\GameBundle\Entity;

use Doctrine\ORM\EntityRepository;

use Hvz\GameBundle\Util\QueryHelper;

/**
 * UserRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class UserRepository extends EntityRepository
{
    use QueryHelper;

    public function findInSearchableFields($term)
    {
        $qb = $this->getEntityManager()->createQueryBuilder();
        $query = $qb->select('u')
                    ->from('HvzGameBundle:User', 'u')
                    ->where("u.fullname LIKE :term ESCAPE '!'")
                    ->orWhere("u.email LIKE :term ESCAPE '!'")
                    ->setParameter('term', $this->makeLikeParam($term))
                    ->getQuery();

        return $query->getResult();
    }

    public function findByPage($page, $maxPerPage = 10)
    {
        $qb = $this->getEntityManager()->createQueryBuilder();
        $query = $qb->select('u')
                    ->from('HvzGameBundle:User', 'u')
                    ->getQuery()
                    ->setMaxResults($maxPerPage)
                    ->setFirstResult($page * $maxPerPage);

        return $query->getResult();
    }

    public function findCount()
    {
        $qb = $this->getEntityManager()->createQueryBuilder();
        $query = $qb->select('count(u)')
                    ->from('HvzGameBundle:User', 'u')
                    ->getQuery();

        return $query->getSingleScalarResult();
    }
}
