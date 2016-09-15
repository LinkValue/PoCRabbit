<?php

namespace RabbitMqPocBundle\Consumer;

use OldSound\RabbitMqBundle\RabbitMq\ConsumerInterface;
use PhpAmqpLib\Message\AMQPMessage;

class PubSub implements ConsumerInterface
{
    /**
     * @var string
     */
    private $name;

    public function __construct($name)
    {
        $this->name = $name;
    }

    public function execute(AMQPMessage $msg)
    {
        $dir = sys_get_temp_dir();
        $fh = fopen(sprintf('%s/%s.txt', $dir, $this->name), 'a');
        fwrite($fh, $msg->getBody());
        fclose($fh);

        return ConsumerInterface::MSG_ACK;
    }
}