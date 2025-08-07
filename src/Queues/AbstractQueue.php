<?php

declare(strict_types=1);

namespace QueueSDK\Queues;

use QueueSDK\Contracts\QueueInterface;

abstract class AbstractQueue implements QueueInterface
{
    protected array $config;

    public function __construct(array $config = [])
    {
        $this->config = $config;
    }

    protected function getConfig(string $key, mixed $default = null): mixed
    {
        return $this->config[$key] ?? $default;
    }

    protected function log(string $level, string $message, array $context = []): void
    {
        // Basic logging - can be overridden by implementations
        error_log(sprintf('[%s] %s: %s %s', strtoupper($level), date('Y-m-d H:i:s'), $message, json_encode($context)));
    }
}
