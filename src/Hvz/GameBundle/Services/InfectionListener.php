<?php

namespace Hvz\GameBundle\Services;

class InfectionListener
{
	private $entityManager;
	private $badgeRegistry;

	private $listeners = array();

	public function __construct($entityManager, $badgeRegistry)
	{
		$this->entityManager = $entityManager;
		$this->badgeRegistry = $badgeRegistry;

		$this->registerListeners();
	}

	public function onInfection($victim, $zombie)
	{
		foreach($this->listeners as $listener)
		{
			$listener($victim, $zombie);
		}
	}

	public function registerListener($function)
	{
		$this->listeners[] = $function;
	}

	private function registerListeners()
	{
		$this->registerListener(function($victim, $zombie) {
			$this->badgeRegistry->addBadge($victim, 'infected', false);
		});
	}
};
