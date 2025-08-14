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
    private ?QueueInterface $queue = null;
    private array $config;

    public function __construct(array $config)
    {
        $this->config = $config;
    }

    private function createQueue(string $topicName = ''): QueueInterface
    {
        $queueType = $this->config['queue_type'] ?? 'kafka';

        return match ($queueType) {
            'kafka' => new KafkaQueue(
                implode(",", $this->config['kafka']['brokers'] ?? ['kafka:9092']),
                $topicName,
                $this->config['kafka']['group_id'] ?? 'default-group'
            ),
            'sqs' => new SqsQueue($this->config['sqs'] ?? []),
            default => throw new \InvalidArgumentException("Queue type '{$queueType}' not supported")
        };
    }

    /**
     * Publica uma mensagem na fila
     */
    public function publish(PublishMessageQueueDTO $message): bool
    {
        $queue = $this->createQueue($message->getTopicName());
        return $queue->publish($message) ?? false;
    }

    /**
     * Consome mensagens da fila
     */
    public function consume(string $queueName, callable $callback, array $options = []): void
    {
        $queue = $this->createQueue($queueName);
        $queue->consume($queueName, $callback, $options);
    }

    /**
     * Obtém a instância da fila configurada
     */
    public function getQueue(string $topicName = ''): QueueInterface
    {
        if (!$this->queue) {
            $this->queue = $this->createQueue($topicName);
        }
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
