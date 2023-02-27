<?php

namespace App\Http\Controllers\Store;

use App\Http\Controllers\ResponseController;
use App\Http\Requests\Store\StoreStoreRequest;
use App\Http\Requests\Store\UpdateStoreRequest;
use App\Http\Resources\Log\LogResource;
use App\Http\Resources\Store\StoreResource;
use App\Repositories\Store\StoreRepositoryInterface;
use Illuminate\Http\Request;
use Mockery\Exception;

class StoreController extends ResponseController
{
    public $repository;
    public $resource = StoreResource::class;
    public function __construct(StoreRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Display a listing of the resource.
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        if (count($_GET) == 0) {
            $models = cacheGet('store');

            if (!$models) {
                $models = $this->repository->getAllStores($request);

                cachePut('store', $models);
            }
        } else {

            $models = $this->repository->getAllStores($request);
        }

        return responseJson(200, 'success', StoreResource::collection($models['data']), $models['paginate'] ? getPaginates($models['data']) : null);
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(StoreStoreRequest $request)
    {
        try {
            // return responseJson(200 , __('created'),  new CompanyResource($this->repository->create($request->validated())));
            return responseJson(200, __('created'), $this->repository->create($request->validated()));
        } catch (Exception $exception) {
            return responseJson($exception->getCode(), $exception->getMessage());
        }
    }

    /**
     * Show the specified resource.
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        try {
            $model = cacheGet('store_' . $id);
            if (!$model) {
                $model = $this->repository->show($id);
                if (!$model) {
                    return responseJson(404, __('message.data not found'));
                } else {
                    cachePut('store_' . $id, $model);
                }
            }
            return responseJson(200, __('Done'), new StoreResource($model));
        } catch (Exception $exception) {
            return responseJson($exception->getCode(), $exception->getMessage());
        }
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(UpdateStoreRequest $request, $id)
    {
        try {
            $model = $this->repository->show($id);
            if (!$model) {
                return responseJson(404, __('message.data not found'));
            }
            $model = $this->repository->update($request->validated(), $id);

            return responseJson(200, __('Done'));
        } catch (Exception $exception) {
            return responseJson($exception->getCode(), $exception->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        try {
            $model = $this->repository->show($id);
            if (!$model) {
                return responseJson(404, __('message.data not found'));
            }
            $this->repository->destroy($id);
            return responseJson(200, __('Done'));

        } catch (Exception $exception) {
            return responseJson($exception->getCode(), $exception->getMessage());
        }
    }

    public function bulkDelete(Request $request)
    {
        foreach ($request->ids as $id) {
            $this->repository->destroy($id);
        }
        return responseJson(200, __('Done'));
    }

    public function logs($id)
    {
        $model = $this->repository->show($id);
        if (!$model) {
            return responseJson(404, __('message.data not found'));
        }
        $logs = $this->repository->logs($id);
        return responseJson(200, __('Done'), LogResource::collection($logs));

    }
}
