<?php

namespace App\Repositories\Button;

interface ButtonRepositoryInterface
{

    public function getAllButtons($request);

    public function find($id);

    public function create($request);

    public function update($request, $id);

    public function logs($id);
    public function delete($id);

}
