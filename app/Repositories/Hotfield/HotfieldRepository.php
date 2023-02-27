<?php

namespace App\Repositories\Hotfield;

use App\Models\Hotfield;
use App\Models\UserSettingScreen;
use Illuminate\Support\Facades\DB;

class HotfieldRepository implements HotfieldRepositoryInterface
{

    private $model;
    public function __construct(Hotfield $model)
    {
        $this->model = $model;
    }

    public function getAllHotfields($request)
    {
        $models = $this->model->where(function ($q) use ($request) {

            if ($request->search) {
                $q->where('table_name', 'like', '%' . $request->search . '%')
                    ->orWhere('field_name', 'like', '%' . $request->search . '%')
                    ->orWhere('field_title', 'like', '%' . $request->search . '%')
                    ->orWhere('field_title_en', 'like', '%' . $request->search . '%');
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
            cacheForget("Hotfields");
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

    public function setting($request)
    {
        DB::transaction(function () use ($request) {
            $screenSetting = UserSettingScreen::where('user_id', $request['user_id'])->where('screen_id', $request['screen_id'])->first();
            $request['data_json'] = json_encode($request['data_json']);
            if (!$screenSetting) {
                UserSettingScreen::create($request);
            } else {
                $screenSetting->update($request);
            }
        });
        return $this->successResponse([], __('Done'));
    }

    public function getSetting($user_id, $screen_id)
    {
        return UserSettingScreen::where('user_id', $user_id)->where('screen_id', $screen_id)->first();
    }

    public function logs($id)
    {
        return $this->model->find($id)->activities()->orderBy('created_at', 'DESC')->get();
    }

    private function forget($id)
    {
        $keys = [
            "Hotfields",
            "Hotfields_" . $id,
        ];
        foreach ($keys as $key) {
            cacheForget($key);
        }

    }
}
