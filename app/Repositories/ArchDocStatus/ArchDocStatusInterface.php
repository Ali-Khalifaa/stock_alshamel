<?php

namespace App\Repositories\ArchDocStatus;

interface ArchDocStatusInterface
{

    public function all($request);

    public function find($id);

    public function logs($id);

    public function create($request);

    public function update($request, $id);

    public function delete($id);

}
