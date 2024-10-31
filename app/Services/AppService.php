<?php

declare(strict_types=1);

namespace App\Services;

/**
 * Class AppService.
 */
class AppService
{
    /**
     * @var
     */
    protected $repository;

    /**
     * @param int $limit
     *
     * @return mixed
     */
    public function all(int $limit = 20): mixed
    {
        return $this->repository->paginate($limit);
    }

    /**
     * @param array $data
     * @param bool  $skipPresenter
     *
     * @return mixed
     */
    public function create(array $data, bool $skipPresenter = false): mixed
    {
        if (!empty($data['password'])) {
            $data['password'] = bcrypt($data['password']);
        }
        return $skipPresenter ? $this->repository->skipPresenter()->create($data) : $this->repository->create($data);
    }

    /**
     * @param      $id
     * @param bool $skip_presenter
     *
     * @return mixed
     */
    public function find($id, bool $skip_presenter = false): mixed
    {
        if ($skip_presenter) {
            return $this->repository->skipPresenter()->find($id);
        }
        return $this->repository->find($id);
    }

    /**
     * @param array $data
     * @param       $id
     * @param bool  $skipPresenter
     *
     * @return array|mixed
     */
    public function update(array $data, $id, bool $skipPresenter = false): mixed
    {
        if (!empty($data['password'])) {
            $data['password'] = bcrypt($data['password']);
        }
        return $skipPresenter ? $this->repository->skipPresenter()->update($data, $id) : $this->repository->update(
            $data,
            $id
        );
    }

    /**
     * @param array $data
     * @param bool  $first
     * @param bool  $presenter
     *
     * @return mixed
     */
    public function findWhere(array $data, bool $first = false, bool $presenter = false): mixed
    {
        if ($first) {
            return $this->repository->skipPresenter()->findWhere($data)->first();
        }
        if ($presenter) {
            return $this->repository->findWhere($data);
        }
        return $this->repository->skipPresenter()->findWhere($data);
    }

    /**
     * @param array $data
     *
     * @return mixed
     */
    public function findLast(array $data): mixed
    {
        return $this->repository->skipPresenter()->findWhere($data)->last();
    }

    /**
     * Remove the specified resource from storage using softDelete.
     * =.
     *
     * @param $id
     *
     * @return array
     */
    public function delete($id): array
    {
        return ['success' => (bool) $this->repository->delete($id)];
    }
}
