<?php

declare(strict_types=1);

namespace App\Console\Commands\Queues;

use App\Queues\PaymentOrderQueue;

class PaymentOrderConsumerCommand extends KafkaConsumerCommand
{
    public function __construct()
    {
        parent::__construct(
            'payment-order:consumer',
            'Consume messages from Kafka payment-order topic',
            new PaymentOrderQueue()
        );
    }
}
