<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Criterias\AppRequestCriteria;
use Prettus\Repository\Eloquent\BaseRepository;
use Prettus\Repository\Exceptions\RepositoryException;

/**
 * Class BankAccountRepositoryEloquent.
 */
abstract class AppRepository extends BaseRepository
{
    /**
     * @var array
     */
    protected array $fieldsRules = [];

    /**
     * Get Fields Types.
     *
     * @return array
     */
    public function getFieldsRules(): array
    {
        return $this->fieldsRules;
    }

    /**
     * Boot up the repository, pushing criteria.
     *
     * @throws RepositoryException
     */
    public function boot(): void
    {
        $this->pushCriteria(app(AppRequestCriteria::class));
    }
}
