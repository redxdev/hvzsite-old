<?php

namespace AppBundle\Controller\Web;

use AppBundle\Service\ActionLogService;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
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

    /**
     * @Route("/profile/clan", name="web_profile_change_clan")
     * @Method({"GET"})
     * @Security("is_granted('ROLE_USER') && user.getActive()")
     */
    public function changeClanAction()
    {
        $content = $this->renderView(
            ":Game:change_clan.html.twig",
            [
                "clan" => $this->getUser()->getClan()
            ]
        );

        return new Response($content);
    }

    /**
     * @Route("/profile/clan", name="web_profile_submit_clan")
     * @Method({"POST"})
     * @Security("is_granted('ROLE_USER') && user.getActive()")
     */
    public function submitClanAction(Request $request)
    {
        if(!$this->isCsrfTokenValid("change_clan", $request->request->get('_token')))
        {
            $request->getSession()->getFlashBag()->add(
                'page.toast',
                'Invalid CSRF token. Please try submitting again.'
            );

            return $this->redirectToRoute("web_profile_change_clan");
        }

        $clan = trim($request->request->get('clan'));
        if(strlen($clan) > 32)
        {
            $request->getSession()->getFlashBag()->add(
                'page.toast',
                'Your clan is too long.'
            );

            return $this->redirectToRoute("web_profile_change_clan");
        }

        $entityManager = $this->getDoctrine()->getManager();
        $actLog = $this->get('action_log');

        $this->getUser()->setClan($clan);
        $actLog->record(
            ActionLogService::TYPE_PROFILE,
            $this->getUser()->getEmail(),
            'changed clan to ' . $clan,
            false
        );

        $entityManager->flush();

        $request->getSession()->getFlashBag()->add(
            'page.toast',
            'Successfully changed clan to ' . (empty($clan) ? 'none' : $clan) . '.'
        );

        return $this->redirectToRoute("web_profile");
    }
}