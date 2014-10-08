<?php

namespace Hvz\GameBundle\Controller\Game;

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

		$content = $this->renderView(
			'HvzGameBundle:Game:status.html.twig',
			array(
				'game' => array(
					'humans' => $humans,
					'zombies' => $zombies,
					'time' => $time
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
				"message" => array(
					"type" => "error",
					"body" => "The game hasn't started yet, so you can't view this page!"
				)
			)
		);

		return new Response($content);
	}
}
