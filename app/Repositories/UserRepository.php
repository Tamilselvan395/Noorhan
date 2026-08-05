<?php

namespace App\Repositories;

use App\Models\User;

class UserRepository extends BaseRepository
{
    protected function model(): string
    {
        return User::class;
    }

    public function findByEmail(string $email): ?User
    {
        return $this->model->query()->where('email', strtolower($email))->first();
    }
}