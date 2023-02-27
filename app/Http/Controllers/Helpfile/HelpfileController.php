<?php

namespace App\Http\Controllers\Helpfile;

use App\Http\Controllers\Controller;
use App\Http\Requests\Helpfile\StoreHelpfileRequest;
use App\Http\Requests\Helpfile\UpdateHelpfileRequest;
use App\Http\Resources\Helpfile\HelpfileResource;
use App\Repositories\Helpfile\HelpfileRepositoryInterface;
use Illuminate\Http\Request;
use Mockery\Exception;

class HelpfileController extends Controller
{

    protected $repository;
    protected $resource = HelpfileResource::class;

    public function __construct(HelpfileRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function all(Request $request)
    {
        if (count($_GET) == 0) {
            $models = cacheGet('Helpfiles');

            if (!$models) {
                $models = $this->repository->getAllHelpfiles($request);
                cachePut('Helpfiles', $models);
            }
        } else {

            $models = $this->repository->getAllHelpfiles($request);
        }

        return responseJson(200, 'success', HelpfileResource::collection($models['data']), $models['paginate'] ? getPaginates($models['data']) : null);

    }

    public function find($id)
    {
        try {
            $model = cacheGet('Helpfiles_' . $id);

            if (!$model) {
                $model = $this->repository->find($id);
                if (!$model) {
                    return responseJson(404, __('message.data not found'));
                } else {
                    cachePut('Helpfiles_' . $id, $model);
                }
            }
            return responseJson(200, __('Done'), new HelpfileResource($model));
        } catch (Exception $exception) {
            return responseJson($exception->getCode(), $exception->getMessage());
        }
    }

    public function store(StoreHelpfileRequest $request)
    {
        try {
            return responseJson(200, __('Done'), $this->repository->create($request->validated()));
        } catch (Exception $exception) {
            return responseJson($exception->getCode(), $exception->getMessage());
        }
    }

    public function update(UpdateHelpfileRequest $request, $id)
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
