<?php

namespace App\Repositories\Stock;

interface StockInterface
{

    public function all($request);

    public function find($id);

    public function logs($id);

    public function create($request);

    public function update($request, $id);

    public function delete($id);

}