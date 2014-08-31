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

class RulesController extends Controller
{
	public function indexAction()
	{
		$securityContext = $this->get('security.context');

		if(!$securityContext->isGranted("ROLE_ADMIN"))
		{
			return $this->redirect($this->generateUrl('hvz_error_403'));
		}

		$ruleRepo = $this->getDoctrine()->getRepository('HvzGameBundle:Rule');
		$ruleEnts = $ruleRepo->findAllOrderedByPosition();
		$rules = array();
		foreach($ruleEnts as $rule)
		{
			$rules[] = array(
				'id' => $rule->getId(),
				'title' => $rule->getTitle(),
				'position' => $rule->getPosition(),
				'body' => $rule->getBody()
			);
		}

		$content = $this->renderView(
			'HvzGameBundle:Admin:rules.html.twig',
			array(
				'navigation' => $this->get('hvz.navigation')->generate(""),
				'rules' => $rules
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

		$rule = $this->getDoctrine()->getRepository('HvzGameBundle:Rule')->findOneById($id);

		if($rule == null)
		{
			$this->get('session')->getFlashBag()->add(
				'page.toast',
				"Unknown ruleset id."
			);

			return $this->redirect($this->generateUrl('hvz_admin_rules'));
		}

		$form = $this->createFormBuilder($rule)
			->add('title', 'text')
			->add('position', 'number', array('precision' => 0))
			->add('body', 'textarea')
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
				'edited ruleset: ' . $rule->getId(),
				false
			);

			$em->flush();

			$this->get('session')->getFlashBag()->add(
				'page.toast',
				"Edited ruleset successfully."
			);

			return $this->redirect($this->generateUrl('hvz_admin_rules'));
		}

		$content = $this->renderView(
			'HvzGameBundle:Admin:edit_rule.html.twig',
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

		$rule = new Rule();
		$rule->setPosition(0);

		$form = $this->createFormBuilder($rule)
			->add('title', 'text')
			->add('position', 'number', array('precision' => 0))
			->add('body', 'textarea')
			->add('save', 'submit')
			->getForm();

		$form->handleRequest($request);

		if($form->isValid())
		{
			$em = $this->getDoctrine()->getManager();
			$em->persist($rule);

			$actlog = $this->get('hvz.action_log');
			$actlog->recordAction(
				ActionLogService::TYPE_ADMIN,
				'email:' . $this->get('security.context')->getToken()->getUser()->getEmail(),
				'created ruleset',
				false
			);

			$em->flush();

			$this->get('session')->getFlashBag()->add(
				'page.toast',
				"Created ruleset successfully."
			);

			return $this->redirect($this->generateUrl('hvz_admin_rules'));
		}

		$content = $this->renderView(
			'HvzGameBundle:Admin:edit_rule.html.twig',
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
		if(!$csrf->isCsrfTokenValid('hvz_rule_delete', $token))
		{
			$this->get('session')->getFlashBag()->add(
				'page.toast',
				"Invalid CSRF token. Please try again."
			);

			return $this->redirect($this->generateUrl('hvz_admin_rules'));
		}

		$rule = $this->getDoctrine()->getRepository('HvzGameBundle:Rule')->findOneById($id);

		if($rule == null)
		{
			$this->get('session')->getFlashBag()->add(
				'page.toast',
				"Unknown ruleset id."
			);

			return $this->redirect($this->generateUrl('hvz_admin_rules'));
		}

		$em = $this->getDoctrine()->getManager();

		$actlog = $this->get('hvz.action_log');
		$actlog->recordAction(
			ActionLogService::TYPE_ADMIN,
			'email:' . $this->get('security.context')->getToken()->getUser()->getEmail(),
			'deleted ruleset: ' . $rule->getId(),
			false
		);

		$em->remove($rule);

		$em->flush();

		$this->get('session')->getFlashBag()->add(
			'page.toast',
			"Deleted ruleset successfully."
		);

		return $this->redirect($this->generateUrl('hvz_admin_rules'));
	}
}
