<?php

namespace KafkaPocBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction()
    {
        return $this->render('KafkaPocBundle:Default:index.html.twig');
    }
}
