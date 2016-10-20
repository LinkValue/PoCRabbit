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
        for ($i=0; $i < 20000; $i++) {
            $this->container
            ->get('old_sound_rabbit_mq.workqueue_producer')
            ->publish(serialize(sprintf('message n°%d', $i)));
        }
        
        return $this->render('RabbitMqPocBundle:Default:index.html.twig', [
            'what' => 'producer vient d\'envoyer 20000 messages dans une queue workQueue' 
            ]);
    }

    public function pubSubAction()
    {
        for ($i=0; $i < 5000; $i++) { 
            $this->container
            ->get('rabbit.producer.pubSub')
            ->publish(serialize(sprintf('test fdsfsdf%d', $i)));
        }
        
        return $this->render('RabbitMqPocBundle:Default:index.html.twig', [
            'what' => 'producer vient d\'envoyer 5000 messages dans 2 queue fanout 1 et 2' 
            ]);
    }

    public function topicAction()
    {
        for ($i=0; $i < 5000; $i++) { 
            $this->container
            ->get('rabbit.producer.topic')
            ->publish(serialize(sprintf('test routing key 1-%d', $i)), 'routing_key.1');

            $this->container
            ->get('rabbit.producer.topic')
            ->publish(serialize(sprintf('test routing key 2-%d', $i)), 'routing_key.2');
        }
        
        return $this->render('RabbitMqPocBundle:Default:index.html.twig', [
            'what' => 'producer vient d\'envoyer 5000 messages avec la routing key 1 et 2 dans une queue' 
            ]);
    }


}
