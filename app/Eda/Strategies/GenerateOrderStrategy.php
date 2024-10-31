<?php

declare(strict_types=1);

namespace App\Eda\Strategies;

use App\DTOs\ConsumerMessageQueueDTO;
use App\Eda\Interfaces\EventHandleInterface;

class GenerateOrderStrategy implements EventHandleInterface
{
    public function __construct(
    ) {}

    public function handle(ConsumerMessageQueueDTO $consumerMessageQueueDTO): void {}
}
