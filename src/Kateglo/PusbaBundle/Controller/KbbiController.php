<?php

namespace Kateglo\PusbaBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use FOS\RestBundle\Controller\Annotations\Get;
use FOS\RestBundle\Controller\Annotations\View;

class KbbiController extends Controller
{

    /**
     * @Get("/kbbi/")
     * @View()
     */
    public function indexAction()
    {
        return $this->render('KategloPusbaBundle:Kbbi:index.html.twig');
    }
}
