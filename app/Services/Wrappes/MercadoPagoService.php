<?php

declare(strict_types=1);

namespace App\Services\Wrappes;

use App\DTOs\GenerateOrder;
use App\Services\Interfaces\PaymentServiceInterface;
use Illuminate\Support\Env;
use MercadoPago\Client\Common\RequestOptions;
use MercadoPago\Client\Payment\PaymentClient;
use MercadoPago\MercadoPagoConfig;

class MercadoPagoService implements PaymentServiceInterface
{
    public function __construct()
    {
        MercadoPagoConfig::setAccessToken(Env::get('MERCADO_PAGO_ACCESS_TOKEN', ''));
    }

    /**
     * @throws \Exception
     */
    public function createPix(GenerateOrder $generateOrder): array
    {
        $client = new PaymentClient();

        $request = [
            'transaction_amount' => $generateOrder->getAmount(),
            'description' => '',
            'payment_method_id' => 'pix',
            'payer' => [
                'email' => $generateOrder->getEmail(),
                'first_name' => 'Test',
                'last_name' => 'User',
                'identification' => [
                    'type' => 'CPF',
                    'number' => $generateOrder->getIdentification()['number']
                ],
                'address' => [
                    'zip_code' => $generateOrder->getAddress()['zip_code'],
                    'street_name' => $generateOrder->getAddress()['street_name'],
                    'street_number' => $generateOrder->getAddress()['street_number'],
                    'neighborhood' => $generateOrder->getAddress()['neighborhood'],
                    'city' => $generateOrder->getAddress()['city'],
                    'federal_unit' => $generateOrder->getAddress()['state']
                ]
            ]
        ];

        $request_options = new RequestOptions();
        $request_options->setCustomHeaders(['X-Idempotency-Key: <SOME_UNIQUE_VALUE>']);

        // Step 6: Make the request
        $payment = $client->create($request, $request_options);

        if (201 != $payment->getResponse()->getStatusCode()) {
            throw new \Exception('Error creating payment on Mercado Pago with method PIX');
        }

        return $payment->getResponse()->getContent();
    }

    public function getPayment(string $reference): array
    {
        /*Todo: Implement this method
         $payment = Payment::get($reference);
        return $payment->toArray();*/

        return [];
    }
}
