<?php

namespace AppBundle\Controller\Web;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ContentController extends Controller
{
    /**
     * @Route("/contact", name="web_contact")
     */
    public function contactAction()
    {
        $content = $this->renderView(
            ":Game:contact.html.twig",
            []
        );

        return new Response($content);
    }

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

    /**
     * @Route("/game_over", name="web_game_over")
     */
    public function gameOverAction()
    {
        $gameStatus = $this->get('game_status')->getGameStatus()['status'];
        if ($gameStatus !== 'end-game') {
            $content = $this->renderView(
                "::message.html.twig",
                [
                    'message' => [
                        'type' => 'danger',
                        'body' => "The game isn't over yet!"
                    ]
                ]
            );

            return new Response($content);
        }

        $contentManager = $this->get('content_manager');

        $content = $this->renderView(
            ":Game:missions.html.twig",
            $contentManager->getFullMissionList()
        );

        return new Response($content);
    }
}