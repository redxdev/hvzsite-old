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
		$badges[] = $id;
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
		if($pb == null)
			return array();

		$badges = array();
		foreach($pb as $id)
		{
			$badges[] = $this->getBadge($id);
		}

		return $badges;
	}

	public function badgeExists($id)
	{
		return array_key_exists($id, $this->registry);
	}

	public function getRegistry()
	{
		return $this->registry;
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
			'infected.png'
		);

		// used an AV
		$this->registerBadge(
			'used-av',
			'Used AV',
			'Used an AV code to become human again',
			'antivirus.png'
		);

		// caught a human between 6 and 8 AM
		$this->registerBadge(
			'early-bird',
			'Early Bird',
			'Caught a human between 6 and 8 AM',
			'earlybird.png'
		);

		// died between 11 PM and midnight
		$this->registerBadge(
			'mission-aint-over',
			'Mission ain\'t Over',
			'Died between 11 PM and Midnight',
			'mission-aint-over.png'
		);

		// got a twinkie from a moderator
		$this->registerBadge(
			'twinkie',
			'Twinkie!',
			'Received a twinkie from a moderator',
			'twinkie.png'
		);

		// died on sunday night
		$this->registerBadge(
			'bad-start',
			'Bad Start',
			'Died on Sunday night',
			'badstart.png'
		);

		// died from thursday onward
		$this->registerBadge(
			'so-close',
			'So Close',
			'Died from Thursday onward',
			'close.png'
		);

		// killstreaks
	}
}
