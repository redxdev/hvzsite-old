<?php

namespace AppBundle\Controller\REST\v1;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class ContentController extends Controller
{
    /**
     * @Route("/rules", name="rest_v1_rules")
     * @Method({"GET"})
     */
    public function rulesAction()
    {
        $contentManager = $this->get('content_manager');

        return new JsonResponse($contentManager->getRulesetList());
    }
}