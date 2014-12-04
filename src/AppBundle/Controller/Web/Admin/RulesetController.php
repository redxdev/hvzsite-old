<?php

namespace AppBundle\Controller\Web\Admin;

use AppBundle\Entity\Ruleset;
use AppBundle\Form\RulesetType;
use AppBundle\Service\ActionLogService;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class RulesetController extends Controller
{
    /**
     * @Route("/admin/rulesets", name="web_admin_rulesets")
     * @Security("is_granted('ROLE_ADMIN')")
     */
    public function rulesetsAction()
    {
        $contentManager = $this->get('content_manager');

        $list = $contentManager->getRulesetList();

        $content = $this->renderView(
            ":Admin:rulesets.html.twig",
            $list
        );

        return new Response($content);
    }

    /**
     * @Route("/admin/ruleset/{id}/delete", name="web_admin_ruleset_delete", requirements={"id" = "\d+"})
     * @ParamConverter("ruleset", class="AppBundle:Ruleset")
     * @Security("is_granted('ROLE_ADMIN')")
     */
    public function rulesetDeleteAction(Request $request, Ruleset $ruleset)
    {
        $token = $request->query->get('token');
        if(!$this->isCsrfTokenValid("ruleset_delete", $token))
        {
            $request->getSession()->getFlashBag()->add(
                'page.toast',
                'Invalid CSRF token. Try deleting the ruleset again!'
            );

            return $this->redirectToRoute('web_admin_rulesets');
        }

        $entityManager = $this->getDoctrine()->getManager();
        $actLog = $this->get('action_log');

        $actLog->record(
            ActionLogService::TYPE_ADMIN,
            $this->getuser()->getEmail(),
            'Deleted ruleset ' . $ruleset->getTitle(),
            false
        );

        $entityManager->remove($ruleset);
        $entityManager->flush();

        $request->getSession()->getFlashBag()->add(
            'page.toast',
            'Successfully deleted mission ' . $ruleset->getTitle()
        );

        return $this->redirectToRoute('web_admin_rulesets');
    }

    /**
     * @Route("/admin/ruleset/create", name="web_admin_ruleset_create")
     * @Security("is_granted('ROLE_ADMIN')")
     */
    public function rulesetCreateAction(Request $request)
    {
        $ruleset = new Ruleset();
        $form = $this->createForm(new RulesetType(), $ruleset);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid())
        {
            $actLog = $this->get('action_log');
            $actLog->record(
                ActionLogService::TYPE_ADMIN,
                $this->getUser()->getEmail(),
                "Created new ruleset " . $ruleset->getTitle(),
                false
            );

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($ruleset);
            $entityManager->flush();

            $request->getSession()->getFlashBag()->add(
                'page.toast',
                'Successfully created ruleset ' . $ruleset->getTitle()
            );

            return $this->redirectToRoute('web_admin_rulesets');
        }

        $content = $this->renderView(
            ":Admin:edit_ruleset.html.twig",
            [
                "form" => $form->createView()
            ]
        );

        return new Response($content);
    }

    /**
     * @Route("/admin/ruleset/{id}/edit", name="web_admin_ruleset_edit", requirements={"id" = "\d+"})
     * @ParamConverter("ruleset", class="AppBundle:Ruleset")
     * @Security("is_granted('ROLE_ADMIN')")
     */
    public function rulesetEditAction(Request $request, Ruleset $ruleset)
    {
        $form = $this->createForm(new RulesetType(), $ruleset);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid())
        {
            $actLog = $this->get('action_log');
            $actLog->record(
                ActionLogService::TYPE_ADMIN,
                $this->getUser()->getEmail(),
                "Edited ruleset " . $ruleset->getTitle(),
                false
            );

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->flush();

            $request->getSession()->getFlashBag()->add(
                'page.toast',
                'Successfully edited ruleset ' . $ruleset->getTitle()
            );

            return $this->redirectToRoute('web_admin_rulesets');
        }

        $content = $this->renderView(
            ':Admin:edit_ruleset.html.twig',
            [
                "form" => $form->createView()
            ]
        );

        return new Response($content);
    }
}