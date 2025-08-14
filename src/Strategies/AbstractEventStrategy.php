<?php

declare(strict_types=1);

namespace QueueSDK\Strategies;

use QueueSDK\Contracts\EventHandleInterface;
use QueueSDK\DTOs\ConsumerMessageQueueDTO;

abstract class AbstractEventStrategy implements EventHandleInterface
{
    protected function log(string $level, string $message, array $context = []): void
    {
        error_log(sprintf('[%s] %s: %s %s', strtoupper($level), date('Y-m-d H:i:s'), $message, json_encode($context)));
    }

    protected function validateMessage(ConsumerMessageQueueDTO $dto): void
    {
        $body = $dto->getBody();

        if (empty($body)) {
            throw new \InvalidArgumentException('Message body cannot be empty');
        }
    }

    public function handle(ConsumerMessageQueueDTO $dto): void
    {
        $this->validateMessage($dto);
        $this->process($dto);
    }

    abstract protected function process(ConsumerMessageQueueDTO $dto): void;
}
