<?php

namespace App\Repositories\User;

use App\Models\User;

class UserRepository implements UserRepositoryInterface
{
    public function __construct(User $model)
    {}

    public function getAllUsers()
    {
        return $this->model->get();
    }
}
