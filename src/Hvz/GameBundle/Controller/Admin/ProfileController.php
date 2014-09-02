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

class ProfileController extends Controller
{
	public function indexAction($user = null, $game = null, $active = null)
	{
		$securityContext = $this->get('security.context');

		if(!$securityContext->isGranted("ROLE_MOD"))
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

	public function generateAction($id, $token)
	{
		$securityContext = $this->get('security.context');

		if(!$securityContext->isGranted("ROLE_MOD"))
		{
			return $this->redirect($this->generateUrl('hvz_error_403'));
		}

		$csrf = $this->get('form.csrf_provider');
		if(!$csrf->isCsrfTokenValid('hvz_profile_generate', $token))
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

		$gameRepo = $this->getDoctrine()->getRepository('HvzGameBundle:Game');
		$game = $gameRepo->findCurrentGame();
		if($game == null)
		{
			$game = $gameRepo->findNextGame();
		}

		if($game == null)
		{
			$this->get('session')->getFlashBag()->add(
				'page.toast',
				"There is no upcoming or current game to generate a profile for."
			);

			return $this->redirect($this->generateUrl('hvz_admin_users'));
		}

		$profile = $this->getDoctrine()->getRepository('HvzGameBundle:Profile')->findByGameAndUser($game, $user);
		if($profile != null)
		{
			$this->get('session')->getFlashBag()->add(
				'page.toast',
				"A profile for this user already exists."
			);

			return $this->redirect($this->generateUrl('hvz_admin_users'));
		}

		$actlog = $this->get('hvz.action_log');
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

		$actlog->recordAction(
			ActionLogService::TYPE_ADMIN,
			'email:' . $this->get('security.context')->getToken()->getUser()->getEmail(),
			'generated profile: ' . $user->getEmail() . ':' . $game->getId(),
			false
		);

		$em->flush();

		return $this->redirect($this->generateUrl('hvz_admin_profile_view', array('id' => $profile->getId())));
	}

	public function printAction(Request $request, $game)
	{
		$securityContext = $this->get('security.context');

		$preview = $request->query->get('preview', 0) != 1;
		$today = $request->query->get('today', 0) == 1;

		if(!$securityContext->isGranted("ROLE_ADMIN"))
		{
			return $this->redirect($this->generateUrl('hvz_error_403'));
		}

		$profileEnts = array();
		if($today)
		{
			$profileEnts = $this->getDoctrine()->getRepository('HvzGameBundle:Profile')->findActiveCreatedToday($game);
		}
		else
		{
			$profileEnts = $this->getDoctrine()->getRepository('HvzGameBundle:Profile')->findActiveOrderedByNumberTaggedAndTeam($game);
		}

		$profiles = array();
		foreach($profileEnts as $profile)
		{
			$tags = array();
			foreach($profile->getIdTags() as $tag)
			{
				$tags[] = $preview == true ? $tag->getTag() : '';
				if(count($tags) >= 2)
					break;
			}

			$profiles[] = array(
				'tagid' => $preview == true ? $profile->getTagId() : '',
				'user' => $profile->getUser()->getFullname(),
				'avatar' => $profile->getWebAvatarPath(),
				'tags' => $tags
			);
		}

		$content = $this->renderView(
			'HvzGameBundle:Admin:print.html.twig',
			array(
				'profiles' => $profiles
			)
		);

		return new Response($content);
	}

	public function viewAction($id)
	{
		$securityContext = $this->get('security.context');

		if(!$securityContext->isGranted("ROLE_MOD"))
		{
			return $this->redirect($this->generateUrl('hvz_error_403'));
		}

		$profile = $this->getDoctrine()->getRepository('HvzGameBundle:Profile')->findOneById($id);
		if($profile == null)
		{
			$this->get('session')->getFlashBag()->add(
				'page.toast',
				"Unknown profile id."
			);

			return $this->redirect($this->generateUrl('hvz_admin_profiles'));
		}

		$idTags = array();
		foreach($profile->getIdTags() as $idTag)
		{
			$idTags[] = array(
				'data' => $idTag->getTag(),
				'active' => $idTag->getActive()
			);
		}

		$badges = $this->get('hvz.badge_registry')->getBadges($profile);

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
					'clan' => $profile->getClan(),
					'badges' => $badges,
					'avatar' => $profile->getWebAvatarPath()
				)
			)
		);

		return new Response($content);
	}

	public function addTagAction($id)
	{
		$securityContext = $this->get('security.context');

		if(!$securityContext->isGranted("ROLE_ADMIN"))
		{
			return $this->redirect($this->generateUrl('hvz_error_403'));
		}

		$profile = $this->getDoctrine()->getRepository('HvzGameBundle:Profile')->findOneById($id);
		if($profile == null)
		{
			$this->get('session')->getFlashBag()->add(
				'page.toast',
				"Unknown profile id."
			);

			return $this->redirect($this->generateUrl('hvz_admin_profiles'));
		}

		$tagGen = $this->get('hvz.tag_generator');

		$newTag = new PlayerTag($tagGen->generate());
		$newTag->setProfile($profile);
		$profile->addIdTag($newTag);

		$actlog = $this->get('hvz.action_log');
		$em = $this->getDoctrine()->getManager();
		$em->persist($newTag);

		$actlog->recordAction(
			ActionLogService::TYPE_ADMIN,
			'email:' . $this->get('security.context')->getToken()->getUser()->getEmail(),
			'generated tag for profile ' . $profile->getUser()->getEmail() . ':' . $profile->getGame()->getId() . ': '
				. $newTag->getTag(),
			false
		);

		$em->flush();

		$this->get('session')->getFlashBag()->add(
			'page.toast',
			"Added new tag successfully."
		);

		return $this->redirect($this->generateUrl('hvz_admin_profile_view', array('id' => $id)));
	}

	public function giveBadgeAction($id, $badge = null)
	{
		$securityContext = $this->get('security.context');

		if(!$securityContext->isGranted("ROLE_ADMIN"))
		{
			return $this->redirect($this->generateUrl('hvz_error_403'));
		}

		$profile = $this->getDoctrine()->getRepository('HvzGameBundle:Profile')->findOneById($id);

		if($profile == null)
		{
			$this->get('session')->getFlashBag()->add(
				'page.toast',
				"Unknown profile id."
			);

			return $this->redirect($this->generateUrl('hvz_admin_profiles'));
		}

		$badgeReg = $this->get('hvz.badge_registry');

		if($badge == null)
		{
			$badges = array();
			$registry = $badgeReg->getRegistry();
			foreach($registry as $k => $badge)
			{
				$badges[] = array(
					'id' => $k,
					'name' => $badge['name'],
					'image' => $badge['image'],
					'description' => $badge['description']
				);
			}

			$content = $this->renderView(
				'HvzGameBundle:Admin:give_badge.html.twig',
				array(
					'navigation' => $this->get('hvz.navigation')->generate(""),
					'profile' => array(
						'id' => $id,
						'user' => $profile->getUser()->getFullname()
					),
					'badges' => $badges
				)
			);

			return new Response($content);
		}
		else
		{
			if(!$badgeReg->badgeExists($badge))
			{
				$this->get('session')->getFlashBag()->add(
					'page.toast',
					"Unknown badge id."
				);

				return $this->redirect($this->generateUrl('hvz_admin_profile_give_badge', array('id' => $id)));
			}

			$badgeReg->addBadge($profile, $badge, true);

			$actlog = $this->get('hvz.action_log');
			$actlog->recordAction(
				ActionLogService::TYPE_ADMIN,
				'email:' . $this->get('security.context')->getToken()->getUser()->getEmail(),
				'added badge to profile ' . $profile->getUser()->getEmail() . ':' . $profile->getGame()->getId()
					. ': ' . $badge,
				true
			);

			$this->get('session')->getFlashBag()->add(
				'page.toast',
				"Gave badge successfully."
			);

			return $this->redirect($this->generateUrl('hvz_admin_profile_view', array('id' => $id)));
		}
	}

	public function editAction(Request $request, $id)
	{
		$securityContext = $this->get('security.context');

		if(!$securityContext->isGranted("ROLE_MOD"))
		{
			return $this->redirect($this->generateUrl('hvz_error_403'));
		}

		$profile = $this->getDoctrine()->getRepository('HvzGameBundle:Profile')->findOneById($id);

		if($profile == null)
		{
			$this->get('session')->getFlashBag()->add(
				'page.toast',
				"Unknown profile id."
			);

			return $this->redirect($this->generateUrl('hvz_admin_users'));
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

			$actlog = $this->get('hvz.action_log');
			$actlog->recordAction(
				ActionLogService::TYPE_ADMIN,
				'email:' . $this->get('security.context')->getToken()->getUser()->getEmail(),
				'edited profile: ' . $profile->getUser()->getEmail() . ':' . $profile->getGame()->getId(),
				false
			);

			$em->flush();

			$this->get('session')->getFlashBag()->add(
				'page.toast',
				"Edited profile successfully."
			);

			return $this->redirect($this->generateUrl('hvz_admin_profiles'));
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

	public function changeAvatarAction(Request $request, $id)
	{
		$securityContext = $this->get('security.context');

		if(!$securityContext->isGranted("ROLE_MOD"))
		{
			return $this->redirect($this->generateUrl('hvz_error_403'));
		}

		$profile = $this->getDoctrine()->getRepository('HvzGameBundle:Profile')->findOneById($id);

		if($profile == null)
		{
			$this->get('session')->getFlashBag()->add(
				'page.toast',
				"Unknown profile id."
			);

			return $this->redirect($this->generateUrl('hvz_admin_profiles'));
		}

		$content = $this->renderView(
			'HvzGameBundle:Admin:edit_avatar.html.twig',
			array(
				'navigation' => $this->get('hvz.navigation')->generate(""),
				'profile' => array(
					'user' => $profile->getUser()->getFullname(),
					'id' => $id
				)
			)
		);

		return new Response($content);
	}

	public function submitAvatarAction(Request $request, $id)
	{
		$securityContext = $this->get('security.context');

		if(!$securityContext->isGranted("ROLE_MOD"))
		{
			return $this->redirect($this->generateUrl('hvz_error_403'));
		}

		$profile = $this->getDoctrine()->getRepository('HvzGameBundle:Profile')->findOneById($id);

		if($profile == null)
		{
			$this->get('session')->getFlashBag()->add(
				'page.toast',
				"Unknown profile id."
			);

			return $this->redirect($this->generateUrl('hvz_admin_profiles'));
		}

		$oldAvatarPath = $profile->getAbsoluteAvatarPath();

		$form = $this->createFormBuilder($profile, array('csrf_protection' => false))
					->add('avatarFile', 'file')
					->getForm();

		$form->handleRequest($request);

		if($form->isValid())
		{
			$em = $this->getDoctrine()->getManager();

			$profile->uploadAvatar();

			$actlog = $this->get('hvz.action_log');
			$actlog->recordAction(
				ActionLogService::TYPE_ADMIN,
				'email:' . $this->get('security.context')->getToken()->getUser()->getEmail(),
				'changed avatar: ' . $profile->getUser()->getEmail() . ':' . $profile->getGame()->getId(),
				false
			);

			$em->flush();

			if($oldAvatarPath != null)
			{
				@unlink($oldAvatarPath);
			}

			$this->get('session')->getFlashBag()->add(
				'page.toast',
				"Changed avatar successfully."
			);

			return new Response($this->generateUrl('hvz_admin_profile_view', array('id' => $id)));
		}

		$this->get('session')->getFlashBag()->add(
			'page.toast',
			"Error changing avatar."
		);

		return new Response($this->generateUrl('hvz_admin_profile_avatar_change', array('id' => $id)));
	}

	public function deleteAction($id, $token)
	{
		$securityContext = $this->get('security.context');

		if(!$securityContext->isGranted("ROLE_ADMIN"))
		{
			return $this->redirect($this->generateUrl('hvz_error_403'));
		}

		$csrf = $this->get('form.csrf_provider');
		if(!$csrf->isCsrfTokenValid('hvz_profile_delete', $token))
		{
			$this->get('session')->getFlashBag()->add(
				'page.toast',
				"Invalid CSRF token. Please try again."
			);

			return $this->redirect($this->generateUrl('hvz_admin_profiles'));
		}

		$profile = $this->getDoctrine()->getRepository('HvzGameBundle:Profile')->findOneById($id);

		if($profile == null)
		{
			$this->get('session')->getFlashBag()->add(
				'page.toast',
				"Unknown profile id."
			);

			return $this->redirect($this->generateUrl('hvz_admin_profiles'));
		}

		$em = $this->getDoctrine()->getManager();
		$em->remove($profile);

		$actlog = $this->get('hvz.action_log');
		$actlog->recordAction(
			ActionLogService::TYPE_ADMIN,
			'email:' . $this->get('security.context')->getToken()->getUser()->getEmail(),
			'deleted profile: ' . $profile->getUser()->getEmail() . ':' . $profile->getGame()->getId(),
			false
		);

		$em->flush();

		if($profile->getAbsoluteAvatarPath() != null)
		{
			@unlink($profile->getAbsoluteAvatarPath());
		}

		$this->get('session')->getFlashBag()->add(
			'page.toast',
			"Deleted profile successfully."
		);

		return $this->redirect($this->generateUrl('hvz_admin_profiles'));
	}
}
