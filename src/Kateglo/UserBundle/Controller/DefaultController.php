<?php

namespace Kateglo\UserBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use FOS\RestBundle\Controller\Annotations\Get;
use FOS\RestBundle\Controller\Annotations\View;

class DefaultController extends Controller
{
    /**
     * @Get("/register/confirm")
     * @View()
     */
    public function confirmAction()
    {
        return array();
    }
}
