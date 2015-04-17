<?php

namespace AppBundle\Controller\Web\Admin;

use AppBundle\Entity\HumanId;
use AppBundle\Entity\User;
use AppBundle\Form\Type\UserType;
use AppBundle\Service\ActionLogService;
use AppBundle\Util\GameUtil;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class NotifyController extends Controller
{
    /**
     * @Route("/admin/notify", name="web_admin_notify")
     * @Security("is_granted('ROLE_ADMIN')")
     */
    public function notifyAction()
    {
        $content = $this->renderView(
            ':Admin:notify.html.twig',
            []
        );

        return new Response($content);
    }

    /**
     * @Route("/admin/notify/send", name="web_admin_notify_send")
     * @Security("is_granted('ROLE_ADMIN')")
     * @Method({"POST"})
     */
    public function notifySendAction(Request $request)
    {
        $token = $request->request->get('_token');
        $message = $request->request->get('message');

        if(!$this->isCsrfTokenValid("notify_players", $token))
        {
            $request->getSession()->getFlashBag()->add(
                'page.toast',
                "Invalid CSRF token; Try resubmitting the form."
            );

            return $this->redirectToRoute("web_admin_notify");
        }

        if($message == null || empty($message) || ctype_space($message))
        {
            $request->getSession()->getFlashBag()->add(
                'page.toast',
                "The message was empty. Try actually typing out a message!"
            );

            return $this->redirectToRoute("web_admin_notify");
        }

        $notificationHub = $this->get('notification_hub');
        $notificationHub->broadcastMessage($message);

        $request->getSession()->getFlashBag()->add(
            'page.toast',
            "Notification sent to all players!"
        );

        return $this->redirectToRoute("web_admin_notify");
    }
}