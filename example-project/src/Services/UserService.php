<?php

declare(strict_types=1);

namespace ExampleProject\Services;

/**
 * Serviço para operações relacionadas a usuários
 */
class UserService
{
    public function completeUserSetup(array $userData): void
    {
        // Simular operações de finalização do usuário
        usleep(25000); // 25ms - simula operação de banco

        echo "   📝 User setup completed for: {$userData['email']}\n";
    }

    public function setupDefaultPreferences(string $userId): void
    {
        // Simular configuração de preferências padrão
        usleep(15000); // 15ms - simula operação de banco

        echo "   ⚙️ Default preferences set for user: {$userId}\n";
    }

    public function updateUserProfile(array $userData): void
    {
        // Simular atualização de perfil
        usleep(30000); // 30ms - simula operação de banco

        echo "   👤 Profile updated for user: {$userData['user_id']}\n";
    }
}
