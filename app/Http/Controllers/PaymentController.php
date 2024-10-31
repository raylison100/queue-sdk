<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\PaymentServices;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Class RafflesController.
 */
class PaymentController extends Controller
{
    /**
     * @var PaymentServices
     */
    protected $service;

    /**
     * RafflesController constructor.
     *
     * @param PaymentServices $service
     */
    public function __construct(PaymentServices $service)
    {
        $this->service = $service;
    }

    public function mercadoPagoNotification(Request $request): JsonResponse
    {
        try {
            return response()->json($this->service->mercadoPagoNotification($request->all()));
        } catch (\Exception $exception) {
            return $this->sendBadResponse($exception);
        }
    }
}
