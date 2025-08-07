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

        // Validate required fields if getRequiredFields is implemented
        if (method_exists($this, 'getRequiredFields')) {
            $requiredFields = $this->getRequiredFields();
            foreach ($requiredFields as $field) {
                if (!isset($body[$field]) || $body[$field] === null) {
                    throw new \InvalidArgumentException("Required field '{$field}' is missing from message body");
                }
            }
        }
    }

    public function handle(ConsumerMessageQueueDTO $dto): void
    {
        $this->validateMessage($dto);
        $this->process($dto);
    }

    abstract protected function process(ConsumerMessageQueueDTO $dto): void;
}
