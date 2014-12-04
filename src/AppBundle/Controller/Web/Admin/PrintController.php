<?php

namespace AppBundle\Controller\Web\Admin;

use AppBundle\Service\ActionLogService;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class PrintController extends Controller
{
    /**
     * @Route("/admin/print", name="web_admin_print")
     * @Security("is_granted('ROLE_ADMIN')")
     */
    public function indexAction()
    {
        $content = $this->renderView(
            ":Admin:print_options.html.twig",
            []
        );

        return new Response($content);
    }

    /**
     * @Route("/admin/print/preview", name="web_admin_print_preview")
     * @Security("is_granted('ROLE_ADMIN')")
     */
    public function previewAction()
    {
        $profileManager = $this->get('profile_manager');
        $result = $profileManager->getUnprintedProfiles();
        $result["preview"] = true;

        $content = $this->renderView(
            ":Admin:print.html.twig",
            $result
        );

        return new Response($content);
    }

    /**
     * @Route("/admin/print/full", name="web_admin_print_full")
     * @Security("is_granted('ROLE_ADMIN')")
     */
    public function printAction()
    {
        $profileManager = $this->get('profile_manager');
        $result = $profileManager->getUnprintedProfiles();
        $result["preview"] = false;

        $content = $this->renderView(
            ":Admin:print.html.twig",
            $result
        );

        return new Response($content);
    }

    /**
     * @Route("/admin/print/mark", name="web_admin_print_mark")
     * @Security("is_granted('ROLE_ADMIN')")
     */
    public function markPrintedAction(Request $request)
    {
        $token = $request->query->get('token');
        if(!$this->isCsrfTokenValid("print_mark", $token))
        {
            $request->getSession()->getFlashBag()->add(
                'page.toast',
                "Invalid CSRF token. Try marking all as printed again."
            );

            return $this->redirectToRoute("web_admin_print");
        }

        $actLog = $this->get('action_log');
        $entityManager = $this->getDoctrine()->getManager();

        $userRepo = $entityManager->getRepository("AppBundle:User");
        $users = $userRepo->findActiveUnprinted();
        foreach($users as $user)
        {
            $user->setPrinted(true);
        }

        $actLog->record(
            ActionLogService::TYPE_ADMIN,
            $this->getUser()->getEmail(),
            "Marked all active users as printed",
            false
        );

        $entityManager->flush();

        $request->getSession()->getFlashBag()->add(
            'page.toast',
            "Successfully marked all active users as printed."
        );

        return $this->redirectToRoute("web_admin_print");
    }
}