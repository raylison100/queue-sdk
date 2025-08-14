<?php

declare(strict_types=1);

namespace ExampleProject\Events\Strategies;

use QueueSDK\Contracts\EventHandleInterface;
use QueueSDK\DTOs\ConsumerMessageQueueDTO;
use ExampleProject\Services\UserService;
use ExampleProject\Services\EmailService;
use ExampleProject\Services\NotificationService;

/**
 * Estratégia para processar eventos de usuário criado
 * Demonstra processamento completo de onboarding
 */
class UserCreatedStrategy implements EventHandleInterface
{
    private UserService $userService;
    private EmailService $emailService;
    private NotificationService $notificationService;

    public function __construct(
        UserService $userService,
        EmailService $emailService,
        NotificationService $notificationService
    ) {
        $this->userService = $userService;
        $this->emailService = $emailService;
        $this->notificationService = $notificationService;
    }

    public function handle(ConsumerMessageQueueDTO $dto): void
    {
        $userData = $dto->getBody();
        $headers = $dto->getHeaders();

        echo "🚀 Processing UserCreated event...\n";
        echo "👤 User ID: {$userData['user_id']}\n";
        echo "📧 Email: {$userData['email']}\n";
        echo "📅 Created: {$userData['created_at']}\n";

        // Validação dos dados obrigatórios
        $this->validateUserData($userData);

        // Processamento do usuário
        try {
            // 1. Finalizar setup do usuário
            $this->userService->completeUserSetup($userData);

            // 2. Enviar email de boas-vindas
            $this->emailService->sendWelcomeEmail($userData);

            // 3. Configurar preferências iniciais
            $this->userService->setupDefaultPreferences($userData['user_id']);

            // 4. Notificar sistemas internos
            $this->notificationService->notifyUserCreated($userData);

            // 5. Log de auditoria
            $this->logUserCreationEvent($userData, $headers);

            echo "✅ UserCreated processed successfully\n";
        } catch (\Throwable $e) {
            echo "❌ Error processing UserCreated: {$e->getMessage()}\n";
            throw $e;
        }
    }

    private function validateUserData(array $userData): void
    {
        $required = ['user_id', 'email', 'name', 'created_at'];

        foreach ($required as $field) {
            if (!isset($userData[$field]) || empty($userData[$field])) {
                throw new \InvalidArgumentException("Required field missing: {$field}");
            }
        }

        if (!filter_var($userData['email'], FILTER_VALIDATE_EMAIL)) {
            throw new \InvalidArgumentException("Invalid email format: {$userData['email']}");
        }
    }

    private function logUserCreationEvent(array $userData, array $headers): void
    {
        $logData = [
            'event' => 'user_created',
            'user_id' => $userData['user_id'],
            'email' => $userData['email'],
            'processed_at' => date('Y-m-d H:i:s'),
            'message_id' => $headers['MessageId'] ?? 'unknown'
        ];

        echo "📝 Audit Log: " . json_encode($logData) . "\n";
    }
}
