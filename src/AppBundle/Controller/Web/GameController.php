<?php

namespace AppBundle\Controller\Web;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
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

    /**
     * @Route("/register_infection", name="web_register_infection_submit")
     * @Method({"POST"})
     */
    public function registerInfectionSubmitAction(Request $request)
    {
        $token = $request->request->get('_token');
        $humanIdStr = $request->request->get('human');
        $zombieIdStr = $request->request->get('zombie');
        $latitude = $request->request->get('latitude');
        $longitude = $request->request->get('longitude');

        if(!$this->isCsrfTokenValid("register_infection", $token))
        {
            $content = $this->renderView(
                ":Game:register_infection.html.twig",
                [
                    "status" => "error",
                    "errors" => ["Invalid CSRF token; try resubmitting the form."],
                    "human" => $humanIdStr,
                    "zombie" => $zombieIdStr
                ]
            );

            return new Response($content);
        }

        $gameManager = $this->get('game_manager');
        $result = $gameManager->processInfection($humanIdStr, $zombieIdStr, $latitude, $longitude);

        if($result["status"] == "ok")
        {
            $request->getSession()->getFlashBag()->add(
                'page.toast',
                $result["human_name"] . " has joined the horde, courtesy of " . $result["zombie_name"] . "!"
            );
        }

        $content = $this->renderView(
            ":Game:register_infection.html.twig",
            $result
        );

        return new Response($content);
    }

    /**
     * @Route("/register_infection/multiple", name="web_register_multiple_infections")
     * @Method({"GET"})
     */
    public function registerMultipleInfectionsAction()
    {
        $content = $this->renderView(
            ":Game:register_multiple_infections.html.twig",
            []
        );

        return new Response($content);
    }

    /**
     * @Route("/register_infection/multiple", name="web_register_multiple_infections_submit")
     * @Method({"POST"})
     */
    public function registerMultipleInfectionsSubmitAction(Request $request)
    {
        $token = $request->request->get('_token');
        $humanIdStr = $request->request->get('humans');
        $zombieIdStr = $request->request->get('zombie');
        $latitude = $request->request->get('latitude');
        $longitude = $request->request->get('longitude');

        if(!$this->isCsrfTokenValid("register_infection", $token))
        {
            $content = $this->renderView(
                ":Game:register_infection.html.twig",
                [
                    "status" => "error",
                    "errors" => ["Invalid CSRF token; try resubmitting the form."],
                    "humans" => $humanIdStr,
                    "zombie" => $zombieIdStr
                ]
            );

            return new Response($content);
        }

        $humanIdList = preg_split('/\r\n|[\r\n]/', $humanIdStr);

        if(count($humanIdList) > 10)
        {
            $content = $this->renderView(
                ":Game:register_infection.html.twig",
                [
                    "status" => "error",
                    "errors" => ["You can only submit up to 10 infections at a time"],
                    "humans" => $humanIdStr,
                    "zombie" => $zombieIdStr
                ]
            );

            return new Response($content);
        }

        $gameManager = $this->get('game_manager');

        $errors = [];
        $successes = [];

        foreach($humanIdList as $idStr)
        {
            $result = $gameManager->processInfection($idStr, $zombieIdStr, $latitude, $longitude);

            if ($result["status"] == "ok") {
                $successes[] = $result["human_name"] . " has joined the horde, courtesy of " . $result["zombie_name"] . " (used: " . $idStr . ")";
            }
            else {
                foreach($result["errors"] as $error)
                {
                    $errors[] = $idStr . " could not be used: " . $error;
                }
            }
        }

        $content = $this->renderView(
            ":Game:register_multiple_infections.html.twig",
            [
                "errors" => $errors,
                "successes" => $successes,
                "humans" => $humanIdStr,
                "zombie" => $zombieIdStr
            ]
        );

        return new Response($content);
    }

    /**
     * @Route("/antivirus", name="web_antivirus")
     * @Method({"GET"})
     */
    public function antivirusAction()
    {
        $gameManager = $this->get('game_manager');

        $avClan = null;
        if($this->getUser() != null)
        {
            $avClan = $this->getUser()->getClan();
        }

        $content = $this->renderView(
            ":Game:antivirus.html.twig",
            [
                "valid_time" => $gameManager->isValidAntiVirusTime(),
                "av_clan" => $avClan
            ]
        );

        return new Response($content);
    }

    /**
     * @Route("/antivirus", name="web_antivirus_submit")
     * @Method({"POST"})
     */
    public function antivirusSubmitAction(Request $request)
    {
        $token = $request->request->get('_token');
        $avIdStr = $request->request->get('antivirus');
        $zombieIdStr = $request->request->get('zombie');

        if(!$this->isCsrfTokenValid("antivirus", $token))
        {
            $content = $this->renderView(
                ":Game:antivirus.html.twig",
                [
                    "status" => "error",
                    "errors" => ["Invalid CSRF token; try resubmitting the form."],
                    "antivirus" => $avIdStr,
                    "zombie" => $zombieIdStr
                ]
            );

            return new Response($content);
        }

        $gameManager = $this->get('game_manager');
        $result = $gameManager->processAntiVirus($avIdStr, $zombieIdStr);
        $result["valid_time"] = $gameManager->isValidAntiVirusTime();

        if($result["status"] == "ok")
        {
            $request->getSession()->getFlashBag()->add(
                'page.toast',
                $result["zombie_name"] . "has taken an antivirus and become human once more!"
            );
        }

        $content = $this->renderView(
            ":Game:antivirus.html.twig",
            $result
        );

        return new Response($content);
    }
}