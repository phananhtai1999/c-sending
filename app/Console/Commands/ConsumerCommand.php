<?php

namespace App\Console\Commands;

use App\Services\KafkaService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use RdKafka\Conf;
use RdKafka\KafkaConsumer;

class ConsumerCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:consumer';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Running Consumer';

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
        $conf->set('group.id', 'group');

        $consumer = new KafkaConsumer($conf);

        $consumer->subscribe([env('KAFKA_TOPIC')]);

        while (true) {
            $message = $consumer->consume(5000);
            if (null === $message || $message->err === RD_KAFKA_RESP_ERR__PARTITION_EOF) {
                continue;
            } elseif ($message->err) {
                Log::info($message->errstr() . "\n");
                break;
            } else {
                $request = json_decode($message->payload);
                $service = new KafkaService();
                $model = $service->findOneById($request->receiver_id);
                $result = $service->update($model, ['status' => $request->status]);
                if ($result) {
                    $this->info('update status successfully');
                }
            }
        }
    }
}
