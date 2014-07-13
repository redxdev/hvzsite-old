<?php

namespace Hvz\GameBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

use Hvz\GameBundle\Entity\User;

class ComponentController extends Controller
{
	public function navbarAction($active = 'status')
	{
		$navigation = array();

		$navigation[] = array(
			'text' => 'Status',
			'href' => $this->generateUrl('hvz_index'),
			'active' => $active == 'status'
		);
		$navigation[] = array(
			'text' => 'Rules',
			'href' => $this->generateUrl('hvz_rules'),
			'active' => $active == 'rules'
		);
		$navigation[] = array(
			'text' => 'Register Tag',
			'href' => $this->generateUrl('hvz_register_tag'),
			'active' => $active == 'register_tag'
		);
		$navigation[] = array(
			'text' => 'Players',
			'href' => $this->generateUrl('hvz_players'),
			'active' => $active == 'players'
		);
		$navigation[] = array(
			'text' => 'Tags',
			'href' => $this->generateUrl('hvz_tags'),
			'active' => $active == 'tags'
		);
        /*$navigation[] = array(
            'text' => 'Graph',
            'href' => $this->generateUrl('hvz_graph'),
            'active' => $active == 'graph'
        );*/

		$securityContext = $this->get('security.context');
		
		if($securityContext->isGranted("ROLE_USER"))
		{
			$navigation[] = array(
				'text' => 'Missions',
				'href' => $this->generateUrl('hvz_missions'),
				'active' => $active == 'missions'
			);
		}

		if($securityContext->isGranted("ROLE_ADMIN"))
		{
			$navigation[] = array(
				'text' => 'Admin',
				'href' => $this->generateUrl('hvz_admin_overview'),
				'active' => $active == 'admin'
			);
		}

		$content = $this->renderView(
			'HvzGameBundle:Component:navbar.html.twig',
			array(
				'navigation' => $navigation
			)
		);

		return new Response($content);
	}

	public function postsAction()
	{
		$postEnts = $this->getDoctrine()->getRepository('HvzGameBundle:Post')->findAllOrderedByDate();
		$posts = array();
		foreach($postEnts as $post)
		{
			$posts[] = array(
				'title' => $post->getTitle(),
				'subtitle' => $post->getSubtitle(),
				'user' => $post->getUser()->getFullname(),
				'date' => $post->getPostdate()->format('Y D M j h:i:s A'),
				'body' => $post->getBody()
			);
		}

		$content = $this->renderView(
			'HvzGameBundle:Component:posts.html.twig',
			array(
				'posts' => $posts
			)
		);

		return new Response($content);
	}
}
