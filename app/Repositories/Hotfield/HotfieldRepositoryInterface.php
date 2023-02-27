<?php

namespace App\Repositories\Hotfield;

interface HotfieldRepositoryInterface
{

    public function getAllHotfields($request);

    public function find($id);

    public function create($request);

    public function update($request, $id);

    public function logs($id);

    public function delete($id);


}
