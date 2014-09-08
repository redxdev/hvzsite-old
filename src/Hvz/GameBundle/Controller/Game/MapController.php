<?php

namespace Hvz\GameBundle\Controller\Game;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

class MapController extends Controller
{
	// restricted to mods+ only for now
	public function indexAction($mode)
	{
		$game = $this->getDoctrine()->getRepository('HvzGameBundle:Game')->findCurrentGame();
		if($game == null)
			$tagEnts = array();
		else
		{
			switch($mode)
			{
				default:
				case 'all':
					$tagEnts = $this->getDoctrine()->getManager()->getRepository('HvzGameBundle:InfectionSpread')->findByGameOrderedByTime($game);
					break;

				case 'yesterday':
					$tagEnts = $this->getDoctrine()->getManager()->getRepository('HvzGameBundle:InfectionSpread')->findPreviousDay($game);
					break;
			}
		}

		$tags = array();
		foreach($tagEnts as $tag)
		{
			if($tag->getLatitude() == 0 && $tag->getLongitude() == 0)
				continue;

			$tags[] = array(
				"victim" => $tag->getVictim()->getUser()->getFullname(),
				"zombie" => $tag->getZombie()->getUser()->getFullname(),
				"time" => $tag->getTime()->format("l h:i:s A"),
				"latitude" => $tag->getLatitude(),
				"longitude" => $tag->getLongitude()
			);
		}

		$content = $this->renderView(
			'HvzGameBundle:Game:map.html.twig',
			array(
				'tags' => $tags
			)
		);

		return new Response($content);
	}
}
