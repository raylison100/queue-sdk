<?php

declare(strict_types=1);

namespace Tests\Unit\DTOs;

use PHPUnit\Framework\TestCase;
use QueueSDK\DTOs\PublishMessageQueueDTO;

class PublishMessageDTOTest extends TestCase
{
    public function testCanCreatePublishMessageDTO(): void
    {
        $data = [
            'body' => ['user_id' => 123, 'action' => 'created'],
            'headers' => ['EventType' => 'user_created']
        ];

        $dto = new PublishMessageQueueDTO($data);

        $this->assertEquals(['user_id' => 123, 'action' => 'created'], $dto->getBody());
        $this->assertEquals(['EventType' => 'user_created'], $dto->getHeaders());
    }

    public function testCanCreateWithMinimalData(): void
    {
        $dto = new PublishMessageQueueDTO([]);

        $this->assertEquals([], $dto->getBody());
        $this->assertEquals([], $dto->getHeaders());
    }

    public function testCanCreateWithBodyOnly(): void
    {
        $data = [
            'body' => ['message' => 'test']
        ];

        $dto = new PublishMessageQueueDTO($data);

        $this->assertEquals(['message' => 'test'], $dto->getBody());
        $this->assertEquals([], $dto->getHeaders());
    }

    public function testCanCreateWithHeadersOnly(): void
    {
        $data = [
            'headers' => ['type' => 'notification']
        ];

        $dto = new PublishMessageQueueDTO($data);

        $this->assertEquals([], $dto->getBody());
        $this->assertEquals(['type' => 'notification'], $dto->getHeaders());
    }

    public function testToArrayReturnsAllData(): void
    {
        $data = [
            'body' => ['test' => 'data'],
            'headers' => ['type' => 'test']
        ];

        $dto = new PublishMessageQueueDTO($data);
        $result = $dto->toArray();

        $this->assertEquals($data, $result);
    }

    public function testJsonSerialize(): void
    {
        $data = [
            'body' => ['user_id' => 456],
            'headers' => ['EventType' => 'user_updated']
        ];

        $dto = new PublishMessageQueueDTO($data);
        $json = json_encode($dto);
        $decoded = json_decode($json, true);

        $this->assertEquals($data, $decoded);
    }
}
