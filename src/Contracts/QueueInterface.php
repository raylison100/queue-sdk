<?php

declare(strict_types=1);

namespace QueueSDK\Contracts;

use QueueSDK\DTOs\ConsumerMessageQueueDTO;
use QueueSDK\DTOs\PublishMessageQueueDTO;

interface QueueInterface
{
    public function consume(): ?ConsumerMessageQueueDTO;

    public function publish(PublishMessageQueueDTO $publishMessageQueueDTO): void;

    public function ack(ConsumerMessageQueueDTO $dto): void;

    public function nack(ConsumerMessageQueueDTO $dto): void;
}
