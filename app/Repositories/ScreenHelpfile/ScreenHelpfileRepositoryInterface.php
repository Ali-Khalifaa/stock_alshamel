<?php

namespace App\Repositories\ScreenHelpfile;

interface ScreenHelpfileRepositoryInterface
{

    public function getAllScreenHelpfiles($request);

    public function find($id);

    public function create($request);

    public function update($request, $id);

    public function logs($id);

    public function delete($id);


}
