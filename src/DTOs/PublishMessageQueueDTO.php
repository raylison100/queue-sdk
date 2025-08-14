<?php

declare(strict_types=1);

namespace QueueSDK\DTOs;

class PublishMessageQueueDTO implements \JsonSerializable
{
    private array $headers;
    private array $body;
    private ?string $key;
    private string $topicName;

    public function __construct(array $data)
    {
        $this->headers = $data['headers'] ?? [];
        $this->body = $data['body'] ?? [];
        $this->key = $data['key'] ?? null;
        $this->topicName = $data['topic'] ?? '';
    }

    public function getHeaders(): array
    {
        return $this->headers;
    }

    public function getBody(): array
    {
        return $this->body;
    }

    public function getKey(): ?string
    {
        return $this->key;
    }

    public function getTopicName(): string
    {
        return $this->topicName;
    }

    public function toArray(): array
    {
        return [
            'headers' => $this->headers,
            'body' => $this->body,
            'key' => $this->key,
        ];
    }

    public function jsonSerialize(): array
    {
        return $this->toArray();
    }
}
