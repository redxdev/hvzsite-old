<?php

namespace Hvz\GameBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

use Hvz\GameBundle\Entity\User;

use Hvz\GameBundle\Services\ActionLogService;

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
			$game = $this->getDoctrine()->getRepository('HvzGameBundle:Game')->findNextGame();
		}

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
				"time" => $infection->getTime()
			);
		}

		$content = $this->renderView(
			'HvzGameBundle:Profile:index.html.twig',
			array(
				"profile" => array(
					"team" => $profile->getTeam() == User::TEAM_HUMAN ? 'human' : 'zombie',
					"tags" => $profile->getNumberTagged(),
					"player_id" => $profile->getTagId(),
					"id_tags" => $tagIds,
					"infections" => $infections,
					"clan" => $profile->getClan(),
					'badges' => $this->get('hvz.badge_registry')->getBadges($profile)
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
			$game = $this->getDoctrine()->getRepository('HvzGameBundle:Game')->findNextGame();
		}

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
                'HvzGameBundle:Profile:edit_clan.html.twig',
				array (
					"profile" => array(
						"clan" => $profile->getClan()
					)
				)
			);

			return new Response($content);
		}
		else
		{
			$actlog = $this->get('hvz.action_log');
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

			if(strlen($newClan) > 32)
			{
				$this->get('session')->getFlashBag()->add(
					'page.toast',
					"Your clan name is too long. Please shorten it and try again."
				);

				return $this->redirect($this->generateUrl('hvz_profile_edit_clan'));
			}

			$profile->setClan($newClan);

			$actlog->recordAction(
				ActionLogService::TYPE_PROFILE,
				'email:' . $profile->getUser()->getEmail(),
				'changed clan: ' . $newClan,
				false
			);

			$this->getDoctrine()->getManager()->flush();

			$this->get('session')->getFlashBag()->add(
				'page.toast',
				"Changed clan successfully."
			);

			return $this->redirect($this->generateUrl('hvz_profile'));
		}
	}
}
