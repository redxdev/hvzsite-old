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

class UserController extends Controller
{
	public function indexAction($page = 0)
	{
		$securityContext = $this->get('security.context');

		if(!$securityContext->isGranted("ROLE_MOD"))
		{
			return $this->redirect($this->generateUrl('hvz_error_403'));
		}

		$userRepo = $this->getDoctrine()->getRepository('HvzGameBundle:User');
		$userEnts = $userRepo->findByPage($page, 10);
		$users = array();
		foreach($userEnts as $user)
		{
			$users[] = array(
				'id' => $user->getId(),
				'fullname' => $user->getFullname(),
				'email' => $user->getEmail(),
				'access' => implode(' ', $user->getRoles()),
				'profiles' => count($user->getProfiles())
			);
		}

		$count = $userRepo->findCount();

		$content = $this->renderView(
			'HvzGameBundle:Admin:users.html.twig',
			array(
				'navigation' => $this->get('hvz.navigation')->generate(""),
				'users' => $users,
				'previous_page' => $page <= 0 ? -1 : $page - 1,
				'next_page' => $page >= ($count / 10 - 1) ? -1 : $page + 1
			)
		);

		return new Response($content);
	}

	public function searchAction(Request $request)
	{
		$securityContext = $this->get('security.context');

		if(!$securityContext->isGranted("ROLE_MOD"))
		{
			return $this->redirect($this->generateUrl('hvz_error_403'));
		}

		$term = $request->get('term', NULL);
		if($term === NULL)
		{
			return $this->redirect($this->generateUrl('hvz_admin_users'));
		}

		$userRepo = $this->getDoctrine()->getRepository('HvzGameBundle:User');
		$userEnts = $userRepo->findInSearchableFields($term);
		$users = array();
		foreach($userEnts as $user)
		{
			$users[] = array(
				'id' => $user->getId(),
				'fullname' => $user->getFullname(),
				'email' => $user->getEmail(),
				'access' => implode(' ', $user->getRoles()),
				'profiles' => count($user->getProfiles())
			);
		}

		$content = $this->renderView(
			'HvzGameBundle:Admin:users.html.twig',
			array(
				'navigation' => $this->get('hvz.navigation')->generate(""),
				'users' => $users
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

		$user = $this->getDoctrine()->getRepository('HvzGameBundle:User')->findOneById($id);

		if($user == null)
		{
			$this->get('session')->getFlashBag()->add(
				'page.toast',
				"Unknown user id."
			);

			return $this->redirect($this->generateUrl('hvz_admin_users'));
		}

		$form = $this->createFormBuilder($user)
			->add('fullname', 'text')
			->add('email', 'email')
			->add('roles', 'collection')
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
				'edited user: email:' . $user->getEmail(),
				false
			);

			$em->flush();

			$this->get('session')->getFlashBag()->add(
				'page.toast',
				"Edited user successfully."
			);

			return $this->redirect($this->generateUrl('hvz_admin_users'));
		}

		$content = $this->renderView(
			'HvzGameBundle:Admin:edit_user.html.twig',
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
		if(!$csrf->isCsrfTokenValid('hvz_user_delete', $token))
		{
			$this->get('session')->getFlashBag()->add(
				'page.toast',
				"Invalid CSRF token. Please try again."
			);

			return $this->redirect($this->generateUrl('hvz_admin_users'));
		}

		$user = $this->getDoctrine()->getRepository('HvzGameBundle:User')->findOneById($id);

		if($user == null)
		{
			$this->get('session')->getFlashBag()->add(
				'page.toast',
				"Unknown user id."
			);

			return $this->redirect($this->generateUrl('hvz_admin_users'));
		}

		$actlog = $this->get('hvz.action_log');
		$em = $this->getDoctrine()->getManager();

		$actlog->recordAction(
			ActionLogService::TYPE_ADMIN,
			'email:' . $this->get('security.context')->getToken()->getUser()->getEmail(),
			'deleted user: email:' . $user->getEmail(),
			false
		);

		$em->remove($user);

		$em->flush();

		$this->get('session')->getFlashBag()->add(
			'page.toast',
			"Deleted user successfully."
		);

		return $this->redirect($this->generateUrl('hvz_admin_users'));
	}
}
