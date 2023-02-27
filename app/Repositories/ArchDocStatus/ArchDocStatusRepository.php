<?php

namespace App\Repositories\ArchDocStatus;

use App\Models\ArchDocStatus;
use Illuminate\Support\Facades\DB;

class ArchDocStatusRepository implements ArchDocStatusInterface
{

    public function __construct(private ArchDocStatus $model)
    {
    }

    public function all($request)
    {
        $models = $this->model->orderBy($request->order ? $request->order : 'updated_at', $request->sort ? $request->sort : 'DESC');

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
            $this->model->create($request->all());
            cacheForget("archDocStatus");
        });
    }

    public function update($request, $id)
    {
        DB::transaction(function () use ($id, $request) {
            $this->model->where("id", $id)->update($request->all());
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
            "archDocStatus",
            "archDocStatus_" . $id,
        ];
        foreach ($keys as $key) {
            cacheForget($key);
        }
    }
}
