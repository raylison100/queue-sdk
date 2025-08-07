<?php

declare(strict_types=1);

namespace QueueSDK\Consumers;

use QueueSDK\Contracts\QueueInterface;
use QueueSDK\Factories\EventStrategyFactory;
use QueueSDK\DTOs\ConsumerMessageQueueDTO;

abstract class AbstractQueueConsumer
{
    protected QueueInterface $queue;
    protected EventStrategyFactory $strategyFactory;
    protected bool $shouldStop = false;

    public function __construct(QueueInterface $queue, EventStrategyFactory $strategyFactory)
    {
        $this->queue = $queue;
        $this->strategyFactory = $strategyFactory;
    }

    public function consume(string $topic, int $maxMessages = 0): void
    {
        $processedMessages = 0;
        $this->log('info', "Starting consumer for topic: {$topic}");

        while (!$this->shouldStop) {
            try {
                $message = $this->queue->consume();

                if ($message === null) {
                    $this->handleNoMessage();
                    continue;
                }

                $this->processMessage($message, $topic);
                $processedMessages++;

                if ($maxMessages > 0 && $processedMessages >= $maxMessages) {
                    $this->log('info', "Reached max messages limit: {$maxMessages}");
                    break;
                }
            } catch (\Throwable $e) {
                $this->handleError($e);
            }
        }

        $this->log('info', "Consumer stopped. Processed {$processedMessages} messages");
    }

    public function stop(): void
    {
        $this->shouldStop = true;
    }

    protected function processMessage(ConsumerMessageQueueDTO $message, string $topic): void
    {
        try {
            $strategy = $this->strategyFactory->getStrategy($topic);

            if ($strategy === null) {
                $this->log('warning', "No strategy found for topic: {$topic}");
                $this->queue->ack($message);
                return;
            }

            $strategy->handle($message);
            $this->queue->ack($message);

            $this->log('info', 'Message processed successfully', [
                'topic' => $topic,
                'strategy' => get_class($strategy)
            ]);
        } catch (\Throwable $e) {
            $this->log('error', 'Failed to process message', [
                'topic' => $topic,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            $this->queue->nack($message);
        }
    }

    protected function handleNoMessage(): void
    {
        // Override this method to customize behavior when no message is available
        // Default: just continue the loop
    }

    protected function handleError(\Throwable $e): void
    {
        $this->log('error', 'Consumer error', [
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);

        // Default: sleep for a bit before continuing
        sleep(1);
    }

    protected function log(string $level, string $message, array $context = []): void
    {
        error_log(sprintf('[%s] %s: %s %s', strtoupper($level), date('Y-m-d H:i:s'), $message, json_encode($context)));
    }
}
