<?php

namespace App\Http\Controllers\ScreenButton;

use App\Http\Controllers\Controller;
use App\Repositories\ScreenButton\ScreenButtonRepositoryInterface;
use App\Http\Resources\ScreenButton\ScreenButtonResource;
use App\Http\Requests\ScreenButton\StoreScreenButtonRequest;
use App\Http\Requests\ScreenButton\UpdateScreenButtonRequest;
use Illuminate\Http\Request;

class ScreenButtonController extends Controller
{

    protected $repository;
    protected $resource = ScreenButtonResource::class;


    public function __construct(ScreenButtonRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function all(Request $request)
    {
        if (count($_GET) == 0) {
            $models = cacheGet('ScreenButtons');

            if (!$models) {
                $models = $this->repository->getAllScreenButtons($request);
                cachePut('ScreenButtons', $models);
            }
        } else {

            $models = $this->repository->getAllScreenButtons($request);
        }

        return responseJson(200, 'success', ScreenButtonResource::collection($models['data']), $models['paginate'] ? getPaginates($models['data']) : null);
    }

    public function allScreens()
    {
        return $this->repository->getScreens();
    }


    public function find($id)
    {

        $model = cacheGet('ScreenButtons_' . $id);

        if (!$model) {
            $model = $this->repository->find($id);
            if (!$model) {
                return responseJson(404, __('message.data not found'));
            } else {
                cachePut('ScreenButtons_' . $id, $model);
            }
        }
        return responseJson(200, __('Done'), new ScreenButtonResource($model));
    }


    public function store(StoreScreenButtonRequest $request)
    {
        if ($this->repository->screenButtonExist($request->screen_id, $request->button_id)) {
            return response()->json(["error" => "screen button exist"], 422);
        }
        return responseJson(200, __('Done'), $this->repository->create($request));
    }

    public function removeScreenFromButton($screen_id, $button_id)
    {
        $this->repository->removeScreenFromButton($screen_id, $button_id);
    }

    public function update(UpdateScreenButtonRequest $request, $id)
    {

        $model = $this->repository->find($id);
        if (!$model) {
            return responseJson(404, __('message.data not found'));
        }
        $model = $this->repository->update($request->validated(), $id);

        return  responseJson(200, __('Done'));
    }


    public function logs($id)
    {
        $model = $this->repository->find($id);
        if (!$model) {
            return responseJson(404, __('message.data not found'));
        }
        return responseJson(200, __('Done'), $model->logs);
    }


    public function delete($id)
    {

        $model = $this->repository->find($id);
        if (!$model) {
            return responseJson(404, __('message.data not found'));
        }
        $this->repository->delete($id);
        return  responseJson(200, __('Done'));
    }

    public function bulkDelete(Request $request)
    {
        foreach ($request->ids as $id) {
            $this->repository->delete($id);
        }
        return  responseJson(200, __('Done'));
    }
}
