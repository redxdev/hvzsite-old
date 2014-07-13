<?php

namespace Hvz\GameBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

use Hvz\GameBundle\Entity\User;

class ProfileController extends Controller
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
			return $this->redirect($this->generateUrl('hvz_error_active'));
		}

		$profile = $this->getDoctrine()->getRepository('HvzGameBundle:Profile')->findByGameAndUser($game, $securityContext->getToken()->getUser());

		if(!$profile || !$profile->getActive())
		{
			return $this->redirect($this->generateUrl('hvz_error_active'));
		}
		
		$tagEnts = $profile->getIdTags();
		$tagIds = array();
		foreach($tagEnts as $id)
		{
			$tagIds[] = array("tag" => $id->getTag(), "active" => $id->getActive());
		}
		
		$infectionEnts = $this->getDoctrine()->getRepository('HvzGameBundle:InfectionSpread')->findByZombieOrderedByTime($game, $profile);
		$infections = array();
		foreach($infectionEnts as $infection)
		{
			$infections[] = array(
				"victim" => $infection->getVictim()->getUser()->getFullname(),
				"time" => $infection->getTime()->format("l h:i:s A")
			);
		}

		$content = $this->renderView(
			'HvzGameBundle:Profile:index.html.twig',
			array(
				"navigation" => \Hvz\GameBundle\HvzGameBundle::generateNavigation("profile", $this->get("router"), $this->get('security.context')),
				"profile" => array(
					"team" => $profile->getTeam() == User::TEAM_HUMAN ? 'human' : 'zombie',
					"tags" => $profile->getNumberTagged(),
					"player_id" => $profile->getTagId(),
					"id_tags" => $tagIds,
					"infections" => $infections,
					"clan" => $profile->getClan()
				)
			)
		);

		return new Response($content);
	}
	
	public function editClanAction(Request $request)
	{
		$securityContext = $this->get('security.context');

		if(!$securityContext->isGranted("ROLE_USER"))
		{
			return $this->redirect($this->generateUrl('hvz_error_403'));
		}

		$game = $this->getDoctrine()->getRepository('HvzGameBundle:Game')->findCurrentGame();

		if($game == null)
		{
			return $this->redirect($this->generateUrl('hvz_error_active'));
		}

		$profile = $this->getDoctrine()->getRepository('HvzGameBundle:Profile')->findByGameAndUser($game, $securityContext->getToken()->getUser());

		if(!$profile || !$profile->getActive())
		{
			return $this->redirect($this->generateUrl('hvz_error_active'));
		}
		
		$newClan = $request->request->get('clan');
		
		if($newClan == null)
		{
			$content = $this->renderView(
				"HvzGameBundle:Profile:edit_clan.html.twig",
				array (
					"navigation" => \Hvz\GameBundle\HvzGameBundle::generateNavigation("profile", $this->get("router"), $this->get('security.context')),
					"profile" => array(
						"clan" => $profile->getClan()
					)
				)
			);
			
			return new Response($content);
		}
		else
		{
			if($newClan == 'null')
				$newClan = "";
			
			$profile->setClan($newClan);
			$this->getDoctrine()->getManager()->flush();
			
			return $this->redirect($this->generateUrl('hvz_profile'));
		}
	}
}
