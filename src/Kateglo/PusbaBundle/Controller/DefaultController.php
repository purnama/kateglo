<?php

namespace Kateglo\PusbaBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction($name)
    {
        return $this->render('KategloPusbaBundle:Default:index.html.twig', array('name' => $name));
    }
}
