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
				"navigation" => $this->get('hvz.navigation')->generate("profile"),
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
					"navigation" => $this->get('hvz.navigation')->generate("profile"),
					"profile" => array(
						"clan" => $profile->getClan()
					)
				)
			);

			return new Response($content);
		}
		else
		{
			$csrf = $this->get('form.csrf_provider');
			$token = $request->get('_token');

			if(!$csrf->isCsrfTokenValid('hvz_edit_clan', $token))
			{
				$this->get('session')->getFlashBag()->add(
					'page.toast',
					"Invalid CSRF token: Try resubmitting the form."
				);

				return $this->redirect($this->generateUrl('hvz_profile_edit_clan'));
			}

			if($newClan == 'null')
				$newClan = "";

			$profile->setClan($newClan);
			$this->getDoctrine()->getManager()->flush();

			$this->get('session')->getFlashBag()->add(
				'page.toast',
				"Changed clan successfully."
			);

			return $this->redirect($this->generateUrl('hvz_profile'));
		}
	}
}
