<?php

namespace App\Http\Controllers\ScreenHelpfile;

use App\Http\Controllers\Controller;
use App\Http\Requests\ScreenHelpfile\StoreScreenHelpfileRequest;
use App\Http\Requests\ScreenHelpfile\UpdateScreenHelpfileRequest;
use App\Http\Resources\ScreenHelpfile\ScreenHelpfileResource;
use App\Repositories\ScreenHelpfile\ScreenHelpfileRepositoryInterface;
use Illuminate\Http\Request;
use Mockery\Exception;

class ScreenHelpfileController extends Controller
{

    protected $repository;
    protected $resource = ScreenHelpfileResource::class;

    public function __construct(ScreenHelpfileRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function all(Request $request)
    {
        if (count($_GET) == 0) {
            $models = cacheGet('ScreenHelpfiles');

            if (!$models) {
                $models = $this->repository->getAllScreenHelpfiles($request);
                cachePut('ScreenHelpfiles', $models);
            }
        } else {

            $models = $this->repository->getAllScreenHelpfiles($request);
        }

        return responseJson(200, 'success', ScreenHelpfileResource::collection($models['data']), $models['paginate'] ? getPaginates($models['data']) : null);

    }

    public function find($id)
    {
        try {
            $model = cacheGet('ScreenHelpfiles_' . $id);

            if (!$model) {
                $model = $this->repository->find($id);
                if (!$model) {
                    return responseJson(404, __('message.data not found'));
                } else {
                    cachePut('ScreenHelpfiles_' . $id, $model);
                }
            }
            return responseJson(200, __('Done'), new ScreenHelpfileResource($model));
        } catch (Exception $exception) {
            return responseJson($exception->getCode(), $exception->getMessage());
        }
    }

    public function store(StoreScreenHelpfileRequest $request)
    {
        try {
            return responseJson(200, __('Done'), $this->repository->create($request->validated()));
        } catch (Exception $exception) {
            return responseJson($exception->getCode(), $exception->getMessage());
        }
    }

    public function update(UpdateScreenHelpfileRequest $request, $id)
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
        return responseJson(200, __('Done'), \App\Http\Resources\Log\LogResource::collection($logs));

    }
}
