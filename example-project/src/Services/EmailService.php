<?php

declare(strict_types=1);

namespace ExampleProject\Services;

/**
 * Serviço para envio de emails
 */
class EmailService
{
    public function sendWelcomeEmail(array $userData): void
    {
        // Simular envio de email de boas-vindas
        usleep(120000); // 120ms - simula chamada para API de email

        echo "   📧 Welcome email sent to: {$userData['email']}\n";
    }

    public function sendOrderConfirmation(array $orderData): void
    {
        // Simular envio de confirmação de pedido
        usleep(80000); // 80ms - simula chamada para API de email

        echo "   📧 Order confirmation sent for order: {$orderData['order_id']}\n";
    }

    public function sendShippingNotification(array $orderData): void
    {
        // Simular notificação de envio
        usleep(90000); // 90ms - simula chamada para API de email

        echo "   📦 Shipping notification sent for order: {$orderData['order_id']}\n";
    }
}
