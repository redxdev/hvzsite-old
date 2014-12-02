<?php

namespace AppBundle\Controller\Web;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AuthController extends Controller
{
    /**
     * @Route("/auth/register", name="web_auth_register_redirect")
     */
    public function registerRedirectAction()
    {
        $googleAuthService = $this->get('google_oauth.client');
        $client = $googleAuthService->createClient($this->generateUrl("web_auth_register", [], true));

        return $this->redirect($client->createAuthUrl());
    }

    /**
     * @Route("/auth/register/code", name="web_auth_register")
     */
    public function registerAction(Request $request)
    {
        if($request->query->get('error') == "access_denied")
        {
            $content = $this->renderView(
                "::message.html.twig",
                [
                    "message" => [
                        "type" => "danger",
                        "body" => "Access to your account was denied. Please try again!"
                    ]
                ]
            );

            return new Response($content);
        }

        $code = $request->query->get('code');
        $gameAuth = $this->get('game_authentication');
        $result = $gameAuth->registerAccount($code, $this->generateUrl('web_auth_register', [], true));

        if($result["status"] == "error")
        {
            $content = $this->renderView(
                "::message.html.twig",
                [
                    "message" => [
                        "type" => "danger",
                        "body" => $result["error"]
                    ]
                ]
            );

            return new Response($content);
        }
        else
        {
            $content = $this->renderView(
                "::message.html.twig",
                [
                    "message" => [
                        "type" => "success",
                        "body" => "You have successfully registered! Your account must be activated by an administrator or moderator before you can log in."
                    ]
                ]
            );

            return new Response($content);
        }
    }
}