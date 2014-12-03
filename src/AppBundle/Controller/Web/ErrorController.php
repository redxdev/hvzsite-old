<?php

namespace AppBundle\Controller\Web;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ErrorController extends Controller
{
    /**
     * @Route("/error/403", name="web_error_403")
     */
    public function accessDeniedAction()
    {
        $content = $this->renderView(
            "::message.html.twig",
            [
                "message" => [
                    "type" => "danger",
                    "body" => "<strong>403 error:</strong> You do not have access to that page."
                ]
            ]
        );

        $response = new Response($content);
        $response->setStatusCode(403);
        return $response;
    }
}