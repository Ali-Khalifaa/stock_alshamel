<?php

namespace App\Repositories\ScreenHelpfile;

use App\Models\ScreenHelpfile;
use Illuminate\Support\Facades\DB;

class ScreenHelpfileRepository implements ScreenHelpfileRepositoryInterface
{

    private $model;
    public function __construct(ScreenHelpfile $model)
    {
        $this->model = $model;
    }

    public function getAllScreenHelpfiles($request)
    {
        $models = $this->model;

        if ($request->per_page) {
            return ['data' => $models->paginate($request->per_page), 'paginate' => true];
        } else {
            return ['data' => $models->get(), 'paginate' => false];
        }
    }

    public function find($id)
    {
        return $this->model->find($id);
    }

    public function create($request)
    {
        DB::transaction(function () use ($request) {

            $this->model->create($request);
            cacheForget("ScreenHelpfiles");
        });
    }

    public function update($request, $id)
    {
        DB::transaction(function () use ($id, $request) {
            $this->model->where("id", $id)->update($request);
            $this->forget($id);

        });

    }

    public function logs($id)
    {
        return $this->model->find($id)->activities()->orderBy('created_at', 'DESC')->get();
    }

    public function delete($id)
    {
        $model = $this->find($id);
        $this->forget($id);
        $model->delete();
    }

    private function forget($id)
    {
        $keys = [
            "ScreenHelpfiles",
            "ScreenHelpfiles_" . $id,
        ];
        foreach ($keys as $key) {
            cacheForget($key);
        }

    }
}
