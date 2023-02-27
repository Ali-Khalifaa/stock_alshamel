<?php

namespace App\Repositories\Screen;

use App\Exceptions\NotFoundException;
use App\Models\Screen;
use App\Models\ScreenDocumentType;
use Illuminate\Support\Facades\DB;

class ScreenRepository implements ScreenRepositoryInterface
{

    public function __construct(private Screen $model)
    {
    }

    public function getAllScreens($request)
    {
        $models = $this->model->where(function ($q) use ($request) {
            if ($request->search && $request->columns) {
                foreach ($request->columns as $column) {
                    $q->orWhere($column, 'like', '%' . $request->search . '%');
                }
            }
            if ($request->search) {
                $q->where('name', 'like', '%' . $request->search . '%')
                    ->orWhere('name_e', 'like', '%' . $request->search . '%')
                    ->orWhere('title', 'like', '%' . $request->search . '%')
                    ->orWhere('title_e', 'like', '%' . $request->search . '%');
            }

            if ($request->serial_id) {
                $q->where('serial_id', $request->serial_id);
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
        return DB::transaction(function () use ($request) {

            cacheForget("Screens");
            return $this->model->create($request);
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

    public function addScreenToDocumentType($request)
    {
        $screen = $this->model->find($request->screen_id);
        if (!$screen) {
            throw new NotFoundException();
        }
        $screen->documentTypes()->attach($request->documentType_id);
    }

    public function removeScreenFromDocumentType($screen_id, $documentType_id)
    {
        $screen = $this->model->find($screen_id);
        if (!$screen) {
            throw new NotFoundException();
        }
        $screen->documentTypes()->detach($documentType_id);
    }
    public function getScreenButtons($screen_id)
    {
        $screen = $this->model->find($screen_id);
        return $screen->buttons;
    }
    public function screenDocumentExist($screen_id, $documentType_id)
    {
        return ScreenDocumentType::where("screen_id", $screen_id)->where("document_type_id", $documentType_id)
            ->count() > 0;
    }
    public function getScreenDocumentTypes($screen_id)
    {
        $screen = $this->model->find($screen_id);
        return $screen->documentTypes;
    }

    public function logs($id)
    {
        return $this->model->find($id)->activities()->orderBy('created_at', 'DESC')->get();
    }

    private function forget($id)
    {
        $keys = [
            "Screens",
            "Screens_" . $id,
        ];
        foreach ($keys as $key) {
            cacheForget($key);
        }
    }
}
