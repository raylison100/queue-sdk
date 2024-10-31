<?php

declare(strict_types=1);

namespace App\Eda\Interfaces;

use App\DTOs\ConsumerMessageQueueDTO;

interface EventHandleInterface
{
    public function handle(ConsumerMessageQueueDTO $consumerMessageQueueDTO): void;
}
