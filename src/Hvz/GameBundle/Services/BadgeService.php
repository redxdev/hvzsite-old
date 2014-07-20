<?php

namespace Hvz\GameBundle\Services;

class BadgeService
{
	private $entityManager;

	private $badgeTypes = array();

	public function __construct($entityManager)
	{
		$this->entityManager = $entityManager;
	}
}
