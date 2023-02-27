<?php

namespace App\Repositories\Helpfile;

interface HelpfileRepositoryInterface
{

    public function getAllHelpfiles($request);

    public function find($id);

    public function create($request);

    public function update($request, $id);

    public function delete($id);

    public function logs($id);


}
