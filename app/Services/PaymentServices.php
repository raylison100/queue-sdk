<?php

declare(strict_types=1);

namespace App\Services;

use App\DTOs\GenerateOrder;
use App\Entities\Payment;
use App\Entities\PaymentGateway;
use App\Entities\PaymentMethod;
use App\Entities\PaymentStatus;
use App\Repositories\PaymentRepository;
use App\Services\Interfaces\PaymentServiceInterface;
use App\Services\Wrappes\MercadoPagoService;

class PaymentServices extends AppService
{
    /**
     * @var PaymentRepository
     */
    protected $repository;

    public function __construct(
        PaymentRepository $repository,
        private readonly MercadoPagoService $mercadoPagoService,
    ) {
        $this->repository = $repository;
    }

    public function createPayment(GenerateOrder $generateOrder): ?Payment
    {
        $payment = match ($generateOrder->getPaymentGatewayId()) {
            PaymentMethod::PIX => $this->getActiveWrapper($generateOrder->getPaymentGatewayId())
                ->createPix($generateOrder),
            default => null,
        };

        $dataPayment = [
            'reference' => $payment['id'],
            'payment_status_id' => PaymentStatus::PENDING,
            'payment_method_id' => PaymentMethod::PIX,
            'payment_gateway_id' => $generateOrder->getPaymentGatewayId(),
            'gateway_info' => json_encode($payment)
        ];

        return $this->repository->skipPresenter()->create($dataPayment);
    }

    public function mercadoPagoNotification(array $data): array
    {
        if ('payment.updated' == $data['action']) {
            $payment = $this->repository->skipPresenter()
                ->findWhere(['reference' => $data['data']['id']])->first();

            if (!empty($payment)) {
                $paymentInfo = $this->getActiveWrapper()->getPayment($payment->reference);
                $status = PaymentStatus::status($paymentInfo['status']);

                $this->repository->update([
                    'payment_status_id' => $status,
                    'gateway_info' => json_encode($paymentInfo)
                ], $payment->id);

                /*Todo: implementar ações por jobs

                if ($status == PaymentStatus::APPROVED) {
                    $this->raffleService->confirmByQuotation($payment->paymentOrder->quotation->id);
                } elseif ($status == PaymentStatus::REFUSED || $status == PaymentStatus::CANCELLED) {
                    $this->raffleService->releaseByQuotation($payment->paymentOrder->quotation->id);
                }*/
            }
        }

        return [
            'success' => true
        ];
    }

    private function getActiveWrapper($active = PaymentGateway::MERCADO_PAGO): ?PaymentServiceInterface
    {
        return match ($active) {
            PaymentGateway::MERCADO_PAGO => $this->mercadoPagoService,
            default => null,
        };
    }
}
