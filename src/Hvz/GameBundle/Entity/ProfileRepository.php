<?php

namespace Hvz\GameBundle\Entity;

use Doctrine\ORM\EntityRepository;

use Hvz\GameBundle\Util\QueryHelper;

/**
 * ProfileRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class ProfileRepository extends EntityRepository
{
	use QueryHelper;

	public function findInSearchableFields($game, $term)
	{
		$qb = $this->getEntityManager()->createQueryBuilder();
		$query = $qb->select('p')
					->from('HvzGameBundle:Profile', 'p')
					->innerJoin('p.user', 'u')
					->where("p.clan LIKE :term ESCAPE '!'")
					->orWhere("u.fullname LIKE :term ESCAPE '!'")
					->setParameter('term', $this->makeLikeParam($term))
					->getQuery();

		return $query->getResult();
	}

	public function findByGameAndUser($game, $user)
	{
		$qb = $this->getEntityManager()->createQueryBuilder();
		$query = $qb->select('p')
					->from('HvzGameBundle:Profile', 'p')
					->where('p.user = :user')
					->andWhere('p.game = :game')
					->setParameter('user', $user)
					->setParameter('game', $game)
					->getQuery();

		$result = $query->getResult();
		return $result == null ? null : $result[0];
	}

	public function findActive($game)
	{
		$qb = $this->getEntityManager()->createQueryBuilder();
		$query = $qb->select('p')
					->from('HvzGameBundle:Profile', 'p')
					->where('p.active = true')
					->andWhere('p.game = :game')
					->setParameter('game', $game)
					->getQuery();

		return $query->getResult();
	}

	public function findByPage($page, $maxPerPage = 10)
	{
		$qb = $this->getEntityManager()->createQueryBuilder();
		$query = $qb->select('p')
					->from('HvzGameBundle:Profile', 'p')
					->getQuery()
					->setMaxResults($maxPerPage)
					->setFirstResult($page * $maxPerPage);

		return $query->getResult();
	}

	public function findCount()
	{
		$qb = $this->getEntityManager()->createQueryBuilder();
		$query = $qb->select('count(p)')
					->from('HvzGameBundle:Profile', 'p')
					->getQuery();

		return $query->getSingleScalarResult();
	}

	public function findCountByGame($game)
	{
		$qb = $this->getEntityManager()->createQueryBuilder();
		$query = $qb->select('count(p)')
					->from('HvzGameBundle:Profile', 'p')
					->where('p.game = :game')
					->setParameter('game', $game)
					->getQuery();

		return $query->getSingleScalarResult();
	}

	public function findActiveByTeam($game, $team)
	{
		$qb = $this->getEntityManager()->createQueryBuilder();
		$query = $qb->select('p')
					->from('HvzGameBundle:Profile', 'p')
					->where('p.active = true')
					->andWhere('p.team = :team')
					->andWhere('p.game = :game')
					->setParameter('team', $team)
					->setParameter('game', $game)
					->getQuery();

		return $query->getResult();
	}

	public function findActiveZombies($game)
	{
		return $this->findActiveByTeam($game, User::TEAM_ZOMBIE);
	}

	public function findActiveHumans($game)
	{
		return $this->findActiveByTeam($game, User::TEAM_HUMAN);
	}

	public function findActiveOrderedByNumberTaggedAndTeam($game, $page = -1, $maxPerPage = 10)
	{
		$qb = $this->getEntityManager()->createQueryBuilder();
		$query = $qb->select('p')
					->from('HvzGameBundle:Profile', 'p')
					->where('p.active = true')
					->andWhere('p.game = :game')
					->orderBy('p.numberTagged', 'DESC')
					->setParameter('game', $game)
					->getQuery();

		if($page >= 0)
			$query->setMaxResults($maxPerPage)
				  ->setFirstResult($page * $maxPerPage);

		return $query->getResult();
	}

	public function findActiveClanCount($game)
	{
		$qb = $this->getEntityManager()->createQueryBuilder();
		$query = $qb->select('count(p)')
					->from('HvzGameBundle:Profile', 'p')
					->where('p.active = true')
					->andWhere('p.game = :game')
					->andWhere('p.clan is not NULL')
					->andWhere('p.clan != :empty')
					->setParameter('game', $game)
					->setParameter('empty', '')
					->getQuery();

		return $query->getSingleScalarResult();
	}

	public function findActiveOrderedByClan($game, $page, $maxPerPage = 10)
	{
		$qb = $this->getEntityManager()->createQueryBuilder();
		$query = $qb->select('p, p.clan as HIDDEN tmpClan')
					->from('HvzGameBundle:Profile', 'p')
					->where('p.active = true')
					->andWhere('p.game = :game')
					->andWhere('p.clan is not NULL')
					->andWhere('p.clan != :empty')
					->orderBy('tmpClan', 'ASC')
					->setParameter('game', $game)
					->setParameter('empty', '')
					->getQuery()
					->setMaxResults($maxPerPage)
					->setFirstResult($page * $maxPerPage);

		return $query->getResult();
	}

	public function findOneByGameAndTagId($game, $tag)
	{
		$qb = $this->getEntityManager()->createQueryBuilder();
		$query = $qb->select('p')
					->from('HvzGameBundle:Profile', 'p')
					->where('p.game = :game')
					->andWhere('p.tagId = :tag')
					->setParameter('game', $game)
					->setParameter('tag', $tag)
					->getQuery();

		return $query->getOneOrNullResult();
	}

	public function findActiveCreatedToday($game)
	{
		$start = new \DateTime();
		$start->modify('today');

		$qb = $this->getEntityManager()->createQueryBuilder();
		$query = $qb->select('p')
					->from('HvzGameBundle:Profile', 'p')
					->where('p.active = true')
					->andWhere('p.creationDate BETWEEN :start AND :end')
					->andWhere('p.game = :game')
					->orderBy('p.numberTagged', 'DESC')
					->orderBy('p.team', 'DESC')
					->setParameter('game', $game)
					->setParameter('start', $start)
					->setParameter('end', new \DateTime())
					->getQuery();

		return $query->getResult();
	}
}
