<?php

namespace RabbitMqPocBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction()
    {
        for ($i=0; $i < 50; $i++) { 
            $this->container
            ->get('old_sound_rabbit_mq.workqueue_producer')
            ->publish(sprintf('value%d', $i));
        }
        

        return $this->render('RabbitMqPocBundle:Default:index.html.twig');
    }
}
