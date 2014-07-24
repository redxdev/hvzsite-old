<?php

namespace Hvz\GameBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

use Hvz\GameBundle\Entity\User;
use Hvz\GameBundle\Entity\InfectionSpread;

class GameController extends Controller
{
	public function statusAction()
	{
		$gameRepo = $this->getDoctrine()->getRepository('HvzGameBundle:Game');
		$game = $gameRepo->findCurrentGame();
		$time = null;
		if($game != null) {
			$now = date_create("now");
			$gameTime = ($now->getTimestamp() - $game->getStartDate()->getTimestamp() > 0) ? $game->getEndDate() : $game->getStartDate();
			$diff = $now->diff($gameTime);
			$time = array(
				'days' => $diff->d,
				'hours' => $diff->h,
				'minutes' => $diff->i,
				'seconds' => $diff->s,
				'timestamp' => $gameTime->getTimestamp()
			);
		}
		else {
			$game = $gameRepo->findNextGame();
			if($game != null) {
				$now = date_create("now");
				$gameTime = $game->getStartDate();
				$diff = $now->diff($gameTime);
				$time = array(
					'days' => $diff->d,
					'hours' => $diff->h,
					'minutes' => $diff->i,
					'seconds' => $diff->s,
					'timestamp' => $gameTime->getTimestamp()
				);
			}
		}

		$profileRepo = $this->getDoctrine()->getRepository('HvzGameBundle:Profile');

		$humans = count($profileRepo->findActiveHumans($game));
		$zombies = count($profileRepo->findActiveZombies($game));
		$total = $humans + $zombies;
		$human_percent = 0;
		$zombie_percent = 0;
		if($total != 0)
		{
			$human_percent = round($humans / $total * 100, 1);
			$zombie_percent = round($zombies / $total * 100, 1);
		}

		$content = $this->renderView(
    		'HvzGameBundle:Game:status.html.twig',
    		array(
				'navigation' => $this->get('hvz.navigation')->generate("status"),
    			'game' => array(
    				'humans' => $humans,
    				'zombies' => $zombies,
    				'time' => $time,
					'human_percent' => $human_percent,
					'zombie_percent' => $zombie_percent
    			)
    		)
    	);

    	return new Response($content);
	}

	public function rulesAction()
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

	public function registerTagAction(Request $request)
	{
		if($request->getMethod() == "GET")
		{
			$content = $this->renderView(
				'HvzGameBundle:Game:register_tag.html.twig',
				array(
					'navigation' => $this->get('hvz.navigation')->generate("register-tag")
				)
			);

			return new Response($content);
		}
		else if($request->getMethod() == "POST")
		{
			$csrf = $this->get('form.csrf_provider');

			$token = $request->get('_token');
			$victim = $request->get('victim');
			$zombie = $request->get('zombie');
			$latitude = $request->get('latitude') or null;
			$longitude = $request->get('longitude') or null;

			if(!$csrf->isCsrfTokenValid('hvz_register_tag', $token))
			{
				$content = $this->renderView(
					'HvzGameBundle:Game:register_tag.html.twig',
					array(
						'navigation' => $this->get('hvz.navigation')->generate("register-tag"),
						"errors" => array("Invalid CSRF token: Try resubmitting the form."),
						"victim" => $victim,
						"zombie" => $zombie
					)
				);

				return new Response($content);
			}

			$game = $this->getDoctrine()->getRepository('HvzGameBundle:Game')->findCurrentGame();
			if($game == null)
			{
				$content = $this->renderView(
					'HvzGameBundle:Game:register_tag.html.twig',
					array(
						'navigation' => $this->get('hvz.navigation')->generate("register-tag"),
						"errors" => array("There is no game currently running."),
						"victim" => $victim,
						"zombie" => $zombie
					)
				);

				return new Response($content);
			}

			$showError = false;
			$errors = array();

			$tagRepo = $this->getDoctrine()->getRepository('HvzGameBundle:PlayerTag');
			$profileRepo = $this->getDoctrine()->getRepository('HvzGameBundle:Profile');
			$victimTag = $tagRepo->findOneByGameAndTag($game, $victim);
			$zombieProfile = $profileRepo->findOneByGameAndTagId($game, $zombie);

			if(!$victimTag)
			{
				$av = $this->getDoctrine()->getRepository('HvzGameBundle:AntiVirusTag')->findOneByGameAndTag($game, $victim);

				if($av && $av->getActive())
				{
					if(!$zombieProfile)
					{
						$content = $this->renderView(
							'HvzGameBundle:Game:register_tag.html.twig',
							array(
								'navigation' => $this->get('hvz.navigation')->generate("register-tag"),
								"errors" => array("Correct AV code, but invalid zombie"),
								"victim" => $victim,
								"zombie" => $zombie
							)
						);

						return new Response($content);
					}

					if($zombieProfile->getTeam() != User::TEAM_ZOMBIE)
					{
						$content = $this->renderView(
							'HvzGameBundle:Game:register_tag.html.twig',
							array(
								'navigation' => $this->get('hvz.navigation')->generate("register-tag"),
								"errors" => array("You can't use an AV code on a human!"),
								"victim" => $victim,
								"zombie" => $zombie
							)
						);

						return new Response($content);
					}

					$zombieProfile->setTeam(User::TEAM_HUMAN);
					$av->setActive(false);

					$this->get('hvz.badge_registry')->addBadge($zombieProfile, 'used-av', false);

					$this->getDoctrine()->getManager()->flush();

					$this->get('session')->getFlashBag()->add(
						'page.toast',
						$zombieProfile->getUser()->getFullname() . " has taken an antivirus, and has become human once again!"
					);

					return $this->redirect($this->generateUrl('hvz_register_tag'));
				}
			}

			if(!$victimTag || $victimTag->getActive() == false || $victimTag->getProfile()->getActive() == false || $victimTag->getProfile()->getGame() != $game)
			{
				$showError = true;
				$errors[] = "Unknown victim tag";
			}

			if(!$zombieProfile || $zombieProfile->getActive() == false || $zombieProfile->getGame() != $game)
			{
				$showError = true;
				$errors[] = "Unknown zombie tag";
			}

			if(!$showError && $victimTag->getProfile()->getTeam() != User::TEAM_HUMAN)
			{
				$showError = true;
				$errors[] = "Victim is not a human";
			}

			if(!$showError && $zombieProfile->getTeam() != User::TEAM_ZOMBIE)
			{
				$showError = true;
				$errors[] = "Zombie is not a zombie";
			}

			if($showError)
			{
				$content = $this->renderView(
					'HvzGameBundle:Game:register_tag.html.twig',
					array(
						'navigation' => $this->get('hvz.navigation')->generate("register-tag"),
						"errors" => $errors,
						"victim" => $victim,
						"zombie" => $zombie
					)
				);

				return new Response($content);
			}
			else
			{
				$em = $this->getDoctrine()->getManager();

				$infection = new InfectionSpread();
				$infection->setZombie($zombieProfile);
				$infection->setVictim($victimTag->getProfile());
				$infection->setLatitude($latitude);
				$infection->setLongitude($longitude);
				$infection->setGame($game);
				$em->persist($infection);

				$victimTag->getProfile()->setTeam(User::TEAM_ZOMBIE);
				$victimTag->setActive(false);

				$zombieProfile->setNumberTagged($zombieProfile->getNumberTagged() + 1);

				$badgeReg = $this->get('hvz.badge_registry');
				$this->applyBadges($victimTag->getProfile(), $zombieProfile, $badgeReg);

				$em->flush();

				$this->get('session')->getFlashBag()->add(
					'page.toast',
					$victimTag->getProfile()->getUser()->getFullname() . " has joined the horde, courtesy of " . $zombieProfile->getUser()->getFullname()
				);

				return $this->redirect($this->generateUrl('hvz_register_tag'));
			}
		}
	}

	protected function applyBadges($victim, $zombie, $badgeReg)
	{
		$badgeReg->addBadge($victim, 'infected', false);

		$now = new \DateTime();

		$hour = intval($now->format('G'));
		$day = intval($now->format('w'));

		if($hour >= 6 && $hour < 8)
		{
			$badgeReg->addBadge($zombie, 'early-bird', false);
		}
		else if($hour >= 23)
		{
			$badgeReg->addBadge($victim, 'mission-aint-over', false);
		}

		if($day == 0)
		{
			$badgeReg->addBadge($victim, 'bad-start', false);
		}
		else if($day >= 4)
		{
			$badgeReg->addBadge($victim, 'so-close', false);
		}
	}

	public function playersAction(Request $request)
	{
		$game = $this->getDoctrine()->getRepository('HvzGameBundle:Game')->findCurrentGame();
		$sortBy = $request->get('sort');
		if($game == null)
			$playerEnts = array();
		else if($sortBy == null)
			$playerEnts = $this->getDoctrine()->getRepository('HvzGameBundle:Profile')->findActiveOrderedByNumberTaggedAndTeam($game);
		else
			$playerEnts = $this->getDoctrine()->getRepository('HvzGameBundle:Profile')->findActiveOrderedByCustom($game, $sortBy);

		$badgeReg = $this->get('hvz.badge_registry');

		$players = array();
		foreach($playerEnts as $player)
		{
			$badges = $badgeReg->getBadges($player);

			$players[] = array(
				'fullname' => $player->getUser()->getFullname(),
				'team' => ($player->getTeam() == User::TEAM_HUMAN ? 'human' : 'zombie'),
				'tags' => $player->getNumberTagged(),
				'clan' => $player->getClan(),
				'badges' => $badges,
				'avatar' => $player->getWebAvatarPath()
			);
		}

		$content = $this->renderView(
			'HvzGameBundle:Game:players.html.twig',
			array(
				'navigation' => $this->get('hvz.navigation')->generate("players"),
				'players' => $players
			)
		);

		return new Response($content);
	}

	public function tagsAction()
	{
		$game = $this->getDoctrine()->getRepository('HvzGameBundle:Game')->findCurrentGame();
		if($game == null)
			$tagEnts = array();
		else
			$tagEnts = $this->getDoctrine()->getManager()->getRepository('HvzGameBundle:InfectionSpread')->findByGameOrderedByTime($game);
		$tags = array();
		foreach($tagEnts as $tag)
		{
			$tags[] = array(
				"victim" => $tag->getVictim()->getUser()->getFullname(),
				"zombie" => $tag->getZombie()->getUser()->getFullname(),
				"time" => $tag->getTime()->format("l h:i:s A")
			);
		}

		$content = $this->renderView(
			'HvzGameBundle:Game:tags.html.twig',
			array(
				'navigation' => $this->get('hvz.navigation')->generate("tags"),
				"tags" => $tags
			)
		);

		return new Response($content);
	}

    public function graphAction()
    {
        $game = $this->getDoctrine()->getRepository('HvzGameBundle:Game')->findCurrentGame();
        if($game == null)
            $tagEnts = array();
        else
            $tagEnts = $this->getDoctrine()->getManager()->getRepository('HvzGameBundle:InfectionSpread')->findByGameOrderedByTime($game);
        $hasParent = array();
        $graph = array();
        foreach($tagEnts as $tag)
        {
            if(!array_key_exists($tag->getZombie()->getId(), $hasParent))
            {
                $hasParent[$tag->getZombie()->getId()] = true;
                $graph[$tag->getZombie()->getId()] = array(
                    "name" => $tag->getZombie()->getUser()->getFullname(),
                    "parent" => "null"
                );
            }

            $graph[$tag->getVictim()->getId()] = array(
                "name" => $tag->getVictim()->getUser()->getFullname(),
                "parent" => $tag->getZombie()->getUser()->getFullname()
            );
        }

        $content = $this->renderView(
            'HvzGameBundle:Game:graph.html.twig',
            array(
                "graph" => $graph,
                "game_running" => $game != null
            )
        );

        return new Response($content);
    }

	public function missionsAction()
	{
		$securityContext = $this->get('security.context');

		if(!$securityContext->isGranted("ROLE_USER"))
		{
			return $this->redirect($this->generateUrl('hvz_error_403'));
		}


		$game = $this->getDoctrine()->getRepository('HvzGameBundle:Game')->findCurrentGame();
		if($game == null)
		{
			return $this->redirect($this->generateUrl('hvz_error_active'));
		}

		$profile = $this->getDoctrine()->getRepository('HvzGameBundle:Profile')->findByGameAndUser($game, $securityContext->getToken()->getUser());

		if(!$profile || !$profile->getActive())
		{
			return $this->redirect($this->generateUrl('hvz_error_active'));
		}

		$missionEnts = $this->getDoctrine()->getManager()->getRepository('HvzGameBundle:Mission')->findPostedByTeamOrderedByDate($game, $profile->getTeam());
		$missions = array();
		foreach($missionEnts as $mission)
		{
			$missions[] = array(
				"title" => $mission->getTitle(),
				"body" => $mission->getBody(),
				"team" => $mission->getTeam() == User::TEAM_HUMAN ? 'human' : 'zombie'
			);
		}

		$content = $this->renderView(
			'HvzGameBundle:Game:missions.html.twig',
			array(
				'navigation' => $this->get('hvz.navigation')->generate("missions"),
				"missions" => $missions
			)
		);

		return new Response($content);
	}
}
