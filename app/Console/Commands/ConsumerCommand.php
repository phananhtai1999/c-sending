<?php

namespace App\Console\Commands;

use App\Services\ReceiverService;
use Illuminate\Console\Command;
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
        $conf->set('metadata.broker.list', env('KAFKA_BROKER_LIST'));
        $conf->set('security.protocol', env('KAFKA_SECURITY'));
        $conf->set('sasl.mechanism', env('KAFKA_MECHANISM'));
        $conf->set('sasl.username', env('KAFKA_USERNAME'));
        $conf->set('sasl.password', env('KAFKA_PASSWORD'));
        $conf->set('group.id', 'group');
        $conf->set('auto.offset.reset', 'earliest');

        $consumer = new KafkaConsumer($conf);

        $consumer->subscribe([env('KAFKA_TOPIC')]);

        while (true) {
            $message = $consumer->consume(5000);
            if ($message->payload) {
                $request = json_decode($message->payload);
                $service = new ReceiverService();
                $model = $service->findOneById($request->receiver_id);
                $result = $service->update($model, ['status' => $request->status]);
                if ($result) {
                    $this->info('update status successfully');
                }
            }
        }
    }
}
