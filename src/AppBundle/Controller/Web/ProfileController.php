<?php

namespace AppBundle\Controller\Web;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ProfileController extends Controller
{
    /**
     * @Route("/profile", name="web_profile")
     * @Security("is_granted('ROLE_USER') && user.getActive()")
     */
    public function viewAction()
    {
        $profileManager = $this->get('profile_manager');

        $result = $profileManager->getProfileInfo($this->getUser());

        $content = $this->renderView(
            ":Game:profile.html.twig",
            $result
        );

        return new Response($content);
    }
}