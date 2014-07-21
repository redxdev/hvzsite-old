<?php

namespace Hvz\GameBundle\Services;

class BadgeRegistry
{
	private $entityManager;

	private $registry = array();

	public function __construct($entityManager)
	{
		$this->entityManager = $entityManager;

		$this->registerBadges();
	}

	public function addBadge($profile, $id, $flush = true)
	{
		if(!array_key_exists($id, $this->registry))
		{
			throw new InvalidArgumentException("Unknown badge id " . $id);
		}

		$badges = $profile->getBadges();
		$badges[$id] = true;
		$profile->setBadges($badges);

		if($flush)
			$this->entityManager->flush();
	}

	public function getBadge($id)
	{
		return $this->registry[$id];
	}

	public function getBadges($profile)
	{
		$pb = $profile->getBadges();
		$badges = array();
		foreach($pb as $id)
		{
			$badges[] = getBadge($id);
		}

		return $badges;
	}

	/**
	 * @param id badge id
	 * @param name human-readable name
	 * @param description human-readable description
	 * @param imgPath path relative to public/images/badges
	 */
	public function registerBadge($id, $name, $description, $imgPath)
	{
		$this->registry[$id] = array(
			'name' => $name,
			'image' => $imgPath,
			'description' => $description
		);
	}

	public function registerBadges()
	{
		// infection badge
		$this->registerBadge(
			'infected',
			'Infected',
			'Died in the zombie apocalypse',
			'test1.gif'
		);

		// used an AV
		$this->registerBadge(
			'used-av',
			'Used AV',
			'Used an AV code to become human again',
			'test1.gif'
		);
	}
}
