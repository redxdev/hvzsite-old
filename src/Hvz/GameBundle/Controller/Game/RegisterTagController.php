<?php

namespace Hvz\GameBundle\Controller\Game;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

use Hvz\GameBundle\Entity\User;
use Hvz\GameBundle\Entity\InfectionSpread;

use Hvz\GameBundle\Services\ActionLogService;

class RegisterTagController extends Controller
{
	public function indexAction(Request $request)
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
			$actlog = $this->get('hvz.action_log');
			$session = $this->get('session');
			$csrf = $this->get('form.csrf_provider');

			$token = $request->get('_token');
			$victim = $request->get('victim');
			$zombie = $request->get('zombie');
			$latitude = $request->get('latitude') or null;
			$longitude = $request->get('longitude') or null;

			$failedTagCount = $session->get('hvz_tag_failures', 0);
			$lastFailDate = $session->get('hvz_tag_failure_date', new \DateTime());
			$timeDiff = (new \DateTime())->diff($lastFailDate);

			if($timeDiff->h > 2) {
				$failedTagCount = 0;
			}

			if($failedTagCount >= 5)
			{
				$content = $this->renderView(
					'HvzGameBundle:Game:register_tag.html.twig',
					array(
						'navigation' => $this->get('hvz.navigation')->generate("register-tag"),
						"errors" => array("You have submitted invalid tags too many times. You must wait ~" . (120 - $timeDiff->i) . " minutes before trying again."),
						"victim" => $victim,
						"zombie" => $zombie
					)
				);

				return new Response($content);
			}



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
						"errors" => array("The game hasn't started yet!"),
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

					$now = new \DateTime();
					$hour = intval($now->format('G'));
					if($hour >= 17)
					{
						$content = $this->renderView(
							'HvzGameBundle:Game:register_tag.html.twig',
							array(
								'navigation' => $this->get('hvz.navigation')->generate("register-tag"),
								"errors" => array("AVs must be used before 5PM"),
								"victim" => $victim,
								"zombie" => $zombie
							)
						);

						return new Response($content);
					}

					$zombieProfile->setTeam(User::TEAM_HUMAN);
					$av->setActive(false);

					$this->get('hvz.badge_registry')->addBadge($zombieProfile, 'used-av', false);

					$actlog->recordAction(
						ActionLogService::TYPE_GAME,
						'email:' . $zombieProfile->getUser()->getEmail(),
						'antivirus used: ' . $av->getTag(), false);

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
				$failedTagCount++;
				$session->set('hvz_tag_failures', $failedTagCount);
				$session->set('hvz_tag_failure_date', new \DateTime());

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
				$this->applyBadges($victimTag->getProfile(), $zombieProfile, $badgeReg, $infection);

				$actlog->recordAction(
					ActionLogService::TYPE_GAME,
					'email:' . $zombieProfile->getUser()->getEmail(),
					'zombified player email:' . $victimTag->getProfile()->getUser()->getEmail(),
					false
				);
				$actlog->recordAction(
					ActionLogService::TYPE_GAME,
					'email:' . $victimTag->getProfile()->getUser()->getEmail(),
					'zombified by player email:' . $zombieProfile->getUser()->getEmail(),
					false
				);

				$em->flush();

				$this->get('session')->getFlashBag()->add(
					'page.toast',
					$victimTag->getProfile()->getUser()->getFullname() . " has joined the horde, courtesy of " . $zombieProfile->getUser()->getFullname()
				);

				return $this->redirect($this->generateUrl('hvz_register_tag'));
			}
		}
	}

	protected function applyBadges($victim, $zombie, $badgeReg, $infection)
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

		$recentKills = $this->getDoctrine()->getRepository('HvzGameBundle:InfectionSpread')
											->findForKillstreak($zombie);
		$recentKills[] = $infection;

		$this->applyKillstreak($recentKills, 2, 'streak-2', $zombie, $badgeReg);
		$this->applyKillstreak($recentKills, 3, 'streak-3', $zombie, $badgeReg);
		$this->applyKillstreak($recentKills, 4, 'streak-4', $zombie, $badgeReg);
		$this->applyKillstreak($recentKills, 5, 'streak-5', $zombie, $badgeReg);
		$this->applyKillstreak($recentKills, 6, 'streak-6', $zombie, $badgeReg);
		$this->applyKillstreak($recentKills, 7, 'streak-7', $zombie, $badgeReg);
		$this->applyKillstreak($recentKills, 8, 'streak-8', $zombie, $badgeReg);
		$this->applyKillstreak($recentKills, 9, 'streak-9', $zombie, $badgeReg);
		$this->applyKillstreak($recentKills, 10, 'streak-10', $zombie, $badgeReg);
	}

	protected function applyKillstreak($recent, $streak, $badge, $zombie, $badgeReg)
	{
		$available = array();
		foreach($recent as $infection)
		{
			if(!array_key_exists($streak, $infection->getKillstreaks()))
				$available[] = $infection;
		}

		if(count($available) >= $streak)
		{
			$badgeReg->addBadge($zombie, $badge, false);

			foreach($available as $infection)
			{
				$streaks = $infection->getKillstreaks();
				for($i = $streak; $i > 1; $i--)
				{
					$streaks[$i] = true;
				}

				$infection->setKillstreaks($streaks);
			}
		}
	}
}
