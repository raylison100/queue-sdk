<?php

declare(strict_types=1);

namespace App\Transformers;

use App\Entities\Order;
use League\Fractal\TransformerAbstract;

/**
 * Class OrderTransformer.
 */
class OrderTransformer extends TransformerAbstract
{
    /**
     * Transform the Order entity.
     *
     * @param Order $model
     *
     * @return array
     */
    public function transform(Order $model): array
    {
        return [
            'id' => (int) $model->id,
            'amount' => $model->amount,
            'payment_id' => $model->payment_id,
            'created_at' => $model->created_at,
            'updated_at' => $model->updated_at
        ];
    }
}
