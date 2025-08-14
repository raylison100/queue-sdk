<?php

declare(strict_types=1);

namespace QueueSDK\Contracts;

use QueueSDK\DTOs\ConsumerMessageQueueDTO;

interface EventHandleInterface
{
    public function handle(ConsumerMessageQueueDTO $consumerMessageQueueDTO): void;
}
