<?php

declare(strict_types=1);

namespace App\Queues;

use Illuminate\Support\Facades\Config;

class PaymentOrderQueue extends KafkaQueue
{
    public function __construct()
    {
        parent::__construct(
            Config::get('kafka.group_id'),
            Config::get('kafka.topics.payment_order')
        );
    }
}
