<?php

namespace AppBundle\Controller\Web\Admin;

use AppBundle\Entity\User;
use AppBundle\Form\UserType;
use AppBundle\Service\ActionLogService;
use AppBundle\Util\GameUtil;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

//TODO: Add option to generate additional human ids
class PlayerController extends Controller
{
    /**
     * @Route("/admin/players/{page}", name="web_admin_players", requirements={"page" = "\d+"})
     * @Security("is_granted('ROLE_MOD')")
     */
    public function playersAction($page = 0)
    {
        $gameStatus = $this->get('game_status');

        $list = $gameStatus->getPlayerList($page, 10, GameUtil::SORT_ALL, true, true);
        $list["page"] = $page;

        $content = $this->renderView(
            ":Admin:players.html.twig",
            $list
        );

        return new Response($content);
    }

    /**
     * @Route("/admin/players/search", name="web_admin_players_search")
     * @Security("is_granted('ROLE_MOD')")
     */
    public function playersSearchAction(Request $request)
    {
        $gameStatus = $this->get('game_status');

        $term = $request->query->get('term');
        $list = $gameStatus->searchPlayerList($term, false, true);
        $list["search_term"] = $term;

        $content = $this->renderView(
            ":Admin:players.html.twig",
            $list
        );

        return new Response($content);
    }

    /**
     * @Route("/admin/player/{id}/delete", name="web_admin_player_delete", requirements={"id" = "\d+"})
     * @ParamConverter("user", class="AppBundle:User")
     * @Security("is_granted('ROLE_MOD')")
     */
    public function playerDeleteAction(Request $request, User $user)
    {
        $token = $request->query->get('token');
        if(!$this->isCsrfTokenValid('player_delete', $token))
        {
            $request->getSession()->getFlashBag()->add(
                'page.toast',
                'Invalid CSRF token. Try deleting the player again!'
            );

            return $this->redirectToRoute('web_admin_players');
        }

        $entityManager = $this->getDoctrine()->getManager();
        $actLog = $this->get('action_log');

        $actLog->record(
            ActionLogService::TYPE_ADMIN,
            $this->getUser()->getEmail(),
            "Deleted user " . $user->getEmail(),
            false
        );

        $entityManager->remove($user);
        $entityManager->flush();

        $request->getSession()->getFlashBag()->add(
            'page.toast',
            'Successfully deleted user ' . $user->getFullname() . '.'
        );

        return $this->redirectToRoute('web_admin_players');
    }

    /**
     * @Route("/admin/player/{id}/edit", name="web_admin_player_edit", requirements={"id" = "\d+"})
     * @ParamConverter("user", class="AppBundle:User")
     * @Security("is_granted('ROLE_MOD')")
     */
    public function playerEditAction(Request $request, User $user)
    {
        $form = $this->createForm(new UserType(), $user, ["show_roles" => $this->isGranted("ROLE_ADMIN")]);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid())
        {
            $actLog = $this->get('action_log');
            $actLog->record(
                ActionLogService::TYPE_ADMIN,
                $this->getUser()->getEmail(),
                "Edited user " . $user->getEmail(),
                false
            );

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->flush();

            $request->getSession()->getFlashBag()->add(
                'page.toast',
                'Successfully edited user ' . $user->getFullname() . '.'
            );

            return $this->redirectToRoute('web_admin_players');
        }

        $content = $this->renderView(
            ":Admin:edit_player.html.twig",
            [
                "fullname" => $user->getFullname(),
                "email" => $user->getEmail(),
                "form" => $form->createView()
            ]
        );

        return new Response($content);
    }

    /**
     * @Route("/admin/player/{id}/view", name="web_admin_player_view", requirements={"id" = "\d+"})
     * @ParamConverter("user", class="AppBundle:User")
     * @Security("is_granted('ROLE_MOD')")
     */
    public function playerViewAction(User $user)
    {
        $profileManager = $this->get('profile_manager');

        $result = $profileManager->getProfileInfo($user, true);

        $content = $this->renderView(
            ":Admin:view_player.html.twig",
            $result
        );

        return new Response($content);
    }

    /**
     * @Route("/admin/player/{id}/avatar", name="web_admin_player_avatar_change", requirements={"id" = "\d+"})
     * @ParamConverter("user", class="AppBundle:User")
     * @Security("is_granted('ROLE_MOD')")
     */
    public function changeAvatarAction(Request $request, User $user)
    {
        $content = $this->renderView(
            ":Admin:edit_avatar.html.twig",
            [
                "playerId" => $user->getId()
            ]
        );

        return new Response($content);
    }

    /**
     * @Route("/admin/player/{id}/avatar/submit", name="web_admin_player_avatar_submit", requirements={"id" = "\d+"})
     * @ParamConverter("user", class="AppBundle:User")
     * @Security("is_granted('ROLE_MOD')")
     */
    public function submitAvatarAction(Request $request, User $user)
    {
        $token = $request->query->get('token');
        if(!$this->isCsrfTokenValid("avatar_submit", $token))
        {
            $request->getSession()->getFlashBag()->add(
                'page.toast',
                'Invalid CSRF token. Try taking another picture.'
            );

            return new Response($this->generateUrl('web_admin_player_change_avatar', ['id' => $user->getId()]));
        }

        $oldAvatarPath = $user->getAbsoluteAvatarPath();

        $form = $this->createFormBuilder($user, ['csrf_protection' => false])
            ->add('avatarFile', 'file')
            ->getForm();

        $form->handleRequest($request);

        if($form->isValid())
        {
            $entityManager = $this->getDoctrine()->getManager();
            $actLog = $this->get('action_log');

            $actLog->record(
                ActionLogService::TYPE_ADMIN,
                $this->getUser()->getEmail(),
                "Changed avatar for user " . $user->getEmail(),
                false
            );

            $user->uploadAvatar();

            $entityManager->flush();

            if($oldAvatarPath != null)
            {
                @unlink($oldAvatarPath);
            }

            $this->get('session')->getFlashBag()->add(
                'page.toast',
                "Changed avatar successfully."
            );

            return new Response($this->generateUrl('web_admin_player_view', ['id' => $user->getId()]));
        }

        $this->get('session')->getFlashBag()->add(
            'page.toast',
            "There was a problem changing the avatar."
        );

        return new Response($this->generateUrl('web_admin_player_change_avatar', ['id' => $user->getId()]));
    }
}