<?php

namespace AppBundle\Controller\Web;

use AppBundle\Entity\User;
use AppBundle\Form\UserType;
use AppBundle\Util\GameUtil;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AdminController extends Controller
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
     * @Route("/admin/players/delete/{id}", name="web_admin_player_delete", requirements={"id" = "\d+"})
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

        $entityManager = $this->get('doctrine.orm.entity_manager');
        $entityManager->remove($user);
        $entityManager->flush();

        $request->getSession()->getFlashBag()->add(
            'page.toast',
            'Successfully deleted user ' . $user->getFullname() . '.'
        );

        return $this->redirectToRoute('web_admin_players');
    }

    /**
     * @Route("/admin/players/edit/{id}", name="web_admin_player_edit", requirements={"id" = "\d+"})
     * @ParamConverter("user", class="AppBundle:User")
     * @Security("is_granted('ROLE_MOD')")
     */
    public function playerEditAction(Request $request, User $user)
    {
        $form = $this->createForm(new UserType(), $user);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid())
        {
            $entityManager = $this->get('doctrine.orm.entity_manager');
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
}