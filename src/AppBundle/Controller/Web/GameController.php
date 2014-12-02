<?php

namespace AppBundle\Controller\Web;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class GameController extends Controller
{
    /**
     * @Route("/register_infection", name="web_register_infection")
     * @Method({"GET"})
     */
    public function registerInfectionAction()
    {
        $content = $this->renderView(
            ":Game:register_infection.html.twig",
            []
        );

        return new Response($content);
    }
}