<?php

declare(strict_types=1);

namespace QueueSDK;

use QueueSDK\Contracts\QueueInterface;
use QueueSDK\Queues\KafkaQueue;
use QueueSDK\Queues\SqsQueue;
use QueueSDK\DTOs\PublishMessageQueueDTO;
use QueueSDK\DTOs\ConsumerMessageQueueDTO;

/**
 * Queue SDK - Classe principal do SDK
 *
 * Classe principal para gerenciar filas de mensagem com diferentes provedores
 */
class QueueSDK
{
    private QueueInterface $queue;
    private array $config;

    public function __construct(array $config)
    {
        $this->config = $config;
        $this->queue = $this->createQueue();
    }

    private function createQueue(): QueueInterface
    {
        $queueType = $this->config['queue_type'] ?? 'kafka';

        return match ($queueType) {
            'kafka' => new KafkaQueue($this->config['kafka'] ?? []),
            'sqs' => new SqsQueue($this->config['sqs'] ?? []),
            default => throw new \InvalidArgumentException("Queue type '{$queueType}' not supported")
        };
    }

    /**
     * Publica uma mensagem na fila
     */
    public function publish(PublishMessageQueueDTO $message): bool
    {
        return $this->queue->publish($message);
    }

    /**
     * Consome mensagens da fila
     */
    public function consume(string $queueName, callable $callback, array $options = []): void
    {
        $this->queue->consume($queueName, $callback, $options);
    }

    /**
     * Consome uma mensagem única da fila
     */
    public function consumeOne(string $queueName): ?ConsumerMessageQueueDTO
    {
        return $this->queue->consumeOne($queueName);
    }

    /**
     * Obtém a instância da fila configurada
     */
    public function getQueue(): QueueInterface
    {
        return $this->queue;
    }

    /**
     * Obtém a configuração atual
     */
    public function getConfig(): array
    {
        return $this->config;
    }
}
