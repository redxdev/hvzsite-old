<?php

namespace Hvz\GameBundle\Services;

class ActionLogService
{
	private $entityManager;

	public function __construct($entityManager)
	{
		$this->entityManager = $entityManager;
	}
}
