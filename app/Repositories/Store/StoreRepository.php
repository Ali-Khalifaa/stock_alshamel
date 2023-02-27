<?php

namespace App\Repositories\Store;

use App\Models\Store;
use Illuminate\Support\Facades\DB;

class StoreRepository implements StoreRepositoryInterface
{
    public $model;
    public function __construct(Store $model)
    {
        $this->model = $model;
    }

    public function getAllStores($request)
    {
        $models = $this->model->where(function ($q) use ($request) {

            if ($request->search) {
                $q->where('name', 'like', '%' . $request->search . '%');
                $q->orWhere('name_e', 'like', '%' . $request->search . '%');
            }

            if ($request->is_active) {
                $q->where('is_active', $request->is_active);
            }

        })->orderBy($request->order ? $request->order : 'updated_at', $request->sort ? $request->sort : 'DESC');

        if ($request->per_page) {
            return ['data' => $models->paginate($request->per_page), 'paginate' => true];
        } else {
            return ['data' => $models->get(), 'paginate' => false];
        }}

    public function create($request)
    {
        DB::transaction(function () use ($request) {
            $this->model->create($request);
            cacheForget("company");
        });
    }

    public function show($id)
    {
        return $this->model->find($id);
    }

    public function update($data, $id)
    {
        DB::transaction(function () use ($id, $data) {
            $this->model->where("id", $id)->update($data);
            $this->forget($id);
        });
    }

    public function destroy($id)
    {
        $model = $this->show($id);
        $this->forget($id);
        $model->delete();
    }
    public function logs($id)
    {
        return $this->model->find($id)->activities()->orderBy('created_at', 'DESC')->get();
    }

    private function forget($id)
    {
        $keys = [
            "store",
            "store_" . $id,
        ];
        foreach ($keys as $key) {
            cacheForget($key);
        }

    }

}
