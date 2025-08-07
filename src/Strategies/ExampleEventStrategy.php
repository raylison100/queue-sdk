<?php

declare(strict_types=1);

namespace QueueSDK\Strategies;

use QueueSDK\DTOs\ConsumerMessageQueueDTO;

class ExampleEventStrategy extends AbstractEventStrategy
{
    protected function process(ConsumerMessageQueueDTO $dto): void
    {
        $data = $dto->getBody();
        $headers = $dto->getHeaders();

        $this->log('info', 'Processing example event', [
            'event_type' => $headers['EventType'] ?? 'unknown',
            'data_keys' => array_keys($data)
        ]);

        // Sua lógica de processamento aqui
        // Por exemplo: validar dados, salvar no banco, chamar APIs externas, etc.

        $this->log('info', 'Example event processed successfully', [
            'processed_at' => date('Y-m-d H:i:s')
        ]);
    }
}
