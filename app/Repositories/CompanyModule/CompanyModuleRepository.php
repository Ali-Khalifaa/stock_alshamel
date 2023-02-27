<?php

namespace App\Repositories\CompanyModule;

use App\Models\CompanyModule;

use App\Traits\ApiResponser;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class CompanyModuleRepository implements CompanyModuleRepositoryInterface
{

    private $model;
    public function __construct(CompanyModule $model)
    {
        $this->model = $model;

    }

    public function getCompanyModules($request)
    {
        $models = $this->model->where(function ($q) use ($request) {
            $this->model->scopeFilter($q , $request);
        })->orderBy($request->order ? $request->order : 'updated_at', $request->sort ? $request->sort : 'DESC');;

        return ['data' => $models->paginate($request->per_page), 'paginate' => true];

    }

    public function find($id)
    {
        return $this->model->find($id);
    }

    public function create($request)
    {

        DB::transaction(function () use ($request) {

            $this->model->create($request);
            cacheForget("CompanyModules");
        });

    }

    public function update($request, $id)
    {
        DB::transaction(function () use ($id, $request) {

            $this->model->where("id", $id)->update($request);
            $this->forget($id);

        });

    }

    public function delete($id)
    {
        $model = $this->find($id);
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
            "CompanyModules",
            "CompanyModules_" . $id,
        ];
        foreach ($keys as $key) {
            cacheForget($key);
        }

    }
}
