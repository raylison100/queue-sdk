<?php

declare(strict_types=1);

return [
    'broker' => env('KAFKA_BROKER_LIST'),
    'group_id' => env('KAFKA_GROUP_ID', 'aracdyan-payment-service.group-local'),
    'required_ack' => (int) env('KAFKA_REQUIRED_ACK', -1),
    'interval' => (float) env('KAFKA_RETRY_INTERVAL', 1.5),
    'connect_timeout' => (int) env('KAFKA_CONNECT_TIMEOUT_SECONDS', 30),
    'topics' => [
        'payment_order' => env('KAFKA_PAYMENT_ORDER_TOPIC', 'payment_order.event'),
    ],
];
