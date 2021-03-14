<?php

namespace App\Console\Commands;
use App\Http\Components\KafkaComponent;

/**
 * @property KafkaComponent KafkaComponent
 * */

class KafkaShell extends BaseShell
{
    protected $signature    = 'kafka { function } {args?*}';
    protected $description  = 'Kafka consumer';

    public function consumer () {
        $config = \Kafka\ConsumerConfig::getInstance();
        $config->setMetadataRefreshIntervalMs(10000);
        $config->setMetadataBrokerList('kafka:9092');
        $config->setGroupId('main');
        $config->setBrokerVersion('1.0.0');
        $config->setTopics(['main']);
        $consumer = new \Kafka\Consumer();

        $consumer->start(function($topic, $part, $message) {
            if (!empty($message)) {
                $data               = json_decode($message['message']['value'], true);
                $data['topic']      = $topic;
                $data['partition']  = $part;

                try {
                    $this->executedJob($data);
                } catch (\Exception $exception) {
                    $this->out($exception->getMessage());
                }
            } else {
                $this->out('No data message found');
            }
        });
    }

    public function producer () {
       $kafka = new KafkaComponent();
       for ($i = 0; $i < 100; $i++) {
           $kafka->pushJob('main', 'sos', 'test', [1, 2, 3, 44]);
       }
    }

    private function executedJob ($data) {
        $class      = $data['class']    ?? null;
        $function   = $data['function'] ?? null;
        $args       = $data['args']     ?? null;

        $command    = "php artisan $class runJob $function";

        if (!empty($args)) {
            $args    = implode(" ", $args);
            $command .= " $args";
        }

        chdir(base_path());
        exec($command, $output, $returnValue);
    }

    public function test1 () {
        pr('minh ahihi');
        pr('ahihi');
        return 0;
    }

}
