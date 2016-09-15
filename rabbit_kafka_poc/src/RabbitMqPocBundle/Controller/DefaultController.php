<?php

namespace RabbitMqPocBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class DefaultController extends Controller
{
    public function workQueueAction()
    {
        for ($i=0; $i < 10000; $i++) { 
            $this->container
            ->get('old_sound_rabbit_mq.workqueue_producer')
            ->publish(serialize(sprintf('test fdsfsdf%d', $i)));
        }
        
        return $this->render('RabbitMqPocBundle:Default:index.html.twig');
    }

    public function pubSubAction()
    {
        for ($i=0; $i < 10000; $i++) { 
            $this->container
            ->get('rabbit.producer.pubSub')
            ->publish(serialize(sprintf('test fdsfsdf%d', $i)));
        }
        
        return $this->render('RabbitMqPocBundle:Default:index.html.twig');
    }


}
