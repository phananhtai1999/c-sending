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

        $conf->set('metadata.broker.list', config('kafka.config.broker_list'));
        $conf->set('security.protocol', config('kafka.config.security'));
        $conf->set('sasl.mechanism', config('kafka.config.mechanism'));
        $conf->set('sasl.username', config('kafka.config.username'));
        $conf->set('sasl.password', config('kafka.config.password'));

        $producer = new Producer($conf);
        $topic = $producer->newTopic(env('KAFKA_TOPIC'));
        $message = json_encode(['receiver_id' => '63ae51e9c2922582bd065c3e', 'status' => 'active']);

        $topic->produce(RD_KAFKA_PARTITION_UA, 0, $message);

        for ($flushRetries = 0; $flushRetries < 10; $flushRetries++) {
            $result = $producer->flush(10000);
            if (RD_KAFKA_RESP_ERR_NO_ERROR === $result) {
                break;
            }
        }

    }
}
