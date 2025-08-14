<?php

declare(strict_types=1);

namespace ExampleProject\Services;

/**
 * Serviço para notificações internas
 */
class NotificationService
{
    public function notifyUserCreated(array $userData): void
    {
        // Simular notificação para sistemas internos
        usleep(45000); // 45ms - simula chamada para webhooks internos

        echo "   🔔 Internal systems notified of user creation: {$userData['user_id']}\n";
    }

    public function notifyInventoryUpdate(array $inventoryData): void
    {
        // Simular notificação de estoque
        usleep(20000); // 20ms - simula atualização de cache/sistemas

        echo "   🔔 Inventory update notification sent\n";
    }
}
