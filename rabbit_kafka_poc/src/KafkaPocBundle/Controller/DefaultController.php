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

        $topic = $rk->newTopic("k");
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
        $topic = $rk->newTopic("k");

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
        $handle = fopen('http://clips.vorwaerts-gmbh.de/big_buck_bunny.mp4', 'rb');
        $chunkSize = 1024; // The size of each chunk to output
        $position = 
 
    
        $rk = new \RdKafka\Producer();
        $rk->setLogLevel(LOG_DEBUG);
        $rk->addBrokers("127.0.0.1");

        $topic = $rk->newTopic("movie2");
        while (!feof($handle)) {
            $buffer = fread($handle, $chunkSize);
            $topic->produce(RD_KAFKA_PARTITION_UA, 0, $buffer);
            ob_flush();
            flush();
        }
        fclose($handle);
        
        return new Response();
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
        $handle = fopen(__DIR__.'/movie.txt', 'w+');
        
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
}
