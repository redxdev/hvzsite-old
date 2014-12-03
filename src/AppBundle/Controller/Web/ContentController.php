<?php

namespace AppBundle\Controller\Web;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
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

    /**
     * @Route("/missions", name="web_missions")
     * @Security("is_granted('ROLE_USER') && (is_granted('ROLE_MOD') || user.getActive())")
     */
    public function missionsAction()
    {
        $contentManager = $this->get('content_manager');

        $content = $this->renderView(
            ":Game:missions.html.twig",
            $contentManager->getTeamMissionList($this->getUser()->getTeam())
        );

        return new Response($content);
    }
}