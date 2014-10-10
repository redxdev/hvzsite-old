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

class GameController extends Controller
{
	public function indexAction()
	{
		$securityContext = $this->get('security.context');

		if(!$securityContext->isGranted("ROLE_MOD"))
		{
			return $this->redirect($this->generateUrl('hvz_error_403'));
		}

		$gameRepo = $this->getDoctrine()->getRepository('HvzGameBundle:Game');
		$profileRepo = $this->getDoctrine()->getRepository('HvzGameBundle:Profile');
		$avRepo = $this->getDoctrine()->getRepository('HvzGameBundle:AntiVirusTag');
		$gameEnts = $gameRepo->findAllOrderedByStartDate();
		$currentGame = $gameRepo->findCurrentGame();
		$games = array();
		foreach($gameEnts as $game)
		{
			$avCodes = array();
			if(!$securityContext->isGranted("ROLE_ADMIN"))
			{
				$avCodes[] = array('id' => "No permission", 'active' => true);
			}
			else
			{
				$avs = $avRepo->findByGame($game);
				foreach($avs as $av)
				{
					$avCodes[] = array('id' => $av->getTag(), 'active' => $av->getActive());
				}
			}

			$games[] = array(
				'id' => $game->getId(),
				'active' => $currentGame == null ? false : $game == $currentGame,
				'startDate' => $game,
				'endDate' => $game->getEndDate()->format('Y D M j h:i:s A'),
				'humans' => count($profileRepo->findActiveHumans($game)),
				'zombies' => count($profileRepo->findActiveZombies($game)),
				'profiles' => count($profileRepo->findByGame($game)),
				'missions' => count($game->getMissions()),
				'av_codes' => $avCodes
			);
		}

		$content = $this->renderView(
			'HvzGameBundle:Admin:games.html.twig',
			array(
				'games' => $games
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

		$game = new Game();

		$form = $this->createFormBuilder($game)
			->add('startDate', 'datetime')
			->add('endDate', 'datetime')
			->add('save', 'submit')
			->getForm();

		$form->handleRequest($request);

		if($form->isValid())
		{
			$actlog = $this->get('hvz.action_log');
			$em = $this->getDoctrine()->getManager();
			$em->persist($game);

			$actlog->recordAction(
				ActionLogService::TYPE_ADMIN,
				'email:' . $this->get('security.context')->getToken()->getUser()->getEmail(),
				'created new game',
				false
			);

			$em->flush();

			$this->get('session')->getFlashBag()->add(
				'page.toast',
				"Created new game successfully."
			);

			return $this->redirect($this->generateUrl('hvz_admin_games'));
		}

		$content = $this->renderView(
			'HvzGameBundle:Admin:edit_game.html.twig',
			array(
				'form' => $form->createView()
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

		$game = $this->getDoctrine()->getRepository('HvzGameBundle:Game')->findOneById($id);

		if($game == null)
		{
			$this->get('session')->getFlashBag()->add(
				'page.toast',
				"Unknown game id"
			);

			return $this->redirect($this->generateUrl('hvz_admin_games'));
		}

		$form = $this->createFormBuilder($game)
			->add('startDate', 'datetime')
			->add('endDate', 'datetime')
			->add('save', 'submit')
			->getForm();

		$form->handleRequest($request);

		if($form->isValid())
		{
			$actlog = $this->get('hvz.action_log');
			$em = $this->getDoctrine()->getManager();

			$actlog->recordAction(
				ActionLogService::TYPE_ADMIN,
				'email:' . $this->get('security.context')->getToken()->getUser()->getEmail(),
				'edited game: ' . $id,
				false
			);

			$em->flush();

			$this->get('session')->getFlashBag()->add(
				'page.toast',
				"Edited game successfully."
			);

			return $this->redirect($this->generateUrl('hvz_admin_games'));
		}

		$content = $this->renderView(
			'HvzGameBundle:Admin:edit_game.html.twig',
			array(
				'form' => $form->createView()
			)
		);

		return new Response($content);
	}

	public function addAntiVirusAction($id, $token)
	{
		$securityContext = $this->get('security.context');

		if(!$securityContext->isGranted("ROLE_ADMIN"))
		{
			return $this->redirect($this->generateUrl('hvz_error_403'));
		}

		$csrf = $this->get('form.csrf_provider');
		if(!$csrf->isCsrfTokenValid('hvz_antivirus_generate', $token))
		{
			$this->get('session')->getFlashBag()->add(
				'page.toast',
				"Invalid CSRF token. Please try again."
			);

			return $this->redirect($this->generateUrl('hvz_admin_games'));
		}

		$game = $this->getDoctrine()->getRepository('HvzGameBundle:Game')->findOneById($id);
		if($game == null)
		{
			$this->get('session')->getFlashBag()->add(
				'page.toast',
				"Unknown game id."
			);

			return $this->redirect($this->generateUrl('hvz_admin_games'));
		}

		$tagGen = $this->get('hvz.tag_generator');

		$newTag = new AntiVirusTag($tagGen->generate());
		$newTag->setGame($game);

		$actlog = $this->get('hvz.action_log');
		$em = $this->getDoctrine()->getManager();
		$em->persist($newTag);

		$actlog->recordAction(
			ActionLogService::TYPE_ADMIN,
			'email:' . $this->get('security.context')->getToken()->getUser()->getEmail(),
			'added antivirus to game ' . $id . ': ' . $newTag->getTag(),
			false
		);

		$em->flush();

		$this->get('session')->getFlashBag()->add(
			'page.toast',
			"Added new AV successfully."
		);

		return $this->redirect($this->generateUrl('hvz_admin_games'));
	}

	public function deleteAction($id, $token)
	{
		$securityContext = $this->get('security.context');

		if(!$securityContext->isGranted("ROLE_ADMIN"))
		{
			return $this->redirect($this->generateUrl('hvz_error_403'));
		}

		$csrf = $this->get('form.csrf_provider');
		if(!$csrf->isCsrfTokenValid('hvz_game_delete', $token))
		{
			$this->get('session')->getFlashBag()->add(
				'page.toast',
				"Invalid CSRF token. Please try again."
			);

			return $this->redirect($this->generateUrl('hvz_admin_games'));
		}

		$game = $this->getDoctrine()->getRepository('HvzGameBundle:Game')->findOneById($id);

		if($game == null)
		{
			$this->get('session')->getFlashBag()->add(
				'page.toast',
				"Unknown game id."
			);

			return $this->redirect($this->generateUrl('hvz_admin_games'));
		}

		$actlog = $this->get('hvz.action_log');
		$em = $this->getDoctrine()->getManager();
		$em->remove($game);

		$actlog->recordAction(
			ActionLogService::TYPE_ADMIN,
			'email:' . $this->get('security.context')->getToken()->getUser()->getEmail(),
			'deleted game: ' . $id,
			false
		);

		$em->flush();

		$this->get('session')->getFlashBag()->add(
			'page.toast',
			"Deleted game successfully."
		);

		return $this->redirect($this->generateUrl('hvz_admin_games'));
	}
}
