<?php

declare(strict_types=1);

namespace Tests\Integration;

use PHPUnit\Framework\TestCase;
use QueueSDK\Queues\SqsQueue;
use QueueSDK\DTOs\ConsumerMessageQueueDTO;
use QueueSDK\DTOs\PublishMessageQueueDTO;

class SqsQueueIntegrationTest extends TestCase
{
    private SqsQueue $queue;

    protected function setUp(): void
    {
        // Skip integration tests if AWS credentials are not available
        if (empty(getenv('AWS_SQS_QUEUE_URL')) || empty(getenv('AWS_ACCESS_KEY_ID'))) {
            $this->markTestSkipped('AWS credentials not available for integration tests');
        }

        $this->queue = new SqsQueue([
            'queue_url' => getenv('AWS_SQS_QUEUE_URL'),
            'region' => getenv('AWS_DEFAULT_REGION') ?: 'us-east-1',
            'credentials' => [
                'key' => getenv('AWS_ACCESS_KEY_ID'),
                'secret' => getenv('AWS_SECRET_ACCESS_KEY')
            ]
        ]);
    }

    public function testCanPublishAndConsumeMessage(): void
    {
        // Publish a test message
        $publishDto = new PublishMessageQueueDTO([
            'body' => [
                'test_id' => uniqid(),
                'message' => 'Integration test message',
                'timestamp' => time()
            ],
            'headers' => [
                'EventType' => 'integration_test',
                'Source' => 'phpunit'
            ]
        ]);

        $this->queue->publish($publishDto);

        // Wait a moment for SQS to process
        sleep(2);

        // Consume the message
        $consumedMessage = $this->queue->consume();

        $this->assertInstanceOf(ConsumerMessageQueueDTO::class, $consumedMessage);
        $this->assertEquals('Integration test message', $consumedMessage->getBody()['message']);
        $this->assertEquals('integration_test', $consumedMessage->getHeaders()['EventType']);

        // ACK the message to remove it from queue
        $this->queue->ack($consumedMessage);
    }

    public function testCanHandleEmptyQueue(): void
    {
        // Try to consume from empty queue
        $message = $this->queue->consume();

        $this->assertNull($message);
    }

    public function testCanNackMessage(): void
    {
        // Publish a test message
        $publishDto = new PublishMessageQueueDTO([
            'body' => ['test' => 'nack_test'],
            'headers' => ['EventType' => 'nack_test']
        ]);

        $this->queue->publish($publishDto);
        sleep(2);

        // Consume and NACK the message
        $message = $this->queue->consume();
        if ($message) {
            $this->queue->nack($message);

            // Message should be available again after visibility timeout
            $this->assertNotNull($message);
        }
    }
}
