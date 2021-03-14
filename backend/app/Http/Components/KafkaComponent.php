<?php
namespace App\Http\Components;


class KafkaComponent {
    private $config   = null;
    private $producer = null;
    
    public function __construct () {
        $this->config = \Kafka\ProducerConfig::getInstance();
        $this->config->setMetadataRefreshIntervalMs(10000);
        $this->config->setMetadataBrokerList('kafka:9092');
        $this->config->setBrokerVersion('1.0.0');
        $this->config->setRequiredAck(1);
        $this->config->setIsAsyn(false);
        $this->config->setProduceInterval(500);
        $this->producer = new \Kafka\Producer();
    }

    public function pushJob ($topic, $class, $function, $args = []) {
        $dataSend = [
            'class'     => $class,
            'function'  => $function,
            'args'      => $args
        ];

        $this->producer->send([
            [
                'topic' => $topic,
                'value' => json_encode($dataSend)
            ],
        ]);
    }

    public function logKafka ($topic, $partition, $dataInput, $messageError) {

    }
}
