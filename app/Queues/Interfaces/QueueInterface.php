<?php

declare(strict_types=1);

namespace App\Queues\Interfaces;

use App\DTOs\ConsumerMessageQueueDTO;
use App\DTOs\PublishMessageQueueDTO;

interface QueueInterface
{
    public function consume(): ?ConsumerMessageQueueDTO;

    public function publish(PublishMessageQueueDTO $publishMessageQueueDTO): void;
}
