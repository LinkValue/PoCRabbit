<?php

namespace KafkaPocBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction()
    {
        $rk = new \RdKafka\Producer();
        $rk->setLogLevel(LOG_DEBUG);
        $rk->addBrokers("127.0.0.1");

        $topic = $rk->newTopic("kafka");
        $topic->produce(RD_KAFKA_PARTITION_UA, 0, "Message payload2");

        $rk = new \RdKafka\Consumer();
        $rk->setLogLevel(LOG_DEBUG);
        $rk->addBrokers("127.0.0.1");

        $topic = $rk->newTopic("kafka");

        // The first argument is the partition to consume from.
        // The second argument is the offset at which to start consumption. Valid values
        // are: RD_KAFKA_OFFSET_BEGINNING, RD_KAFKA_OFFSET_END, RD_KAFKA_OFFSET_STORED.
        $topic->consumeStart(0, RD_KAFKA_OFFSET_BEGINNING);

        $errors = [];
        $messages = [];
        while (true) {
            // The first argument is the partition (again).
            // The second argument is the timeout.
            $msg = $topic->consume(0, 100);
            dump($msg);
            if ($msg->err) {
                $errors[] = $msg->errstr(). "\n";
                break;
            } else {
                $messages[] = $msg->payload. "\n";
                break;
            }
        }

        return $this->render('KafkaPocBundle:Default:index.html.twig', [
                'errors' => $errors,
                'messages' => $messages
            ]);
    }
}
