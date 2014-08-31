<?php

namespace Hvz\GameBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

class MissionsController extends Controller
{
	public function indexAction()
	{
		$securityContext = $this->get('security.context');

		if(!$securityContext->isGranted("ROLE_USER"))
		{
			return $this->redirect($this->generateUrl('hvz_error_403'));
		}

		$game = $this->getDoctrine()->getRepository('HvzGameBundle:Game')->findCurrentGame();
		if($game == null)
		{
			return $this->redirect($this->generateUrl('hvz_error_game'));
		}

		$profile = $this->getDoctrine()->getRepository('HvzGameBundle:Profile')->findByGameAndUser($game, $securityContext->getToken()->getUser());

		if(!$profile || !$profile->getActive())
		{
			return $this->redirect($this->generateUrl('hvz_error_active'));
		}

		$missionEnts = $this->getDoctrine()->getManager()->getRepository('HvzGameBundle:Mission')->findPostedByTeamOrderedByDate($game, $profile->getTeam());
		$missions = array();
		foreach($missionEnts as $mission)
		{
			$missions[] = array(
				"title" => $mission->getTitle(),
				"body" => $mission->getBody(),
				"team" => $mission->getTeam() == User::TEAM_HUMAN ? 'human' : 'zombie'
			);
		}

		$content = $this->renderView(
			'HvzGameBundle:Game:missions.html.twig',
			array(
				'navigation' => $this->get('hvz.navigation')->generate("missions"),
				"missions" => $missions
			)
		);

		return new Response($content);
	}
}
