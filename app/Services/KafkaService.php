<?php

namespace App\Services;

use App\Abstracts\AbstractService;
use App\Models\ReceiverModel;
use RdKafka\Conf;
use RdKafka\Producer;

class KafkaService extends AbstractService
{
    protected $modelClass = ReceiverModel::class;


    public function sendNotification($topic, $data)
    {
        $conf = new Conf();

        $conf->set('metadata.broker.list', config('kafka.config.broker_list'));
        $conf->set('security.protocol', config('kafka.config.security'));
        $conf->set('sasl.mechanism', config('kafka.config.mechanism'));
        $conf->set('sasl.username', config('kafka.config.username'));
        $conf->set('sasl.password', config('kafka.config.password'));

        $producer = new Producer($conf);
        $topic = $producer->newTopic($topic);
        $message = json_encode([$data]);

        $topic->produce(RD_KAFKA_PARTITION_UA, 0, $message);

        for ($flushRetries = 0; $flushRetries < 10; $flushRetries++) {
            $result = $producer->flush(10000);
            if (RD_KAFKA_RESP_ERR_NO_ERROR === $result) {
                break;
            }
        }
    }

}
