<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use RdKafka\Conf;
use RdKafka\Producer;

class ProducerCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:producer';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Running Producer';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        $conf = new Conf();

        $conf->set('metadata.broker.list', env('KAFKA_BROKER_LIST'));
        $conf->set('security.protocol', env('KAFKA_SECURITY'));
        $conf->set('sasl.mechanism', env('KAFKA_MECHANISM'));
        $conf->set('sasl.username', env('KAFKA_USERNAME'));
        $conf->set('sasl.password', env('KAFKA_PASSWORD'));

        $producer = new Producer($conf);
        $topic = $producer->newTopic(env('KAFKA_TOPIC'));
        $message = json_encode(['account_id' => 1]);

        $topic->produce(RD_KAFKA_PARTITION_UA, 0, $message);

        for ($flushRetries = 0; $flushRetries < 10; $flushRetries++) {
            $result = $producer->flush(10000);
            if (RD_KAFKA_RESP_ERR_NO_ERROR === $result) {
                break;
            }
        }

    }
}
