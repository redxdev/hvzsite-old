<?php

namespace Hvz\GameBundle\Controller\Game;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

class RulesController extends Controller
{
	public function indexAction()
	{
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

	public function videoAction()
	{
		$content = $this->renderView(
			'HvzGameBundle:Game:video.html.twig',
			array(
				"navigation" => $this->get('hvz.navigation')->generate("video")
			)
		);

		return new Response($content);
	}
}
