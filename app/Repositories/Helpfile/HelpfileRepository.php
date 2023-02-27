<?php

namespace App\Repositories\Helpfile;

use App\Models\Helpfile;
use Illuminate\Support\Facades\DB;

class HelpfileRepository implements HelpfileRepositoryInterface
{

    private $model;
    public function __construct(Helpfile $model)
    {
        $this->model = $model;
    }

    public function getAllHelpfiles($request)
    {
        $models = $this->model->where(function ($q) use ($request) {

            if ($request->search) {
                $q->where('name', 'like', '%' . $request->search . '%')
                    ->orWhere('name_e', 'like', '%' . $request->search . '%');
            }
        })->latest();

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
            cacheForget("Helpfiles");
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
            "Helpfiles",
            "Helpfiles_" . $id,
        ];
        foreach ($keys as $key) {
            cacheForget($key);
        }

    }
}
