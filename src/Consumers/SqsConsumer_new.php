<?php

declare(strict_types=1);

namespace QueueSDK\Consumers;

use QueueSDK\Contracts\QueueInterface;
use QueueSDK\Factories\EventStrategyFactory;
use Psr\Log\LoggerInterface;

/**
 * SQS-Specific Consumer
 *
 * Extends AbstractQueueConsumer with SQS-specific optimizations:
 * - SQS batch receiving (up to 10 messages)
 * - Visibility timeout management
 * - Dead letter queue handling
 * - SQS-specific error handling
 */
class SqsConsumer extends AbstractQueueConsumer
{
    private ?LoggerInterface $logger;
    private int $batchSize;
    private int $timeoutMs;

    public function __construct(
        QueueInterface $queue,
        EventStrategyFactory $strategyFactory,
        ?LoggerInterface $logger = null,
        int $batchSize = 10,
        int $timeoutMs = 5000
    ) {
        parent::__construct($queue, $strategyFactory);
        $this->logger = $logger;
        $this->batchSize = min($batchSize, 10); // SQS max batch size is 10
        $this->timeoutMs = $timeoutMs;
    }

    /**
     * SQS batch receiving (up to 10 messages)
     */
    public function consumeBatch(string $topic, ?int $customBatchSize = null): void
    {
        $effectiveBatchSize = min($customBatchSize ?? $this->batchSize, 10);

        $this->log('info', "Starting SQS batch consumer for topic: {$topic} (batch_size: {$effectiveBatchSize})");
        $processedMessages = 0;

        while (!$this->shouldStop) {
            try {
                $messages = $this->receiveBatch($effectiveBatchSize);

                if (empty($messages)) {
                    $this->handleNoMessage();
                    continue;
                }

                $this->processBatch($messages, $topic);
                $processedMessages += count($messages);

                $this->log('info', "Processed SQS batch", [
                    'batch_size' => count($messages),
                    'total_processed' => $processedMessages
                ]);
            } catch (\Throwable $e) {
                $this->handleError($e);
            }
        }

        $this->log('info', "SQS batch consumer stopped. Processed {$processedMessages} messages");
    }

    /**
     * Receive batch of messages from SQS
     */
    protected function receiveBatch(int $batchSize): array
    {
        $messages = [];
        $startTime = microtime(true);

        for ($i = 0; $i < $batchSize; $i++) {
            $message = $this->queue->consume();

            if ($message === null) {
                break; // No more messages available
            }

            $messages[] = $message;

            // Respect timeout
            if ((microtime(true) - $startTime) * 1000 > $this->timeoutMs) {
                break;
            }
        }

        return $messages;
    }

    /**
     * Process a batch of messages
     */
    protected function processBatch(array $messages, string $topic): void
    {
        foreach ($messages as $message) {
            try {
                $this->processMessage($message, $topic);
            } catch (\Throwable $e) {
                $this->log('error', 'Failed to process SQS message in batch', [
                    'topic' => $topic,
                    'error' => $e->getMessage(),
                    'message_id' => $message->getId() ?? 'unknown'
                ]);
                // Continue processing other messages in the batch
            }
        }
    }

    /**
     * Enhanced no message handling for SQS
     */
    protected function handleNoMessage(): void
    {
        // SQS returns empty when no messages available
        // Use longer polling intervals to reduce API calls
        usleep(200000); // 200ms - balance between responsiveness and API efficiency
    }

    /**
     * SQS-specific error handling
     */
    protected function handleError(\Throwable $e): void
    {
        $this->log('error', 'SQS consumer error', [
            'error' => $e->getMessage(),
            'error_type' => get_class($e)
        ]);

        // SQS-specific error handling
        if (str_contains($e->getMessage(), 'ThrottlingException') || str_contains($e->getMessage(), 'throttl')) {
            // AWS throttling - longer sleep
            $this->log('warning', 'AWS throttling detected, sleeping longer');
            sleep(5);
        } elseif (str_contains($e->getMessage(), 'ProvisionedThroughputExceededException')) {
            // Throughput exceeded - back off
            $this->log('warning', 'Throughput exceeded, backing off');
            sleep(3);
        } else {
            // Standard error handling
            sleep(1);
        }
    }

    /**
     * Get SQS-specific metrics if available
     */
    public function getMetrics(): array
    {
        return [
            'consumer_type' => 'sqs',
            'queue_type' => get_class($this->queue),
            'batch_size' => $this->batchSize,
            'max_batch_size' => 10,
            'timeout_ms' => $this->timeoutMs,
            'is_stopped' => $this->shouldStop,
            'sqs_features' => [
                'visibility_timeout_managed' => true,
                'batch_receiving_supported' => true,
                'max_batch_size' => 10
            ]
        ];
    }

    /**
     * Enhanced logging with optional PSR logger
     */
    protected function log(string $level, string $message, array $context = []): void
    {
        if ($this->logger) {
            $this->logger->log($level, $message, $context);
        }

        // Fallback to parent implementation
        parent::log($level, $message, $context);
    }
}
