<?php

namespace App\Repositories\ScreenButton;

use App\Models\Screen;
use App\Models\ScreenButton;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class ScreenButtonRepository implements ScreenButtonRepositoryInterface
{

    private $model;
    public function __construct(ScreenButton $model)
    {
        $this->model = $model;
    }

    public function getAllScreenButtons($request)
    {
        $models = $this->model->filter($request);

        if ($request->per_page) {
            return ['data' => $models->paginate($request->per_page), 'paginate' => true];
        } else {
            return ['data' => $models->get(), 'paginate' => false];
        }
    }

    public function getScreens()
    {
        return $this->model->screens();
    }

    public function find($id)
    {
        return $this->model->find($id);
    }

    public function create($request)
    {
        DB::transaction(function () use ($request) {
            if ($request->buttons) {
                $btns = explode(',', $request->buttons);
                foreach ($btns as $btn) {
                    $this->model->create(
                        [
                            'screen_id' => $request->screen_id,
                            'button_id' => $btn
                        ]
                    );
                }
                cacheForget("ScreenButtons");
            } else {
                $this->model->create($request->input());
                cacheForget("ScreenButtons");
            }
        });
    }

    public function screenButtonExist($screen_id, $button_id)
    {
        return $this->model->where("screen_id",$screen_id)->where("button_id", $button_id)->count() > 0;
    }

    public function removeScreenFromButton($screen_id, $button_id)
    {
        $screen = Screen::find($screen_id);
        $screen->buttons()->detach($button_id);
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
            "ScreenButtons",
            "ScreenButtons_" . $id,
        ];
        foreach ($keys as $key) {
            cacheForget($key);
        }
    }
}
