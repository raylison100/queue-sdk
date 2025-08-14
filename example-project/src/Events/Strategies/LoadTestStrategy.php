<?php

declare(strict_types=1);

namespace ExampleProject\Events\Strategies;

use QueueSDK\Contracts\EventHandleInterface;
use QueueSDK\DTOs\ConsumerMessageQueueDTO;

/**
 * Estratégia simples para testes de carga
 * Simula processamento básico com I/O realista
 */
class LoadTestStrategy implements EventHandleInterface
{
    public function handle(ConsumerMessageQueueDTO $dto): void
    {
        $data = $dto->getBody();
        $headers = $dto->getHeaders();

        // Processamento básico com simulação de I/O
        $this->simulateProcessing($data);

        // Log mínimo para performance
        if (isset($data['message_id']) && $data['message_id'] % 100 === 0) {
            echo "📊 Processed message: {$data['message_id']}\n";
        }
    }

    private function simulateProcessing(array $data): void
    {
        // Simular operações típicas de um sistema real

        // 1. Validação e parsing (CPU bound)
        $this->validateData($data);

        // 2. Consulta ao banco (I/O bound)
        usleep(rand(20000, 50000)); // 20-50ms

        // 3. Processamento de regras de negócio (CPU bound)
        $this->processBusinessLogic($data);

        // 4. Operação de cache (I/O bound)
        usleep(rand(1000, 5000)); // 1-5ms

        // 5. Chamada para API externa (30% das vezes)
        if (rand(1, 10) <= 3) {
            usleep(rand(50000, 200000)); // 50-200ms
        }

        // 6. Escrita em log/arquivo (I/O bound)
        usleep(rand(5000, 15000)); // 5-15ms

        // 7. Atualização no banco (I/O bound)
        usleep(rand(10000, 30000)); // 10-30ms
    }

    private function validateData(array $data): void
    {
        // Validação simples
        if (!isset($data['message_id'])) {
            throw new \InvalidArgumentException('Missing message_id');
        }
    }

    private function processBusinessLogic(array $data): void
    {
        // Simular processamento CPU-intensivo
        $result = 0;
        for ($i = 0; $i < 1000; $i++) {
            $result += sqrt($i) * log($i + 1);
        }
    }
}
