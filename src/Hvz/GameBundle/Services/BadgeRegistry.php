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

		// got extracted
		$this->registerBadge(
			'extraction',
			'Extraction',
			'Escaped safely from the horde!',
			'extraction.png'
		);

		// killstreaks
		$this->registerBadge(
			'streak-2',
			'Double Kill',
			'2 kills within an hour',
			'streak-2.png'
		);

		$this->registerBadge(
			'streak-3',
			'Triple Kill',
			'3 kills within an hour',
			'streak-3.png'
		);

		$this->registerBadge(
			'streak-4',
			'Overkill',
			'4 kills within an hour',
			'streak-4.png'
		);

		$this->registerBadge(
			'streak-5',
			'Killtacular',
			'5 kills within an hour',
			'streak-5.png'
		);

		$this->registerBadge(
			'streak-6',
			'Killtrocity',
			'6 kills within an hour',
			'streak-6.png'
		);

		$this->registerBadge(
			'streak-7',
			'Killmanjaro',
			'7 kills within an hour',
			'streak-7.png'
		);

		$this->registerBadge(
			'streak-8',
			'Killtastrophy',
			'8 kills within an hour',
			'streak-8.png'
		);

		$this->registerBadge(
			'streak-9',
			'Killpocalypse',
			'9 kills within an hour',
			'streak-9.png'
		);

		$this->registerBadge(
			'streak-10',
			'Killionare',
			'10 kills within an hour',
			'streak-10.png'
		);

		// found Sam working on the site during the game
		$this->registerBadge(
			'found-the-dev',
			'Found the Developer',
			'Found the web developer in his natural habitat',
			'found-the-dev.png'
		);
	}
}
