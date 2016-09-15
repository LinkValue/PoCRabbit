<?php

namespace RabbitMqPocBundle\Consumer;

use OldSound\RabbitMqBundle\RabbitMq\ConsumerInterface;
use PhpAmqpLib\Message\AMQPMessage;

class WorkQueue implements ConsumerInterface
{
    /**
     * @param AMQPMessage $msg The message
     * @return mixed false to reject and requeue, any other value to acknowledge
     */
    public function execute(AMQPMessage $msg)
    {
        $dir = sys_get_temp_dir();
        $fh = fopen(sprintf('%s/rabbit.txt', $dir), 'a');
        fwrite($fh, $msg->getBody());
        fclose($fh);

        return ConsumerInterface::MSG_ACK;
    }
}