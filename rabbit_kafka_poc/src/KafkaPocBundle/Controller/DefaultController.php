<?php

namespace KafkaPocBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

class DefaultController extends Controller
{
    public function produceAction()
    {
        $rk = new \RdKafka\Producer();
        $rk->setLogLevel(LOG_DEBUG);
        $rk->addBrokers("127.0.0.1");

        $topic = $rk->newTopic("a");
        for ($i=0; $i < 200; $i++) { 
            $topic->produce(RD_KAFKA_PARTITION_UA, 0, "Message payload".$i);
        }

        return $this->render('KafkaPocBundle:Default:index.html.twig', [
                'errors' => [],
                'messages' => []
            ]);
    }

    public function consumeAction()
    {
        $rk = new \RdKafka\Consumer();
        $rk->setLogLevel(LOG_DEBUG);
        $rk->addBrokers("127.0.0.1"); 
        $topic = $rk->newTopic("a");

        $topic->consumeStart(0, RD_KAFKA_OFFSET_BEGINNING);
        $errors = [];
        $messages = [];
        while (true) {
            // The first argument is the partition (again).
            // The second argument is the timeout.
            $msg = $topic->consume(0, 1000);
            if ($msg->err) {
//                $errors[] = $msg->errstr(). "\n";
                break;
            } else {
                $messages[] = $msg->payload. "\n";
            }
        }

        return $this->render('KafkaPocBundle:Default:index.html.twig', [
                'errors' => $errors,
                'messages' => $messages
            ]);
    }

    public function dlAction()
    {
        $handle = fopen('https://twitrss.me/twitter_user_to_rss/?user=linkvalue', 'rb');
        $chunkSize = 56; // The size of each chunk to output
 
        $errors = [];
        $messages = [];
        $i = 0;
        $start = time();

        $rk = new \RdKafka\Producer();
        $rk->setLogLevel(LOG_DEBUG);
        $rk->addBrokers("127.0.0.1");

        $topic = $rk->newTopic("movie2");
        while (!feof($handle)) {
            $buffer = fread($handle, $chunkSize);
            $topic->produce(RD_KAFKA_PARTITION_UA, 0, $buffer);
            ob_flush();
            flush();
            $messages['nb message'] = $i++;
        }
        fclose($handle);
        $end = time();
        $messages['time elapsed'] = $start-$end;
        
        return $this->render('KafkaPocBundle:Default:index.html.twig', [
                'errors' => $errors,
                'messages' => $messages
            ]);
    }

    public function fullMovieAction()
    {
        $rk = new \RdKafka\Consumer();
        $rk->setLogLevel(LOG_DEBUG);
        $rk->addBrokers("127.0.0.1"); 
        $topic = $rk->newTopic("movie2");

        $topic->consumeStart(0, RD_KAFKA_OFFSET_BEGINNING);
        $errors = [];
        $messages = [];
        $movie = '';
        $handle = fopen(__DIR__.'/../../movie.txt', 'w+');
        
        while (true) {
            // The first argument is the partition (again).
            // The second argument is the timeout.
            $msg = $topic->consume(0, 1000);
            if ($msg->err) {
                $errors[] = $msg->errstr(). "\n";
                break;
            } else {
                $messages[] = $msg->payload. "\n";
                fwrite($handle, $msg->payload);
            }
        }
        $movie = file_get_contents(__DIR__.'/movie.txt');
        fclose($handle);

        $response = new Response($movie, 200, [
            'Content-Type' => 'video/mp4',
        ]);
        return $response;
        return $this->render('KafkaPocBundle:Default:index.html.twig', [
                'errors' => $errors,
                'messages' => $messages
            ]);
    }

    public function streamLogAction()
    {
        $handle = fopen(__DIR__.'/../../../var/logs/dev.log', 'r');
        //var_dump($handle); 
        $rk = new \RdKafka\Producer();
        $rk->setLogLevel(LOG_DEBUG);
        $rk->addBrokers("127.0.0.1");

        // $topic = $rk->newTopic("streams-pipe");
        // while (($buffer = fgets($handle, 4096)) !== false) {
        //     //$topic->produce(RD_KAFKA_PARTITION_UA, 0, $buffer);
        // }
        fclose($handle);
        return $this->render('KafkaPocBundle:Default:stream_log.html.twig'); 
    }

    public function showLogAction()
    { 
        return $this->render('KafkaPocBundle:Default:show_log.html.twig');  
    }

    public function getStreamedLogAction()
    {
        $conf = new \RdKafka\Conf();
        $conf->set('formatter', 'kafka.tools.DefaultMessageFormatter');
        // $conf->set('print.key', true);
        // $conf->set('print.value', true);
        // $conf->set('key.deserializer', 'org.apache.kafka.common.serialization.StringDeserializer');
        // $conf->set('value.deserializer', 'org.apache.kafka.common.serialization.StringDeserializer');

        $rk = new \RdKafka\Consumer($conf);
        $rk->setLogLevel(LOG_DEBUG);
        $rk->addBrokers("127.0.0.1");

        $topic = $rk->newTopic("streams-pipe-output");
        $topic->consumeStart(0, RD_KAFKA_OFFSET_BEGINNING);
        $errors = [];
        $messages = [];
        while (true) {
            // The first argument is the partition (again).
            // The second argument is the timeout.
            $msg = $topic->consume(0, 1000);
            if ($msg->err) {
//                $errors[] = $msg->errstr(). "\n";
                break;
            } else {
                $messages[] = $msg->payload;
            }
        }

        return new JsonResponse($messages);
    }
}
