<?php

namespace Kateglo\DefaultBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use FOS\RestBundle\Controller\Annotations\Get;
use FOS\RestBundle\Controller\Annotations\View;

class DefaultController extends Controller
{
    /**
     * @Get("/")
     * @View()
     */
    public function indexAction()
    {
        return array('name' => 'test');
    }
}
