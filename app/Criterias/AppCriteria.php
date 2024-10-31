<?php

declare(strict_types=1);

namespace App\Criterias;

use Illuminate\Http\Request;
use Prettus\Repository\Contracts\CriteriaInterface;

/**
 * Class AppCriteria.
 */
abstract class AppCriteria implements CriteriaInterface
{
    /** @var Request */
    protected $request;

    /**
     * AppCriteria constructor.
     *
     * @param Request $request
     */
    public function __construct(Request $request)
    {
        $this->request = $request;
    }
}
