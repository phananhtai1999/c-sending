<?php

use Illuminate\Support\Facades\Facade;

return [
    'topic' => [
        'sms' => [
            'default' => env('KAFKA_TOPIC_SMS_VIETTEL'),
            'viettel' => env('KAFKA_TOPIC_SMS_VIETTEL'),
            'mobifone' => env('KAFKA_TOPIC_SMS_MOBIFONE'),
            'vinaphone' => env('KAFKA_TOPIC_SMS_VINAPHONE'),
        ],
        'email' => env('KAFKA_TOPIC_EMAIL'),
        'telegram' => env('KAFKA_TOPIC_TELEGRAM'),
    ],
    'config' => [
        'broker_list' => env('KAFKA_BROKER_LIST'),
        'security' => env('KAFKA_SECURITY'),
        'mechanism' => env('KAFKA_MECHANISM'),
        'username' => env('KAFKA_USERNAME'),
        'password' => env('KAFKA_PASSWORD'),
    ],


    'quantity_receiver' => [
        'sms' => env('KAFKA_QUANTITY_RECEIVER_SMS'),
        'email' => env('KAFKA_QUANTITY_RECEIVER_EMAIL'),
        'telegram' => env('KAFKA_QUANTITY_RECEIVER_TELEGRAM'),
    ]


];
