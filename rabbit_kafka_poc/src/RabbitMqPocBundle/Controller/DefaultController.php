<?php

namespace RabbitMqPocBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction()
    {
        return $this->render('RabbitMqPocBundle:Default:index.html.twig');
    }
}
