<?php

namespace App\Repositories;

use App\Contracts\RepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

abstract class BaseRepository implements RepositoryInterface
{
    protected Model $model;

    public function __construct()
    {
        $this->makeModel();
    }

    abstract protected function model(): string;

    protected function makeModel(): Model
    {
        $model = app($this->model());
        if (!$model instanceof Model) {
            throw new \Exception("Class {$this->model()} must be an instance of Illuminate\\Database\\Eloquent\\Model");
        }
        return $this->model = $model;
    }

    public function all(array $columns = ['*']): Collection
    {
        return $this->model->all($columns);
    }

    public function find(int $id, array $columns = ['*']): ?Model
    {
        return $this->model->find($id, $columns);
    }

    public function findBy(string $field, mixed $value, array $columns = ['*']): ?Model
    {
        return $this->model->where($field, $value)->first($columns);
    }

    public function create(array $data): Model
    {
        return $this->model->create($data);
    }

    public function update(int $id, array $data): bool
    {
        $record = $this->find($id);
        return $record ? $record->update($data) : false;
    }

    public function delete(int $id): bool
    {
        return (bool) $this->model->destroy($id);
    }

    public function paginate(int $perPage = 15, array $columns = ['*'])
    {
        return $this->model->paginate($perPage, $columns);
    }
}