<?php

declare(strict_types=1);

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use QueueSDK\DTOs\PublishMessageQueueDTO;

class PublishMessageQueueDTOTest extends TestCase
{
    public function testCanCreatePublishMessageQueueDTO(): void
    {
        $data = [
            'body' => ['user_id' => 456, 'name' => 'John Doe'],
            'headers' => ['event-type' => 'user.created'],
            'key' => 'user_456'
        ];

        $dto = new PublishMessageQueueDTO($data);

        $this->assertEquals(['user_id' => 456, 'name' => 'John Doe'], $dto->getBody());
        $this->assertEquals(['event-type' => 'user.created'], $dto->getHeaders());
        $this->assertEquals('user_456', $dto->getKey());
    }

    public function testCanCreateWithMinimalData(): void
    {
        $dto = new PublishMessageQueueDTO([]);

        $this->assertEquals([], $dto->getBody());
        $this->assertEquals([], $dto->getHeaders());
        $this->assertNull($dto->getKey());
    }

    public function testToArrayReturnsAllData(): void
    {
        $data = [
            'body' => ['test' => 'data'],
            'headers' => ['type' => 'test'],
            'key' => 'test_partition_key'
        ];

        $dto = new PublishMessageQueueDTO($data);
        $result = $dto->toArray();

        $this->assertEquals($data, $result);
    }

    public function testJsonSerializeReturnsCorrectData(): void
    {
        $data = [
            'body' => ['message' => 'Hello World'],
            'headers' => ['content-type' => 'application/json'],
            'key' => 'message_123'
        ];

        $dto = new PublishMessageQueueDTO($data);
        $result = $dto->jsonSerialize();

        $this->assertEquals($data, $result);
    }

    public function testGetKeyReturnsCorrectValue(): void
    {
        $dto = new PublishMessageQueueDTO(['key' => 'partition_key_456']);

        $this->assertEquals('partition_key_456', $dto->getKey());
    }

    public function testGetKeyReturnsNullWhenNotSet(): void
    {
        $dto = new PublishMessageQueueDTO([]);

        $this->assertNull($dto->getKey());
    }

    public function testJsonEncodeWorksCorrectly(): void
    {
        $data = [
            'body' => ['user_id' => 789],
            'headers' => ['event' => 'test'],
            'key' => 'user_789'
        ];

        $dto = new PublishMessageQueueDTO($data);
        $json = json_encode($dto);
        $decoded = json_decode($json, true);

        $this->assertEquals($data, $decoded);
    }
}
