<?php

namespace Hvz\GameBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

use Hvz\GameBundle\Entity\User;
use Hvz\GameBundle\Entity\PlayerTag;

use Hvz\GameBundle\Security\Authentication\Token\GoogleOAuthToken;

require_once __DIR__ . '/../../../../vendor/google/apiclient/src/Google/Service/Oauth2.php';

class AuthController extends Controller
{

	public function registerAction()
	{
		$client = $this->get('hvz.oauth.google')->createClient($this->generateUrl('hvz_auth_register_code', array(), true));

		return $this->redirect($client->createAuthUrl());
	}

	public function registerCodeAction(Request $request)
	{
		if($request->query->get('error') == 'access_denied')
		{
			$content = $this->renderView(
				'HvzGameBundle:Auth:message.html.twig',
				array(
					'navigation' => $this->get('hvz.navigation')->generate(""),
					"message" => array(
						"type" => "error",
						"body" => "Access to your google account was denied. Try registering again!"
					)
				)
			);

			return new Response($content);
		}

		$code = $request->query->get('code');
		$client = $this->get('hvz.oauth.google')->createClient($this->generateUrl('hvz_auth_register_code', array(), true));
		$oauth = new \Google_Service_Oauth2($client);
		$client->authenticate($code);
		if(!$client->getAccessToken())
		{
			$content = $this->renderView(
				'HvzGameBundle:Auth:message.html.twig',
				array(
					'navigation' => $this->get('hvz.navigation')->generate(""),
					"message" => array(
						"type" => "error",
						"body" => "Invalid authentication token. Please try again."
					)
				)
			);

			return new Response($content);
		}

		$userInfo = $oauth->userinfo->get();
		$email = filter_var($userInfo['email'], FILTER_SANITIZE_EMAIL);

		$userRepo = $this->getDoctrine()->getRepository('HvzGameBundle:User');
		if($userRepo->findOneByEmail($email))
		{
			$content = $this->renderView(
				'HvzGameBundle:Auth:message.html.twig',
				array(
					'navigation' => $this->get('hvz.navigation')->generate(""),
					"message" => array(
						"type" => "error",
						"body" => "You are already registered! Please use the login link instead."
					)
				)
			);

			return new Response($content);
		}

		if(!isset($userInfo['hd']) || ($userInfo['hd'] != 'g.rit.edu' && $userInfo['hd'] != 'rit.edu'))
		{
			$content = $this->renderView(
				'HvzGameBundle:Auth:message.html.twig',
				array(
					'navigation' => $this->get('hvz.navigation')->generate(""),
					"message" => array(
						'navigation' => $this->get('hvz.navigation')->generate(""),
						"type" => "error",
						"body" => "You must use your Google RIT account."
					)
				)
			);

			return new Response($content);
		}

		$em = $this->getDoctrine()->getManager();

		$user = new User();
		$user->setEmail($email);
		$user->setFullname($userInfo['given_name'] . " " . $userInfo['family_name']);
		$em->persist($user);

		$em->flush();

		$client->revokeToken();

		$content = $this->renderView(
			'HvzGameBundle:Auth:message.html.twig',
			array(
				'navigation' => $this->get('hvz.navigation')->generate(""),
				"message" => array(
					"type" => "success",
					"body" => "You have successfully registered! Your account must be activated by an administrator or moderator before you can log in."
				)
			)
		);

		return new Response($content);
	}

	public function loginAction()
	{
		$client = $this->get('hvz.oauth.google')->createClient($this->generateUrl('hvz_auth_login_code', array(), true));

		return $this->redirect($client->createAuthUrl());
	}

	public function loginCodeAction(Request $request)
	{
		if($request->query->get('error') == 'access_denied')
		{
			$content = $this->renderView(
				'HvzGameBundle:Auth:message.html.twig',
				array(
					'navigation' => $this->get('hvz.navigation')->generate(""),
					"message" => array(
						"type" => "error",
						"body" => "Access to your google account was denied. Try logging in again!"
					)
				)
			);

			return new Response($content);
		}

		$code = $request->query->get('code');
		$client = $this->get('hvz.oauth.google')->createClient($this->generateUrl('hvz_auth_login_code', array(), true));
		$oauth = new \Google_Service_Oauth2($client);
		$client->authenticate($code);
		if(!$client->getAccessToken())
		{
			$content = $this->renderView(
				'HvzGameBundle:Auth:message.html.twig',
				array(
					'navigation' => $this->get('hvz.navigation')->generate(""),
					"message" => array(
						"type" => "error",
						"body" => "Invalid authentication token. Please try again."
					)
				)
			);

			return new Response($content);
		}

		$userInfo = $oauth->userinfo->get();
		$email = filter_var($userInfo['email'], FILTER_SANITIZE_EMAIL);

		$userRepo = $this->getDoctrine()->getRepository('HvzGameBundle:User');
		$user = $userRepo->findOneByEmail($email);

		if(!$user)
		{
			$content = $this->renderView(
				'HvzGameBundle:Auth:message.html.twig',
				array(
					'navigation' => $this->get('hvz.navigation')->generate(""),
					"message" => array(
						"type" => "error",
						"body" => "Unknown user. Have you registered?"
					)
				)
			);

			return new Response($content);
		}

		$isAdmin = in_array("ROLE_ADMIN", $user->getRoles()) || in_array("ROLE_SUPER_ADMIN", $user->getRoles());
		$game = $this->getDoctrine()->getRepository('HvzGameBundle:Game')->findCurrentGame();
		if(!$isAdmin && !$game)
		{
			return $this->redirect($this->generateUrl('hvz_error_active'));
		}

		$profile = $this->getDoctrine()->getRepository('HvzGameBundle:Profile')->findByGameAndUser($game, $user);
		if(!$isAdmin && (!$profile || !$profile->getActive()))
		{
			return $this->redirect($this->generateUrl('hvz_error_active'));
		}

		$token = new GoogleOAuthToken($user, $client->getAccessToken(), $this->generateUrl('hvz_auth_login_code', array(), true), 'user_area', $user->getRoles());
		$this->get('security.context')->setToken($token);

		$loginEvent = new InteractiveLoginEvent($this->getRequest(), $token);
		$this->get('event_dispatcher')->dispatch('security.interactive_login', $loginEvent);

		//$client->revokeToken();

		$this->get('session')->getFlashBag()->add(
			'page.toast',
			"You have been successfully logged in."
		);

		return $this->redirect($this->generateUrl('hvz_index'));
	}

	public function logoutAction($token)
	{
		$csrf = $this->get('form.csrf_provider');
		if(!$csrf->isCsrfTokenValid('hvz_auth_logout', $token))
		{
			$content = $this->renderView(
				'HvzGameBundle:Auth:message.html.twig',
				array(
					'navigation' => $this->get('hvz.navigation')->generate(""),
					"message" => array(
						"type" => "error",
						"body" => "Invalid CSRF token: Try logging out again."
					)
				)
			);

			return new Response($content);
		}

		$this->get('security.context')->setToken(null);
		$this->get('request')->getSession()->invalidate();

		$this->get('session')->getFlashBag()->add(
			'page.toast',
			"You have been successfully logged out."
		);

		return $this->redirect($this->generateUrl('hvz_index'));
	}

	public function authErrorAction()
	{
		$content = $this->renderView(
			'HvzGameBundle:Auth:message.html.twig',
			array(
				'navigation' => $this->get('hvz.navigation')->generate(""),
				"message" => array(
					"type" => "error",
					"body" => "<strong>Error 403</strong>: Access denied. Do you have permission to view that page?"
				)
			)
		);

		return new Response($content);
	}

	public function activeErrorAction()
	{
		$content = $this->renderView(
			'HvzGameBundle:Auth:message.html.twig',
			array(
				'navigation' => $this->get('hvz.navigation')->generate(""),
				"message" => array(
					"type" => "error",
					"body" => "Your account hasn't been activated. Please contact an administrator or moderator."
				)
			)
		);

		return new Response($content);
	}
}
