<?php

namespace Hvz\GameBundle\Services;

class BadgeRegistry
{
	private $entityManager;

	private $manualRegistry = array();

	private $autoRegistry = array();

	public function __construct($entityManager)
	{
		$this->entityManager = $entityManager;

		$this->registerBadges();
	}

	// used to add auto badges
	public function handleInfection($profile)
	{
		$badges = $profile->getBadges();

		foreach($autoRegistry as $badge)
		{
			if($badge['function']($profile))
			{
				$badges[$badge['id']] = true;
			}
		}

		$profile->setBadges($badges);
		$entityManager->flush();
	}

	// used to add manual badges
	public function addBadge($profile, $id)
	{
		if(!array_key_exists($id, $manualRegistry))
		{
			throw new InvalidArgumentException("Unknown badge id " . $id);
		}

		$badges = $profile->getBadges();
		$badges[$id] = true;
		$profile->setBadges($badges);
		$entityManager->flush();
	}

	/**
	 * @param id badge id
	 * @param name human-readable name
	 * @param description human-readable description
	 * @param imgPath path relative to public/images/badges
	 * @param auto if true this badge will automatically be applied to players
	 * @param function the auto function
	 */
	public function registerBadge($id, $name, $description, $imgPath, $auto = false, $function = null)
	{
		if($auto)
		{
			$this->autoRegistry[$id] = array(
				'name' => $name,
				'image' => $imgPath,
				'function' => $function
			);
		}
		else
		{
			$this->manualRegistry[$id] = array(
				'name' => $name,
				'image' => $imgPath,
			);
		}
	}

	public function registerBadges()
	{
		// double kill badge
		registerBadge(
			'kill-2x',
			'Double Kill',
			'Killed two humans within one hour',
			'test1.gif',
			true,
			function($profile) {
				$qb = $qb = $this->getEntityManager()->createQueryBuilder();
				$query = $qb->select('count(s.id)')
							->from('HvzGameBundle:InfectionSpread', 's')
							->where('s.zombie = :zombie')
							->setParameter('zombie', $profile)
							->getQuery();

				if($query->getSingleScalarResult() >= 2)
				{
					return true;
				}

				return false;
			}
		);
	}
}
