<?php

namespace App\Http\Controllers\Hotfield;

use App\Http\Controllers\Controller;
use App\Http\Requests\Hotfield\StoreHotfieldRequest;
use App\Http\Requests\Hotfield\UpdateHotfieldRequest;
use App\Http\Resources\Hotfield\HotfieldResource;
use App\Http\Resources\ScreenSetting\ScreenSettingResource;
use App\Repositories\Hotfield\HotfieldRepositoryInterface;
use Illuminate\Http\Request;
use Mockery\Exception;

class HotfieldController extends Controller
{

    protected $repository;
    protected $resource = HotfieldResource::class;

    public function __construct(HotfieldRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function all(Request $request)
    {
        if (count($_GET) == 0) {
            $models = cacheGet('Hotfields');

            if (!$models) {
                $models = $this->repository->getAllHotfields($request);
                cachePut('Hotfields', $models);
            }
        } else {

            $models = $this->repository->getAllHotfields($request);
        }

        return responseJson(200, 'success', HotfieldResource::collection($models['data']), $models['paginate'] ? getPaginates($models['data']) : null);

    }

    public function find($id)
    {
        try {
            $model = cacheGet('Hotfields_' . $id);

            if (!$model) {
                $model = $this->repository->find($id);
                if (!$model) {
                    return responseJson(404, __('message.data not found'));
                } else {
                    cachePut('Hotfields_' . $id, $model);
                }
            }
            return responseJson(200, __('Done'), new HotfieldResource($model));
        } catch (Exception $exception) {
            return responseJson($exception->getCode(), $exception->getMessage());
        }
    }

    public function store(StoreHotfieldRequest $request)
    {
        try {
            return responseJson(200, __('Done'), $this->repository->create($request->validated()));
        } catch (Exception $exception) {
            return responseJson($exception->getCode(), $exception->getMessage());
        }
    }

    public function update(UpdateHotfieldRequest $request, $id)
    {
        try {
            $model = $this->repository->find($id);
            if (!$model) {
                return responseJson(404, __('message.data not found'));
            }
            $model = $this->repository->update($request->validated(), $id);

            return responseJson(200, __('Done'));
        } catch (Exception $exception) {
            return responseJson($exception->getCode(), $exception->getMessage());
        }

    }

    public function delete($id)
    {
        try {
            $model = $this->repository->find($id);
            if (!$model) {
                return responseJson(404, __('message.data not found'));
            }
            $this->repository->delete($id);
            return responseJson(200, __('Done'));

        } catch (Exception $exception) {
            return responseJson($exception->getCode(), $exception->getMessage());
        }
    }

    public function bulkDelete(Request $request)
    {
        foreach ($request->ids as $id) {
            $this->repository->delete($id);
        }
        return responseJson(200, __('Done'));
    }

    public function logs($id)
    {
        $model = $this->repository->find($id);
        if (!$model) {
            return responseJson(404, __('message.data not found'));
        }

        $logs = $this->repository->logs($id);
        return responseJson(200, 'success', \App\Http\Resources\Log\LogResource::collection($logs));

    }

    public function screenSetting(Request $request)
    {
        try {
            return responseJson(200, __('Done'), $this->repository->setting($request->all()));
        } catch (Exception $exception) {
            return responseJson($exception->getCode(), $exception->getMessage());
        }
    }

    public function getScreenSetting($user_id, $screen_id)
    {
        try {
            $screenSetting = $this->repository->getSetting($user_id, $screen_id);
            if (!$screenSetting) {
                return responseJson(404, __('message.data not found'));
            }
            return responseJson(200, __('Done'), new ScreenSettingResource($screenSetting));
        } catch (Exception $exception) {
            return responseJson($exception->getCode(), $exception->getMessage());
        }
    }

}
