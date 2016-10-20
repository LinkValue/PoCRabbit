<?php

namespace KafkaPocBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function produceAction()
    {
        $rk = new \RdKafka\Producer();
        $rk->setLogLevel(LOG_DEBUG);
        $rk->addBrokers("127.0.0.1");

        $topic = $rk->newTopic("kafka");
        $topic->produce(RD_KAFKA_PARTITION_UA, 0, "Message payload2");

        return $this->render('KafkaPocBundle:Default:index.html.twig', [
                'errors' => [],
                'messages' => []
            ]);
    }

    public function consumeAction()
    {
        // $rk = new \RdKafka\Consumer();
        // $rk->setLogLevel(LOG_DEBUG);
        // $rk->addBrokers("127.0.0.1");

        // $topic = $rk->newTopic("kafka");

        // // The first argument is the partition to consume from.
        // // The second argument is the offset at which to start consumption. Valid values
        // // are: RD_KAFKA_OFFSET_BEGINNING, RD_KAFKA_OFFSET_END, RD_KAFKA_OFFSET_STORED.
        // $topic->consumeStart(0, RD_KAFKA_OFFSET_BEGINNING);

        // $errors = [];
        // $messages = [];
        // while (true) {
        //     // The first argument is the partition (again).
        //     // The second argument is the timeout.
        //     $msg = $topic->consume(0, 100);
        //     if ($msg->err) {
        //         $errors[] = $msg->errstr(). "\n";
        //         break;
        //     } else {
        //         $messages[] = $msg->payload. "\n";
        //     }
        // }
        // 
        
        $conf = new \RdKafka\Conf();

        // Set a rebalance callback to log partition assignemts (optional)
        $conf->setRebalanceCb(function (\RdKafka\KafkaConsumer $kafka, $err, array $partitions = null) {
            switch ($err) {
                case RD_KAFKA_RESP_ERR__ASSIGN_PARTITIONS:
                    echo "Assign: ";
                    var_dump($partitions);
                    $kafka->assign($partitions);
                    break;

                 case RD_KAFKA_RESP_ERR__REVOKE_PARTITIONS:
                     echo "Revoke: ";
                     var_dump($partitions);
                     $kafka->assign(NULL);
                     break;

                 default:
                    throw new \Exception($err);
            }
        });

        // Configure the group.id. All consumer with the same group.id will consume
        // different partitions.
        //$conf->set('group.id', 'myConsumerGroup');

        // Initial list of Kafka brokers
        $conf->set('metadata.broker.list', '127.0.0.1');

        $topicConf = new \RdKafka\TopicConf();

        // Set where to start consuming messages when there is no initial offset in
        // offset store or the desired offset is out of range.
        // 'smallest': start from the beginning
        $topicConf->set('auto.offset.reset', 'smallest');

        // Set the configuration to use for subscribed/assigned topics
        $conf->setDefaultTopicConf($topicConf);

        $consumer = new \RdKafka\KafkaConsumer($conf);

        // Subscribe to topic 'test'
        $consumer->subscribe(['test']);

        echo "Waiting for partition assignment... (make take some time when\n";
        echo "quickly re-joinging the group after leaving it.)\n";

        while (true) {
            $message = $consumer->consume(120*1000);
            switch ($message->err) {
                case RD_KAFKA_RESP_ERR_NO_ERROR:
                    var_dump($message);
                    break;
                case RD_KAFKA_RESP_ERR__PARTITION_EOF:
                    echo "No more messages; will wait for more\n";
                    break;
                case RD_KAFKA_RESP_ERR__TIMED_OUT:
                    echo "Timed out\n";
                    break;
                default:
                    throw new \Exception($message->errstr(), $message->err);
                    break;
            }
        }

        return $this->render('KafkaPocBundle:Default:index.html.twig', [
                'errors' => $errors,
                'messages' => $messages
            ]);
    }
}
