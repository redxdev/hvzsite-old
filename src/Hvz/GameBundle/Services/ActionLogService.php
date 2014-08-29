<?php

namespace Hvz\GameBundle\Services;

use Hvz\GameBundle\Entity\ActionLog;

class ActionLogService
{
	const TYPE_USER = "user";
	const TYPE_ADMIN = "admin";

	private $entityManager;

	public function __construct($entityManager)
	{
		$this->entityManager = $entityManager;
	}

	public function recordAction($type, $user, $action, $flush = true)
	{
		$log = new ActionLog();
		$log->setType($type);
		$log->setUser($user);
		$log->setAction($action);

		$this->entityManager->persist($log);
		if($flush)
			$this->entityManager->flush();
	}
}
