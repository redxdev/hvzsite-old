<?php

namespace Hvz\GameBundle\Controller;

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

class AdminController extends Controller
{
	public function gamesAction()
	{
		$securityContext = $this->get('security.context');

		if(!$securityContext->isGranted("ROLE_ADMIN"))
		{
			return $this->redirect($this->generateUrl('hvz_error_403'));
		}

		$gameRepo = $this->getDoctrine()->getRepository('HvzGameBundle:Game');
		$profileRepo = $this->getDoctrine()->getRepository('HvzGameBundle:Profile');
		$gameEnts = $gameRepo->findAllOrderedByStartDate();
		$currentGame = $gameRepo->findCurrentGame();
		$games = array();
		foreach($gameEnts as $game)
		{
			$games[] = array(
				'id' => $game->getId(),
				'active' => $currentGame == null ? false : $game == $currentGame,
				'startDate' => $game,
				'endDate' => $game->getEndDate()->format('Y D M j h:i:s A'),
				'humans' => count($profileRepo->findActiveHumans($game)),
				'zombies' => count($profileRepo->findActiveZombies($game)),
				'profiles' => count($profileRepo->findByGame($game)),
				'missions' => count($game->getMissions())
			);
		}

		$content = $this->renderView(
    		'HvzGameBundle:Admin:games.html.twig',
    		array(
				'navigation' => $this->get('hvz.navigation')->generate(""),
    			'games' => $games
    		)
    	);

    	return new Response($content);
	}

	public function gameCreateAction(Request $request)
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
			$em = $this->getDoctrine()->getManager();
			$em->persist($game);
			$em->flush();

			$content = $this->renderView(
				'HvzGameBundle:Admin:message.html.twig',
				array(
					'navigation' => $this->get('hvz.navigation')->generate(""),
					'message' => array(
						'type' => 'success',
						'body' => 'Created new game successfully.'
					)
				)
			);

			return new Response($content);
		}

		$content = $this->renderView(
    		'HvzGameBundle:Admin:edit_game.html.twig',
    		array(
				'navigation' => $this->get('hvz.navigation')->generate(""),
    			'form' => $form->createView()
    		)
    	);

    	return new Response($content);
	}

	public function gameEditAction(Request $request, $id)
	{
		$securityContext = $this->get('security.context');

		if(!$securityContext->isGranted("ROLE_ADMIN"))
		{
			return $this->redirect($this->generateUrl('hvz_error_403'));
		}

		$game = $this->getDoctrine()->getRepository('HvzGameBundle:Game')->findOneById($id);

		if($game == null)
		{
			$content = $this->renderView(
				'HvzGameBundle:Admin:message.html.twig',
				array(
					'navigation' => $this->get('hvz.navigation')->generate(""),
					'message' => array(
						'type' => 'error',
						'body' => 'Unknown game id.'
					)
				)
			);

			return new Response($content);
		}

		$form = $this->createFormBuilder($game)
			->add('startDate', 'datetime')
			->add('endDate', 'datetime')
			->add('save', 'submit')
			->getForm();

		$form->handleRequest($request);

		if($form->isValid())
		{
			$em = $this->getDoctrine()->getManager();
			$em->flush();

			$content = $this->renderView(
				'HvzGameBundle:Admin:message.html.twig',
				array(
					'navigation' => $this->get('hvz.navigation')->generate(""),
					'message' => array(
						'type' => 'success',
						'body' => 'Edited game successfully.'
					)
				)
			);

			return new Response($content);
		}

		$content = $this->renderView(
    		'HvzGameBundle:Admin:edit_game.html.twig',
    		array(
				'navigation' => $this->get('hvz.navigation')->generate(""),
    			'form' => $form->createView()
    		)
    	);

    	return new Response($content);
	}

	public function gameDeleteAction($id)
	{
		$securityContext = $this->get('security.context');

		if(!$securityContext->isGranted("ROLE_ADMIN"))
		{
			return $this->redirect($this->generateUrl('hvz_error_403'));
		}

		$game = $this->getDoctrine()->getRepository('HvzGameBundle:Game')->findOneById($id);

		if($game == null)
		{
			$content = $this->renderView(
				'HvzGameBundle:Admin:message.html.twig',
				array(
					'navigation' => $this->get('hvz.navigation')->generate(""),
					'message' => array(
						'type' => 'error',
						'body' => 'Unknown game id.'
					)
				)
			);

			return new Response($content);
		}

		$em = $this->getDoctrine()->getManager();
		$em->remove($game);
		$em->flush();

		$content = $this->renderView(
			'HvzGameBundle:Admin:message.html.twig',
			array(
				'message' => array(
					'navigation' => $this->get('hvz.navigation')->generate(""),
					'type' => 'success',
					'body' => 'Deleted game from the database.'
				)
			)
		);

		return new Response($content);
	}

	public function usersAction()
	{
		$securityContext = $this->get('security.context');

		if(!$securityContext->isGranted("ROLE_ADMIN"))
		{
			return $this->redirect($this->generateUrl('hvz_error_403'));
		}

		$userRepo = $this->getDoctrine()->getRepository('HvzGameBundle:User');
		$userEnts = $userRepo->findAll();
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

	public function userEditAction(Request $request, $id)
	{
		$securityContext = $this->get('security.context');

		if(!$securityContext->isGranted("ROLE_ADMIN"))
		{
			return $this->redirect($this->generateUrl('hvz_error_403'));
		}

		$user = $this->getDoctrine()->getRepository('HvzGameBundle:User')->findOneById($id);

		if($user == null)
		{
			$content = $this->renderView(
				'HvzGameBundle:Admin:message.html.twig',
				array(
					'navigation' => $this->get('hvz.navigation')->generate(""),
					'message' => array(
						'type' => 'error',
						'body' => 'Unknown user id.'
					)
				)
			);

			return new Response($content);
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
			$em = $this->getDoctrine()->getManager();
			$em->flush();

			$content = $this->renderView(
				'HvzGameBundle:Admin:message.html.twig',
				array(
					'navigation' => $this->get('hvz.navigation')->generate(""),
					'message' => array(
						'type' => 'success',
						'body' => 'Edited user successfully.'
					)
				)
			);

			return new Response($content);
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

	public function userDeleteAction($id)
	{
		$securityContext = $this->get('security.context');

		if(!$securityContext->isGranted("ROLE_ADMIN"))
		{
			return $this->redirect($this->generateUrl('hvz_error_403'));
		}

		$user = $this->getDoctrine()->getRepository('HvzGameBundle:User')->findOneById($id);

		if($user == null)
		{
			$content = $this->renderView(
				'HvzGameBundle:Admin:message.html.twig',
				array(
					'navigation' => $this->get('hvz.navigation')->generate(""),
					'message' => array(
						'type' => 'error',
						'body' => 'Unknown user id.'
					)
				)
			);

			return new Response($content);
		}

		$em = $this->getDoctrine()->getManager();
		$em->remove($user);
		$em->flush();

		$content = $this->renderView(
			'HvzGameBundle:Admin:message.html.twig',
			array(
				'navigation' => $this->get('hvz.navigation')->generate(""),
				'message' => array(
					'type' => 'success',
					'body' => 'Deleted user from the database.'
				)
			)
		);

		return new Response($content);
	}

	public function profileGenerateAction($id)
	{
		$securityContext = $this->get('security.context');

		if(!$securityContext->isGranted("ROLE_ADMIN"))
		{
			return $this->redirect($this->generateUrl('hvz_error_403'));
		}

		$user = $this->getDoctrine()->getRepository('HvzGameBundle:User')->findOneById($id);
		if($user == null)
		{
			$content = $this->renderView(
				'HvzGameBundle:Admin:message.html.twig',
				array(
					'navigation' => $this->get('hvz.navigation')->generate(""),
					'message' => array(
						'type' => 'error',
						'body' => 'Unknown user id.'
					)
				)
			);

			return new Response($content);
		}

		$gameRepo = $this->getDoctrine()->getRepository('HvzGameBundle:Game');
		$game = $gameRepo->findCurrentGame();
		if($game == null)
		{
			$game = $gameRepo->findNextGame();
		}

		if($game == null)
		{
			$content = $this->renderView(
				'HvzGameBundle:Admin:message.html.twig',
				array(
					'navigation' => $this->get('hvz.navigation')->generate(""),
					'message' => array(
						'type' => 'error',
						'body' => 'There is no current or upcoming game to create a profile for.'
					)
				)
			);

			return new Response($content);
		}

		$profile = $this->getDoctrine()->getRepository('HvzGameBundle:Profile')->findByGameAndUser($game, $user);
		if($profile != null)
		{
			$content = $this->renderView(
				'HvzGameBundle:Admin:message.html.twig',
				array(
					'navigation' => $this->get('hvz.navigation')->generate(""),
					'message' => array(
						'type' => 'error',
						'body' => 'A profile already exists.'
					)
				)
			);

			return new Response($content);
		}

		$em = $this->getDoctrine()->getManager();
		$tagGen = $this->get('hvz.tag_generator');

		$profile = new Profile($tagGen->generate());
		$profile->setUser($user);
		$profile->setGame($game);
		$user->addProfile($profile);
		$game->addProfile($profile);

		$tag1 = new PlayerTag($tagGen->generate());
		$tag1->setProfile($profile);
		$profile->addIdTag($tag1);

		$tag2 = new PlayerTag($tagGen->generate());
		$tag2->setProfile($profile);
		$profile->addIdTag($tag2);

		$em->persist($profile);
		$em->persist($tag1);
		$em->persist($tag2);
		$em->flush();

		return $this->redirect($this->generateUrl('hvz_admin_profile_view', array('id' => $profile->getId())));
	}

	public function profilesAction($user = null, $game = null, $active = null)
	{
		$securityContext = $this->get('security.context');

		if(!$securityContext->isGranted("ROLE_ADMIN"))
		{
			return $this->redirect($this->generateUrl('hvz_error_403'));
		}

		$profileRepo = $this->getDoctrine()->getRepository('HvzGameBundle:Profile');
		$profileEnts = array();
		if($user != null && $game != null)
		{
			$profileEnts = $profileRepo->findByGameAndUser($game, $user);
		}
		elseif($user != null)
		{
			$profileEnts = $profileRepo->findByUser($user);
		}
		elseif($game != null)
		{
			$profileEnts = $profileRepo->findByGame($game);
		}
		else
		{
			$profileEnts = $profileRepo->findAll();
		}

		$profiles = array();
		foreach($profileEnts as $profile)
		{
			$profiles[] = array(
				'id' => $profile->getId(),
				'user' => $profile->getUser()->getFullname(),
				'game' => $profile->getGame(),
				'active' => $profile->getActive() ? 'yes' : 'no',
				'team' => $profile->getTeam() == User::TEAM_HUMAN ? 'human' : 'zombie',
				'numberTagged' => $profile->getNumberTagged(),
				'clan' => $profile->getClan()
			);
		}

		$content = $this->renderView(
    		'HvzGameBundle:Admin:profiles.html.twig',
    		array(
				'navigation' => $this->get('hvz.navigation')->generate(""),
    			'profiles' => $profiles
    		)
    	);

    	return new Response($content);
	}

	public function profileViewAction($id)
	{
		$securityContext = $this->get('security.context');

		if(!$securityContext->isGranted("ROLE_ADMIN"))
		{
			return $this->redirect($this->generateUrl('hvz_error_403'));
		}

		$profile = $this->getDoctrine()->getRepository('HvzGameBundle:Profile')->findOneById($id);
		if($profile == null)
		{
			$content = $this->renderView(
				'HvzGameBundle:Admin:message.html.twig',
				array(
					'navigation' => $this->get('hvz.navigation')->generate(""),
					'message' => array(
						'type' => 'error',
						'body' => 'Unknown profile id.'
					)
				)
			);

			return new Response($content);
		}

		$idTags = array();
		foreach($profile->getIdTags() as $idTag)
		{
			$idTags[] = array(
				'data' => $idTag->getTag(),
				'active' => $idTag->getActive()
			);
		}

		$content = $this->renderView(
			'HvzGameBundle:Admin:view_profile.html.twig',
			array(
				'navigation' => $this->get('hvz.navigation')->generate(""),
				'profile' => array(
					'id' => $profile->getId(),
					'user' => $profile->getUser()->getFullname(),
					'game' => $profile->getGame(),
					'active' => $profile->getActive() ? 'yes' : 'no',
					'team' => $profile->getTeam() == User::TEAM_HUMAN ? 'human' : 'zombie',
					'player_id' => $profile->getTagId(),
					'id_tags' => $idTags,
					'clan' => $profile->getClan()
				)
			)
		);

		return new Response($content);
	}

	public function profileAddTagAction($id)
	{
		$securityContext = $this->get('security.context');

		if(!$securityContext->isGranted("ROLE_ADMIN"))
		{
			return $this->redirect($this->generateUrl('hvz_error_403'));
		}

		$profile = $this->getDoctrine()->getRepository('HvzGameBundle:Profile')->findOneById($id);
		if($profile == null)
		{
			$content = $this->renderView(
				'HvzGameBundle:Admin:message.html.twig',
				array(
					'navigation' => $this->get('hvz.navigation')->generate(""),
					'message' => array(
						'type' => 'error',
						'body' => 'Unknown profile id.'
					)
				)
			);

			return new Response($content);
		}

		$tagGen = $this->get('hvz.tag_generator');

		$newTag = new PlayerTag($tagGen->generate());
		$newTag->setProfile($profile);
		$profile->addIdTag($newTag);

		$em = $this->getDoctrine()->getManager();
		$em->persist($newTag);
		$em->flush();

		return $this->redirect($this->generateUrl('hvz_admin_profile_view', array('id' => $id)));
	}

	public function profileEditAction(Request $request, $id)
	{
		$securityContext = $this->get('security.context');

		if(!$securityContext->isGranted("ROLE_ADMIN"))
		{
			return $this->redirect($this->generateUrl('hvz_error_403'));
		}

		$profile = $this->getDoctrine()->getRepository('HvzGameBundle:Profile')->findOneById($id);

		if($profile == null)
		{
			$content = $this->renderView(
				'HvzGameBundle:Admin:message.html.twig',
				array(
					'navigation' => $this->get('hvz.navigation')->generate(""),
					'message' => array(
						'type' => 'error',
						'body' => 'Unknown profile id.'
					)
				)
			);

			return new Response($content);
		}

		$form = $this->createFormBuilder($profile)
			->add('active', 'checkbox', array('required' => false))
			->add('team', 'choice', array(
				'choices' => array(
					User::TEAM_HUMAN => 'Human',
					User::TEAM_ZOMBIE => 'Zombie')
			))
			->add('clan', 'text', array('required' => false))
			->add('save', 'submit')
			->getForm();

		$form->handleRequest($request);

		if($form->isValid())
		{
			$em = $this->getDoctrine()->getManager();
			$em->flush();

			$content = $this->renderView(
				'HvzGameBundle:Admin:message.html.twig',
				array(
					'navigation' => $this->get('hvz.navigation')->generate(""),
					'message' => array(
						'type' => 'success',
						'body' => 'Edited profile successfully.'
					)
				)
			);

			return new Response($content);
		}

		$content = $this->renderView(
    		'HvzGameBundle:Admin:edit_profile.html.twig',
    		array(
				'navigation' => $this->get('hvz.navigation')->generate(""),
    			'form' => $form->createView(),
				'profile' => array(
					'user' => $profile->getUser()->getFullname()
				)
    		)
    	);

    	return new Response($content);
	}

	public function profileDeleteAction($id)
	{
		$securityContext = $this->get('security.context');

		if(!$securityContext->isGranted("ROLE_ADMIN"))
		{
			return $this->redirect($this->generateUrl('hvz_error_403'));
		}

		$profile = $this->getDoctrine()->getRepository('HvzGameBundle:Profile')->findOneById($id);

		if($profile == null)
		{
			$content = $this->renderView(
				'HvzGameBundle:Admin:message.html.twig',
				array(
					'navigation' => $this->get('hvz.navigation')->generate(""),
					'message' => array(
						'type' => 'error',
						'body' => 'Unknown profile id.'
					)
				)
			);

			return new Response($content);
		}

		$em = $this->getDoctrine()->getManager();
		$em->remove($profile);
		$em->flush();

		$content = $this->renderView(
			'HvzGameBundle:Admin:message.html.twig',
			array(
				'navigation' => $this->get('hvz.navigation')->generate(""),
				'message' => array(
					'type' => 'success',
					'body' => 'Deleted profile from the database.'
				)
			)
		);

		return new Response($content);
	}

	public function missionsAction($game = null)
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

	public function missionEditAction(Request $request, $id)
	{
		$securityContext = $this->get('security.context');

		if(!$securityContext->isGranted("ROLE_ADMIN"))
		{
			return $this->redirect($this->generateUrl('hvz_error_403'));
		}

		$mission = $this->getDoctrine()->getRepository('HvzGameBundle:Mission')->findOneById($id);

		if($mission == null)
		{
			$content = $this->renderView(
				'HvzGameBundle:Admin:message.html.twig',
				array(
					'navigation' => $this->get('hvz.navigation')->generate(""),
					'message' => array(
						'type' => 'error',
						'body' => 'Unknown mission id.'
					)
				)
			);

			return new Response($content);
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
			$em->flush();

			$content = $this->renderView(
				'HvzGameBundle:Admin:message.html.twig',
				array(
					'navigation' => $this->get('hvz.navigation')->generate(""),
					'message' => array(
						'type' => 'success',
						'body' => 'Edited mission successfully.'
					)
				)
			);

			return new Response($content);
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

	public function missionCreateAction(Request $request)
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
			$em->flush();

			$content = $this->renderView(
				'HvzGameBundle:Admin:message.html.twig',
				array(
					'navigation' => $this->get('hvz.navigation')->generate(""),
					'message' => array(
						'type' => 'success',
						'body' => 'Created mission successfully.'
					)
				)
			);

			return new Response($content);
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

	public function missionDeleteAction($id)
	{
		$securityContext = $this->get('security.context');

		if(!$securityContext->isGranted("ROLE_ADMIN"))
		{
			return $this->redirect($this->generateUrl('hvz_error_403'));
		}

		$mission = $this->getDoctrine()->getRepository('HvzGameBundle:Mission')->findOneById($id);

		if($mission == null)
		{
			$content = $this->renderView(
				'HvzGameBundle:Admin:message.html.twig',
				array(
					'navigation' => $this->get('hvz.navigation')->generate(""),
					'message' => array(
						'type' => 'error',
						'body' => 'Unknown mission id.'
					)
				)
			);

			return new Response($content);
		}

		$em = $this->getDoctrine()->getManager();
		$em->remove($mission);
		$em->flush();

		$content = $this->renderView(
			'HvzGameBundle:Admin:message.html.twig',
			array(
				'navigation' => $this->get('hvz.navigation')->generate(""),
				'message' => array(
					'type' => 'success',
					'body' => 'Deleted mission from the database.'
				)
			)
		);

		return new Response($content);
	}

	public function postsAction($game = null)
	{
		$securityContext = $this->get('security.context');

		if(!$securityContext->isGranted("ROLE_ADMIN"))
		{
			return $this->redirect($this->generateUrl('hvz_error_403'));
		}

		$postRepo = $this->getDoctrine()->getRepository('HvzGameBundle:Post');
		$postEnts = $postRepo->findAllOrderedByDate();
		$posts = array();
		foreach($postEnts as $post)
		{
			$posts[] = array(
				'id' => $post->getId(),
				'title' => $post->getTitle(),
				'subtitle' => $post->getSubtitle(),
				'user' => $post->getUser()->getFullname(),
				'postdate' => $post->getPostdate()->format('Y D M j h:i:s A'),
				'body' => $post->getBody()
			);
		}

		$content = $this->renderView(
    		'HvzGameBundle:Admin:news_posts.html.twig',
    		array(
				'navigation' => $this->get('hvz.navigation')->generate(""),
    			'posts' => $posts
    		)
    	);

    	return new Response($content);
	}

	public function postEditAction(Request $request, $id)
	{
		$securityContext = $this->get('security.context');

		if(!$securityContext->isGranted("ROLE_ADMIN"))
		{
			return $this->redirect($this->generateUrl('hvz_error_403'));
		}

		$post = $this->getDoctrine()->getRepository('HvzGameBundle:Post')->findOneById($id);

		if($post == null)
		{
			$content = $this->renderView(
				'HvzGameBundle:Admin:message.html.twig',
				array(
					'navigation' => $this->get('hvz.navigation')->generate(""),
					'message' => array(
						'type' => 'error',
						'body' => 'Unknown post id.'
					)
				)
			);

			return new Response($content);
		}

		$form = $this->createFormBuilder($post)
			->add('title', 'text')
			->add('subtitle', 'text', array('required' => false))
			->add('user', 'entity', array(
				'class' => 'HvzGameBundle:User',
				'property' => 'fullname'
			))
			->add('body', 'textarea')
			->add('save', 'submit')
			->getForm();

		$form->handleRequest($request);

		if($form->isValid())
		{
			$em = $this->getDoctrine()->getManager();
			$em->flush();

			$content = $this->renderView(
				'HvzGameBundle:Admin:message.html.twig',
				array(
					'navigation' => $this->get('hvz.navigation')->generate(""),
					'message' => array(
						'type' => 'success',
						'body' => 'Edited post successfully.'
					)
				)
			);

			return new Response($content);
		}

		$content = $this->renderView(
    		'HvzGameBundle:Admin:edit_post.html.twig',
    		array(
				'navigation' => $this->get('hvz.navigation')->generate(""),
    			'form' => $form->createView()
    		)
    	);

    	return new Response($content);
	}

	public function postCreateAction(Request $request)
	{
		$securityContext = $this->get('security.context');

		if(!$securityContext->isGranted("ROLE_ADMIN"))
		{
			return $this->redirect($this->generateUrl('hvz_error_403'));
		}

		$post = new Post();
		$post->setUser($securityContext->getToken()->getUser());

		$form = $this->createFormBuilder($post)
			->add('title', 'text')
			->add('subtitle', 'text', array('required' => false))
			->add('user', 'entity', array(
				'class' => 'HvzGameBundle:User',
				'property' => 'fullname'
			))
			->add('body', 'textarea')
			->add('save', 'submit')
			->getForm();

		$form->handleRequest($request);

		if($form->isValid())
		{
			$em = $this->getDoctrine()->getManager();
			$em->persist($post);
			$em->flush();

			$content = $this->renderView(
				'HvzGameBundle:Admin:message.html.twig',
				array(
					'navigation' => $this->get('hvz.navigation')->generate(""),
					'message' => array(
						'type' => 'success',
						'body' => 'Created post successfully.'
					)
				)
			);

			return new Response($content);
		}

		$content = $this->renderView(
    		'HvzGameBundle:Admin:edit_post.html.twig',
    		array(
				'navigation' => $this->get('hvz.navigation')->generate(""),
    			'form' => $form->createView()
    		)
    	);

    	return new Response($content);
	}

	public function postDeleteAction($id)
	{
		$securityContext = $this->get('security.context');

		if(!$securityContext->isGranted("ROLE_ADMIN"))
		{
			return $this->redirect($this->generateUrl('hvz_error_403'));
		}

		$post = $this->getDoctrine()->getRepository('HvzGameBundle:Post')->findOneById($id);

		if($post == null)
		{
			$content = $this->renderView(
				'HvzGameBundle:Admin:message.html.twig',
				array(
					'navigation' => $this->get('hvz.navigation')->generate(""),
					'message' => array(
						'type' => 'error',
						'body' => 'Unknown post id.'
					)
				)
			);

			return new Response($content);
		}

		$em = $this->getDoctrine()->getManager();
		$em->remove($post);
		$em->flush();

		$content = $this->renderView(
			'HvzGameBundle:Admin:message.html.twig',
			array(
				'navigation' => $this->get('hvz.navigation')->generate(""),
				'message' => array(
					'type' => 'success',
					'body' => 'Deleted post from the database.'
				)
			)
		);

		return new Response($content);
	}

    public function rulesAction()
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

    public function ruleEditAction(Request $request, $id)
    {
        $securityContext = $this->get('security.context');

        if(!$securityContext->isGranted("ROLE_ADMIN"))
        {
            return $this->redirect($this->generateUrl('hvz_error_403'));
        }

        $rule = $this->getDoctrine()->getRepository('HvzGameBundle:Rule')->findOneById($id);

        if($rule == null)
        {
            $content = $this->renderView(
                'HvzGameBundle:Admin:message.html.twig',
                array(
					'navigation' => $this->get('hvz.navigation')->generate(""),
                    'message' => array(
                        'type' => 'error',
                        'body' => 'Unknown rule id.'
                    )
                )
            );

            return new Response($content);
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
            $em->flush();

            $content = $this->renderView(
                'HvzGameBundle:Admin:message.html.twig',
                array(
					'navigation' => $this->get('hvz.navigation')->generate(""),
                    'message' => array(
                        'type' => 'success',
                        'body' => 'Edited rule successfully.'
                    )
                )
            );

            return new Response($content);
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

    public function ruleCreateAction(Request $request)
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
            $em->flush();

            $content = $this->renderView(
                'HvzGameBundle:Admin:message.html.twig',
                array(
					'navigation' => $this->get('hvz.navigation')->generate(""),
                    'message' => array(
                        'type' => 'success',
                        'body' => 'Created rule successfully.'
                    )
                )
            );

            return new Response($content);
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

    public function ruleDeleteAction($id)
    {
        $securityContext = $this->get('security.context');

        if(!$securityContext->isGranted("ROLE_ADMIN"))
        {
            return $this->redirect($this->generateUrl('hvz_error_403'));
        }

        $rule = $this->getDoctrine()->getRepository('HvzGameBundle:Rule')->findOneById($id);

        if($rule == null)
        {
            $content = $this->renderView(
                'HvzGameBundle:Admin:message.html.twig',
                array(
					'navigation' => $this->get('hvz.navigation')->generate(""),
                    'message' => array(
                        'type' => 'error',
                        'body' => 'Unknown rule id.'
                    )
                )
            );

            return new Response($content);
        }

        $em = $this->getDoctrine()->getManager();
        $em->remove($rule);
        $em->flush();

        $content = $this->renderView(
            'HvzGameBundle:Admin:message.html.twig',
            array(
				'navigation' => $this->get('hvz.navigation')->generate(""),
                'message' => array(
                    'type' => 'success',
                    'body' => 'Deleted rule from the database.'
                )
            )
        );

        return new Response($content);
    }
}
