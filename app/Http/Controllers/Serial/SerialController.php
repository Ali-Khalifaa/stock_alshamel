<?php

namespace App\Http\Controllers\Serial;

use App\Http\Controllers\ResponseController;
use App\Repositories\Serial\SerialRepositoryInterface;
use App\Http\Resources\Serial\SerialResource;
use Illuminate\Http\Request;
use App\Http\Requests\Serial\StoreSerialRequest;
use App\Http\Requests\Serial\UpdateSerialRequest;
use Mockery\Exception;

class SerialController extends ResponseController
{

    protected $repository;
    protected $resource = SerialResource::class;


    public function __construct(SerialRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }


    public function all(Request $request)
    {
        if (count($_GET) == 0) {
            $models = cacheGet('serials');
            if (!$models) {
                $models = $this->repository->getAllSerials($request);
                cachePut('serials', $models);
            }
        } else {
            $models = $this->repository->getAllSerials($request);
        }
        return $this->successResponse (($this->resource)::collection ($models['data']) ,__ ('Done'),200);
    }


    public function find($id)
    {
        try{
            $model = cacheGet('serials_' . $id);
            if (!$model) {
                $model = $this->repository->find($id);
                if (!$model) {
                    return $this->errorResponse( __('message.data not found'), 404);
                } else {
                    cachePut('serials_' . $id, $model);
                }
            }
            return $this->successResponse( new SerialResource($model),__ ('Done'),200);
        } catch (Exception $exception) {
            return $this->errorResponse($exception->getMessage(), $exception->getCode());
        }
    }


    public function store(StoreSerialRequest $request)
    {
        try {
            return $this->successResponse(new $this->resource($this->repository->create($request->validated())), __('created'), 200);
        } catch (Exception $exception) {
            return $this->errorResponse($exception->getMessage(), $exception->getCode());
        }
    }


    public function update(UpdateSerialRequest $request, $id)
    {
        try {
            $model = $this->repository->find($id);
            if (!$model) {
                return  $this->errorResponse( __('message.data not found'),404);
            }
            $model = $this->repository->update($request, $id);

            return  $this->successResponse(__('Done'),200);
        } catch (Exception $exception) {
            return $this->errorResponse($exception->getMessage(), $exception->getCode());
        }

    }


    public function delete($id)
    {
        try{
            $model = $this->repository->find($id);
            if (!$model) {
                return  $this->errorResponse( __('message.data not found'),404);
            }
            $this->repository->delete($id);
            return  $this->successResponse(__('Done'),200);

        } catch (Exception $exception) {
            return $this->errorResponse($exception->getMessage(), $exception->getCode());
        }
    }

    public function bulkDelete(Request $request){
        foreach ($request->ids as $id){
            $this->repository->delete($id);
        }
        return  responseJson(200, __('Done'));
    }
}
