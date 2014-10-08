<?php

namespace Hvz\GameBundle\Controller\Game;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

use Hvz\GameBundle\Entity\User;

class PlayersController extends Controller
{
	public function indexAction(Request $request, $page)
	{
		$game = $this->getDoctrine()->getRepository('HvzGameBundle:Game')->findCurrentGame();

		if($game == null)
			$game = $this->getDoctrine()->getRepository('HvzGameBundle:Game')->findNextGame();

		$profileRepo = $this->getDoctrine()->getRepository('HvzGameBundle:Profile');

		$sortBy = $request->get('sort');
		$playerEnts = array();
		$count = 0;
		if($game == null)
			$playerEnts = array();
		else if($sortBy == null)
		{
			$playerEnts = $profileRepo->findActiveOrderedByNumberTaggedAndTeam($game, $page);
			$count = $profileRepo->findCountByGame($game);
		}
		else if($sortBy == 'clan')
		{
			$playerEnts = $profileRepo->findActiveOrderedByClan($game, $page);
			$count = $profileRepo->findActiveClanCount($game);
		}
		else if($sortBy == 'mods')
		{
			$playerEnts = $profileRepo->findActiveMods($game, $page);
			$count = $profileRepo->findActiveModsCount($game);
		}

		$badgeReg = $this->get('hvz.badge_registry');

		$players = array();
		foreach($playerEnts as $player)
		{
			$badges = $badgeReg->getBadges($player);

			$players[] = array(
				'fullname' => $player->getUser()->getFullname(),
				'team' => ($player->getTeam() == User::TEAM_HUMAN ? 'human' : 'zombie'),
				'tags' => $player->getNumberTagged(),
				'clan' => $player->getClan(),
				'badges' => $badges,
				'avatar' => $player->getWebAvatarPath()
			);
		}

		$content = $this->renderView(
			'HvzGameBundle:Game:players.html.twig',
			array(
				'players' => $players,
				'previous_page' => $page <= 0 ? -1 : $page - 1,
				'next_page' => $page >= ($count / 10 - 1) ? -1 : $page + 1,
				'sort' => $sortBy
			)
		);

		return new Response($content);
	}

	public function searchAction(Request $request)
	{
		$term = $request->get('term', NULL);
		if($term === NULL || empty($term))
		{
			return $this->redirect($this->generateUrl('hvz_players'));
		}

		$game = $this->getDoctrine()->getRepository('HvzGameBundle:Game')->findCurrentGame();

		if($game == null)
			$game = $this->getDoctrine()->getRepository('HvzGameBundle:Game')->findNextGame();

		$badgeReg = $this->get('hvz.badge_registry');

		$profileRepo = $this->getDoctrine()->getRepository('HvzGameBundle:Profile');
		$playerEnts = $profileRepo->findInSearchableFields($game, $term);
		$players = array();
		foreach($playerEnts as $player)
		{
			$badges = $badgeReg->getBadges($player);

			$players[] = array(
				'fullname' => $player->getUser()->getFullname(),
				'team' => ($player->getTeam() == User::TEAM_HUMAN ? 'human' : 'zombie'),
				'tags' => $player->getNumberTagged(),
				'clan' => $player->getClan(),
				'badges' => $badges,
				'avatar' => $player->getWebAvatarPath()
			);
		}

		$content = $this->renderView(
			'HvzGameBundle:Game:players.html.twig',
			array(
				'players' => $players
			)
		);

		return new Response($content);
	}

	public function tagsAction($page = 0)
	{
		$game = $this->getDoctrine()->getRepository('HvzGameBundle:Game')->findCurrentGame();
		if($game == null)
			$tagEnts = array();
		else
			$tagEnts = $this->getDoctrine()->getManager()->getRepository('HvzGameBundle:InfectionSpread')->findPageByGameOrderedByTime($game, $page);
		$tags = array();
		foreach($tagEnts as $tag)
		{
			$tags[] = array(
				"victim" => $tag->getVictim()->getUser()->getFullname(),
				"zombie" => $tag->getZombie()->getUser()->getFullname(),
				"time" => $tag->getTime()
			);
		}

		$count = $this->getDoctrine()->getManager()->getRepository('HvzGameBundle:InfectionSpread')->findCountByGame($game);

		$content = $this->renderView(
			'HvzGameBundle:Game:tags.html.twig',
			array(
				"tags" => $tags,
				'previous_page' => $page <= 0 ? -1 : $page - 1,
				'next_page' => $page >= ($count / 10 - 1) ? -1 : $page + 1
			)
		);

		return new Response($content);
	}
}
