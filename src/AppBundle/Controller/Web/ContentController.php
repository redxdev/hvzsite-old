<?php

namespace AppBundle\Controller\Web;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ContentController extends Controller
{
    /**
     * @Route("/rules", name="web_rules")
     */
    public function rulesAction()
    {
        $contentManager = $this->get('content_manager');

        $content = $this->renderView(
            ":Game:rules.html.twig",
            $contentManager->getRulesetList()
        );

        return new Response($content);
    }
}