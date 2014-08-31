<?php

namespace Hvz\GameBundle\Controller\Admin;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

use Hvz\GameBundle\Entity\Game;
use Hvz\GameBundle\Entity\User;
use Hvz\GameBundle\Entity\PlayerTag;
use Hvz\GameBundle\Entity\Profile;
use Hvz\GameBundle\Entity\Mission;
use Hvz\GameBundle\Entity\Post;
use Hvz\GameBundle\Entity\Rule;
use Hvz\GameBundle\Entity\AntiVirusTag;

use Hvz\GameBundle\Services\ActionLogService;

class MissionController extends Controller
{
	public function indexAction($game = null)
	{
		$securityContext = $this->get('security.context');

		if(!$securityContext->isGranted("ROLE_ADMIN"))
		{
			return $this->redirect($this->generateUrl('hvz_error_403'));
		}

		$missionRepo = $this->getDoctrine()->getRepository('HvzGameBundle:Mission');
		$missionEnts = array();
		if($game != null)
		{
			$missionEnts = $missionRepo->findByGameOrderedByDate($game);
		}
		else
		{
			$missionEnts = $missionRepo->findAllOrderedByDate();
		}

		$missions = array();
		foreach($missionEnts as $mission)
		{
			$missions[] = array(
				'id' => $mission->getId(),
				'title' => $mission->getTitle(),
				'game' => $mission->getGame(),
				'team' => $mission->getTeam() == User::TEAM_HUMAN ? 'human' : 'zombie',
				'postdate' => $mission->getPostdate()->format('Y D M j h:i:s A'),
				'body' => $mission->getBody()
			);
		}

		$content = $this->renderView(
			'HvzGameBundle:Admin:missions.html.twig',
			array(
				'navigation' => $this->get('hvz.navigation')->generate(""),
				'missions' => $missions
			)
		);

		return new Response($content);
	}

	public function editAction(Request $request, $id)
	{
		$securityContext = $this->get('security.context');

		if(!$securityContext->isGranted("ROLE_ADMIN"))
		{
			return $this->redirect($this->generateUrl('hvz_error_403'));
		}

		$mission = $this->getDoctrine()->getRepository('HvzGameBundle:Mission')->findOneById($id);

		if($mission == null)
		{
			$this->get('session')->getFlashBag()->add(
				'page.toast',
				"Unknown mission id."
			);

			return $this->redirect($this->generateUrl('hvz_admin_missions'));
		}

		$form = $this->createFormBuilder($mission)
			->add('title', 'text')
			->add('body', 'textarea')
			->add('postdate', 'datetime')
			->add('team', 'choice', array(
				'choices' => array(
					User::TEAM_HUMAN => 'Human',
					User::TEAM_ZOMBIE => 'Zombie'
				)
			))
			->add('game', 'entity', array(
				'class' => 'HvzGameBundle:Game'
			))
			->add('save', 'submit')
			->getForm();

		$form->handleRequest($request);

		if($form->isValid())
		{
			$em = $this->getDoctrine()->getManager();

			$actlog = $this->get('hvz.action_log');
			$actlog->recordAction(
				ActionLogService::TYPE_ADMIN,
				'email:' . $this->get('security.context')->getToken()->getUser()->getEmail(),
				'edited mission: ' . $mission->getId(),
				false
			);

			$em->flush();

			$this->get('session')->getFlashBag()->add(
				'page.toast',
				"Edited mission successfully."
			);

			return $this->redirect($this->generateUrl('hvz_admin_missions'));
		}

		$content = $this->renderView(
			'HvzGameBundle:Admin:edit_mission.html.twig',
			array(
				'navigation' => $this->get('hvz.navigation')->generate(""),
				'form' => $form->createView()
			)
		);

		return new Response($content);
	}

	public function createAction(Request $request)
	{
		$securityContext = $this->get('security.context');

		if(!$securityContext->isGranted("ROLE_ADMIN"))
		{
			return $this->redirect($this->generateUrl('hvz_error_403'));
		}

		$mission = new Mission();

		$form = $this->createFormBuilder($mission)
			->add('title', 'text')
			->add('body', 'textarea')
			->add('postdate', 'datetime')
			->add('team', 'choice', array(
				'choices' => array(
					User::TEAM_HUMAN => 'Human',
					User::TEAM_ZOMBIE => 'Zombie'
				)
			))
			->add('game', 'entity', array(
				'class' => 'HvzGameBundle:Game'
			))
			->add('save', 'submit')
			->getForm();

		$form->handleRequest($request);

		if($form->isValid())
		{
			$em = $this->getDoctrine()->getManager();
			$em->persist($mission);

			$actlog = $this->get('hvz.action_log');
			$actlog->recordAction(
				ActionLogService::TYPE_ADMIN,
				'email:' . $this->get('security.context')->getToken()->getUser()->getEmail(),
				'created mission',
				false
			);

			$em->flush();

			$this->get('session')->getFlashBag()->add(
				'page.toast',
				"Created mission successfully."
			);

			return $this->redirect($this->generateUrl('hvz_admin_missions'));
		}

		$content = $this->renderView(
			'HvzGameBundle:Admin:edit_mission.html.twig',
			array(
				'navigation' => $this->get('hvz.navigation')->generate(""),
				'form' => $form->createView()
			)
		);

		return new Response($content);
	}

	public function deleteAction($id, $token)
	{
		$securityContext = $this->get('security.context');

		if(!$securityContext->isGranted("ROLE_ADMIN"))
		{
			return $this->redirect($this->generateUrl('hvz_error_403'));
		}

		$csrf = $this->get('form.csrf_provider');
		if(!$csrf->isCsrfTokenValid('hvz_mission_delete', $token))
		{
			$this->get('session')->getFlashBag()->add(
				'page.toast',
				"Invalid CSRF token. Please try again."
			);

			return $this->redirect($this->generateUrl('hvz_admin_missions'));
		}

		$mission = $this->getDoctrine()->getRepository('HvzGameBundle:Mission')->findOneById($id);

		if($mission == null)
		{
			$this->get('session')->getFlashBag()->add(
				'page.toast',
				"Unknown mission id."
			);

			return $this->redirect($this->generateUrl('hvz_admin_missions'));
		}

		$em = $this->getDoctrine()->getManager();

		$actlog = $this->get('hvz.action_log');
		$actlog->recordAction(
			ActionLogService::TYPE_ADMIN,
			'email:' . $this->get('security.context')->getToken()->getUser()->getEmail(),
			'deleted mission: ' . $mission->getId(),
			false
		);

		$em->remove($mission);

		$em->flush();

		$this->get('session')->getFlashBag()->add(
			'page.toast',
			"Deleted mission successfully."
		);

		return $this->redirect($this->generateUrl('hvz_admin_missions'));
	}
}
