<?php

namespace RabbitMqPocBundle\Producer;

use OldSound\RabbitMqBundle\RabbitMq\ProducerInterface;

class WorkQueue implements ProducerInterface
{
    public function publish($msgBody, $routingKey = '', $additionalProperties = array())
    {
        
    }
}