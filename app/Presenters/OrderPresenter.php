<?php

declare(strict_types=1);

namespace App\Presenters;

use App\Transformers\OrderTransformer;
use Prettus\Repository\Presenter\FractalPresenter;

/**
 * Class PaymentOrderPresenter.
 */
class OrderPresenter extends FractalPresenter
{
    /**
     * Transformer.
     *
     * @return OrderTransformer
     */
    public function getTransformer()
    {
        return new OrderTransformer();
    }
}
