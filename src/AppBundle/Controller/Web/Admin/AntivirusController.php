<?php

namespace AppBundle\Controller\Web\Admin;

use AppBundle\Entity\AntiVirusId;
use AppBundle\Form\Type\AntiVirusIdType;
use AppBundle\Service\ActionLogService;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AntivirusController extends Controller
{
    /**
     * @Route("/admin/antiviruses", name="web_admin_antiviruses")
     * @Security("is_granted('ROLE_ADMIN')")
     */
    public function antivirusesAction()
    {
        $entityManager = $this->getDoctrine()->getManager();
        $avRepo = $entityManager->getRepository('AppBundle:AntiVirusId');

        $avEnts = $avRepo->findAll();
        $avs = [];
        foreach($avEnts as $av)
        {
            $avs[] = [
                "id" => $av->getId(),
                "active" => $av->getActive(),
                "id_string" => $av->getIdString(),
                "description" => $av->getDescription()
            ];
        }

        $content = $this->renderView(
            ":Admin:antiviruses.html.twig",
            [
                "antiviruses" => $avs
            ]
        );

        return new Response($content);
    }

    /**
     * @Route("/admin/antivirus/{id}/delete", name="web_admin_antivirus_delete")
     * @ParamConverter("av", class="AppBundle:AntiVirusId")
     * @Security("is_granted('ROLE_ADMIN')")
     */
    public function antivirusDeleteAction(Request $request, AntiVirusId $av)
    {
        $token = $request->query->get('token');
        if(!$this->isCsrfTokenValid("antivirus_delete", $token))
        {
            $request->getSession()->getFlashBag()->add(
                'page.toast',
                'Invalid CSRF token. Try deleting the antivirus again!'
            );

            return $this->redirectToRoute('web_admin_antiviruses');
        }

        $entityManager = $this->getDoctrine()->getManager();
        $actLog = $this->get('action_log');

        $actLog->record(
            ActionLogService::TYPE_ADMIN,
            $this->getUser()->getEmail(),
            'Deleted antivirus #' . $av->getId() . ": " . $av->getIdString(),
            false
        );

        $entityManager->remove($av);
        $entityManager->flush();

        $request->getSession()->getFlashBag()->add(
            'page.toast',
            'Successfully deleted antivirus ' . $av->getId()
        );

        return $this->redirectToRoute('web_admin_antiviruses');
    }

    /**
     * @Route("/admin/antivirus/create", name="web_admin_antivirus_create")
     * @Security("is_granted('ROLE_ADMIN')")
     */
    public function antivirusCreateAction(Request $request)
    {
        $idGen = $this->get('id_generator');

        $av = new AntiVirusId();
        $av->setIdString($idGen->generate());
        $form = $this->createForm(new AntiVirusIdType(), $av);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid())
        {
            $actLog = $this->get('action_log');
            $actLog->record(
                ActionLogService::TYPE_ADMIN,
                $this->getUser()->getEmail(),
                "Created new antivirus " . $av->getIdString(),
                false
            );

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($av);
            $entityManager->flush();

            $request->getSession()->getFlashBag()->add(
                'page.toast',
                'Successfully created antivirus ' . $av->getIdString()
            );

            return $this->redirectToRoute('web_admin_antiviruses');
        }

        $content = $this->renderView(
            ":Admin:edit_antivirus.html.twig",
            [
                "form" => $form->createView()
            ]
        );

        return new Response($content);
    }

    /**
     * @Route("/admin/antivirus/{id}/edit", name="web_admin_antivirus_edit")
     * @ParamConverter("av", class="AppBundle:AntiVirusId")
     * @Security("is_granted('ROLE_ADMIN')")
     */
    public function antivirusEditAction(Request $request, AntiVirusId $av)
    {
        $form = $this->createForm(new AntiVirusIdType(), $av);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid())
        {
            $actLog = $this->get('action_log');
            $actLog->record(
                ActionLogService::TYPE_ADMIN,
                $this->getUser()->getEmail(),
                "Created new antivirus " . $av->getIdString(),
                false
            );

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($av);
            $entityManager->flush();

            $request->getSession()->getFlashBag()->add(
                'page.toast',
                'Successfully created antivirus ' . $av->getIdString()
            );

            return $this->redirectToRoute('web_admin_antiviruses');
        }

        $content = $this->renderView(
            ":Admin:edit_antivirus.html.twig",
            [
                "form" => $form->createView()
            ]
        );

        return new Response($content);
    }
}