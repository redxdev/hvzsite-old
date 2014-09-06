<?php

namespace Hvz\GameBundle\Services;

use Hvz\GameBundle\Entity\ActionLog;

class ActionLogService
{
	const TYPE_GAME = "game";
	const TYPE_ADMIN = "admin";
	const TYPE_AUTH = "auth";
	const TYPE_PROFILE = "profile";

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
