<?php

namespace Kateglo\KbbiBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction($name)
    {
        return $this->render('KategloKbbiBundle:Default:index.html.twig', array('name' => $name));
    }
}
