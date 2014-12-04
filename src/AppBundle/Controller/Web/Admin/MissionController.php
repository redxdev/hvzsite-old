<?php

namespace AppBundle\Controller\Web\Admin;

use AppBundle\Entity\Mission;
use AppBundle\Form\MissionType;
use AppBundle\Service\ActionLogService;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class MissionController extends Controller
{
    /**
     * @Route("/admin/missions", name="web_admin_missions")
     * @Security("is_granted('ROLE_MOD')")
     */
    public function missionsAction()
    {
        $contentManager = $this->get('content_manager');

        $list = $contentManager->getFullMissionList();

        $content = $this->renderView(
            ":Admin:missions.html.twig",
            $list
        );

        return new Response($content);
    }

    /**
     * @Route("/admin/mission/{id}/delete", name="web_admin_mission_delete", requirements={"id" = "\d+"})
     * @ParamConverter("mission", class="AppBundle:Mission")
     * @Security("is_granted('ROLE_ADMIN')")
     */
    public function missionDeleteAction(Request $request, Mission $mission)
    {
        $token = $request->query->get('token');
        if(!$this->isCsrfTokenValid("mission_delete", $token))
        {
            $request->getSession()->getFlashBag()->add(
                'page.toast',
                'Invalid CSRF token. Try deleting the mission again!'
            );

            return $this->redirectToRoute('web_admin_missions');
        }

        $entityManager = $this->getDoctrine()->getManager();
        $actLog = $this->get('action_log');

        $actLog->record(
            ActionLogService::TYPE_ADMIN,
            $this->getuser()->getEmail(),
            'Deleted mission ' . $mission->getTitle(),
            false
        );

        $entityManager->remove($mission);
        $entityManager->flush();

        $request->getSession()->getFlashBag()->add(
            'page.toast',
            'Successfully deleted mission ' . $mission->getTitle()
        );

        return $this->redirectToRoute('web_admin_missions');
    }

    /**
     * @Route("/admin/mission/create", name="web_admin_mission_create")
     * @Security("is_granted('ROLE_ADMIN')")
     */
    public function missionCreateAction(Request $request)
    {
        $mission = new Mission();
        $form = $this->createForm(new MissionType(), $mission);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid())
        {
            $actLog = $this->get('action_log');
            $actLog->record(
                ActionLogService::TYPE_ADMIN,
                $this->getUser()->getEmail(),
                "Created new mission " . $mission->getTitle(),
                false
            );

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($mission);
            $entityManager->flush();

            $request->getSession()->getFlashBag()->add(
                'page.toast',
                'Successfully created mission ' . $mission->getTitle()
            );

            return $this->redirectToRoute('web_admin_missions');
        }

        $content = $this->renderView(
            ":Admin:edit_mission.html.twig",
            [
                "form" => $form->createView()
            ]
        );

        return new Response($content);
    }

    /**
     * @Route("/admin/mission/{id}/edit", name="web_admin_mission_edit", requirements={"id" = "\d+"})
     * @ParamConverter("mission", class="AppBundle:Mission")
     * @Security("is_granted('ROLE_ADMIN')")
     */
    public function missionEditAction(Request $request, Mission $mission)
    {
        $form = $this->createForm(new MissionType(), $mission);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid())
        {
            $actLog = $this->get('action_log');
            $actLog->record(
                ActionLogService::TYPE_ADMIN,
                $this->getUser()->getEmail(),
                "Edited mission " . $mission->getTitle(),
                false
            );

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->flush();

            $request->getSession()->getFlashBag()->add(
                'page.toast',
                'Successfully edited mission ' . $mission->getTitle()
            );

            return $this->redirectToRoute('web_admin_missions');
        }

        $content = $this->renderView(
            ":Admin:edit_mission.html.twig",
            [
                "form" => $form->createView()
            ]
        );

        return new Response($content);
    }
}