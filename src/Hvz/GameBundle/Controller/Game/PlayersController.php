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
		if($game == null)
			$playerEnts = array();
		else if($sortBy == null)
			$playerEnts = $profileRepo->findActiveOrderedByNumberTaggedAndTeam($game, $page);
		else if($sortBy == 'clan')
			$playerEnts = $profileRepo->findActiveOrderedByClan($game, $page);

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

		$count = $profileRepo->findCountByGame($game);

		$content = $this->renderView(
			'HvzGameBundle:Game:players.html.twig',
			array(
				'navigation' => $this->get('hvz.navigation')->generate("players"),
				'players' => $players,
				'previous_page' => $page <= 0 ? -1 : $page - 1,
				'next_page' => $page >= ($count / 10 - 1) ? -1 : $page + 1
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
				'navigation' => $this->get('hvz.navigation')->generate("players"),
				'players' => $players
			)
		);

		return new Response($content);
	}

	public function tagsAction()
	{
		$game = $this->getDoctrine()->getRepository('HvzGameBundle:Game')->findCurrentGame();
		if($game == null)
			$tagEnts = array();
		else
			$tagEnts = $this->getDoctrine()->getManager()->getRepository('HvzGameBundle:InfectionSpread')->findByGameOrderedByTime($game);
		$tags = array();
		foreach($tagEnts as $tag)
		{
			$tags[] = array(
				"victim" => $tag->getVictim()->getUser()->getFullname(),
				"zombie" => $tag->getZombie()->getUser()->getFullname(),
				"time" => $tag->getTime()->format("l h:i:s A")
			);
		}

		$content = $this->renderView(
			'HvzGameBundle:Game:tags.html.twig',
			array(
				'navigation' => $this->get('hvz.navigation')->generate("tags"),
				"tags" => $tags
			)
		);

		return new Response($content);
	}
}
