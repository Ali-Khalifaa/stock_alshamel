<?php

namespace App\Repositories\Store;

interface StoreRepositoryInterface
{

    public function getAllStores($request);

    public function create($request);

    public function show($id);

    public function update($data, $id);

    public function destroy($id);
    

    public function logs($id);
}
