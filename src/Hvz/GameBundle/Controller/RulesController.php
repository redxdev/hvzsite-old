<?php

namespace Hvz\GameBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

class RulesController extends Controller
{
	public function indexAction()
	{
		$logger = $this->get('logger');

		$ruleEnts = $this->getDoctrine()->getRepository('HvzGameBundle:Rule')->findAllOrderedByPosition();
		$rules = array();
		foreach($ruleEnts as $rule)
		{
			$rules[] = array(
				"title" => $rule->getTitle(),
				"body" => $rule->getBody()
			);
		}

		$content = $this->renderView(
			'HvzGameBundle:Game:rules.html.twig',
			array(
				"navigation" => $this->get('hvz.navigation')->generate("rules"),
				"rules" => $rules
			)
		);

		return new Response($content);
	}
}
