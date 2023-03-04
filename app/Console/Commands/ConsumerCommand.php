<?php

namespace App\Console\Commands;

use App\Services\CampaignService;
use App\Services\KafkaService;
use App\Services\ReceiverService;
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
        $conf->set('group.id', 'group1');

        $consumer = new KafkaConsumer($conf);

        $consumer->subscribe(['email_topic']);

        while (true) {
            $message = $consumer->consume(5000);
            if (null === $message || $message->err === RD_KAFKA_RESP_ERR__PARTITION_EOF) {
                continue;
            } elseif ($message->err) {
                Log::info($message->errstr() . "\n");
            } else {
                $request = json_decode($message->payload);
                foreach ($request[0] as $receiver) {
                    $service = new ReceiverService();
                    $model = $service->findOneById($receiver->receiver_uuid);
                    $result = $service->update($model, ['status' => 'done']);
                    if ($result) {
                        $this->info('update status successfully for receiver ' . $receiver->receiver_uuid);
                    }
                }
            }
        }
    }
}
