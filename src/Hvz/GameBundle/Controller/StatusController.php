<?php

namespace Hvz\GameBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

class StatusController extends Controller
{
	public function indexAction()
	{
		$gameRepo = $this->getDoctrine()->getRepository('HvzGameBundle:Game');
		$game = $gameRepo->findCurrentGame();
		$time = null;
		if($game != null) {
			$now = date_create("now");
			$gameTime = ($now->getTimestamp() - $game->getStartDate()->getTimestamp() > 0) ? $game->getEndDate() : $game->getStartDate();
			$diff = $now->diff($gameTime);
			$time = array(
				'days' => $diff->d,
				'hours' => $diff->h,
				'minutes' => $diff->i,
				'seconds' => $diff->s,
				'timestamp' => $gameTime->getTimestamp()
			);
		}
		else {
			$game = $gameRepo->findNextGame();
			if($game != null) {
				$now = date_create("now");
				$gameTime = $game->getStartDate();
				$diff = $now->diff($gameTime);
				$time = array(
					'days' => $diff->d,
					'hours' => $diff->h,
					'minutes' => $diff->i,
					'seconds' => $diff->s,
					'timestamp' => $gameTime->getTimestamp()
				);
			}
		}

		$profileRepo = $this->getDoctrine()->getRepository('HvzGameBundle:Profile');

		$humans = count($profileRepo->findActiveHumans($game));
		$zombies = count($profileRepo->findActiveZombies($game));
		$total = $humans + $zombies;
		$human_percent = 0;
		$zombie_percent = 0;
		if($total != 0)
		{
			$human_percent = round($humans / $total * 100, 1);
			$zombie_percent = round($zombies / $total * 100, 1);
		}

		$content = $this->renderView(
			'HvzGameBundle:Game:status.html.twig',
			array(
				'navigation' => $this->get('hvz.navigation')->generate("status"),
				'game' => array(
					'humans' => $humans,
					'zombies' => $zombies,
					'time' => $time,
					'human_percent' => $human_percent,
					'zombie_percent' => $zombie_percent
				)
			)
		);

		return new Response($content);
	}

	public function gameErrorAction()
	{
		$content = $this->renderView(
			'HvzGameBundle::message.html.twig',
			array(
				'navigation' => $this->get('hvz.navigation')->generate(""),
				"message" => array(
					"type" => "error",
					"body" => "The game hasn't started yet, so you can't view this page!"
				)
			)
		);

		return new Response($content);
	}
}
