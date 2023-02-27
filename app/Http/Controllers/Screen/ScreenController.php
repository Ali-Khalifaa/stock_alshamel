<?php

namespace App\Http\Controllers\Screen;

use App\Exceptions\NotFoundException;
use App\Http\Controllers\ResponseController;
use App\Http\Requests\Screen\AddScreenToDocumentTypeRequest;
use App\Http\Requests\Screen\StoreScreenRequest;
use App\Http\Requests\Screen\UpdateScreenRequest;
use App\Http\Resources\Screen\ScreenResource;
use App\Repositories\Screen\ScreenRepositoryInterface;
use Illuminate\Http\Request;
use Mockery\Exception;

class ScreenController extends ResponseController
{

    protected $repository;
    protected $resource = ScreenResource::class;

    public function __construct(ScreenRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function all(Request $request)
    {
        if (count($_GET) == 0) {
            $models = cacheGet('Screens');

            if (!$models) {
                $models = $this->repository->getAllScreens($request);

                cachePut('Screens', $models);
            }
        } else {

            $models = $this->repository->getAllScreens($request);
        }
        return responseJson(200, 'success', ($this->resource)::collection($models['data']), $models['paginate'] ? getPaginates($models['data']) : null);
    }
    public function getScreenDocumentTypes($screen_id){
        return $this->repository->getScreenDocumentTypes($screen_id);
    }
    public function getScreenButtons($screen_id){
        return $this->repository->getScreenButtons($screen_id);
    }

    public function find($id)
    {

        try {
            $model = cacheGet('Screens_' . $id);

            if (!$model) {
                $model = $this->repository->find($id);
                if (!$model) {
                    return responseJson(404, __('message.data not found'));
                } else {
                    cachePut('Screens_' . $id, $model);
                }
            }
            return responseJson(200, __('Done'), new ScreenResource($model), );
        } catch (Exception $exception) {
            return responseJson($exception->getCode(), $exception->getMessage());
        }
    }

    public function store(StoreScreenRequest $request)
    {
        try {
            return responseJson(200, __('Done'), $this->repository->create($request->validated()));
        } catch (Exception $exception) {
            return responseJson($exception->getCode(), $exception->getMessage());
        }
    }

    public function update(UpdateScreenRequest $request, $id)
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

    public function bulkDelete(Request $request){
        foreach ($request->ids as $id){
            $this->repository->delete($id);
        }
        return  responseJson(200, __('Done'));
    }

    public function addScreenToDocumentType(AddScreenToDocumentTypeRequest $request)
    {
        try {
            if($this->repository->screenDocumentExist($request->screen_id,$request->documentType_id)){
                return response()->json(["error"=>"Screen document exist"],422);
            }
            $this->repository->addScreenToDocumentType($request);
            return responseJson(200, 'success');
        } catch (\Exception$ex) {
            return responseJson(422, $ex->getMessage() ? $ex->getMessage() : throw new NotFoundException());
        }

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

    public function removeScreenFromDocumentType($screen_id, $documentType_id)
    {
        try {
            $this->repository->removeScreenFromDocumentType($screen_id, $documentType_id);
            return responseJson(200, 'deleted successfully');
        } catch (\Exception$ex) {
            return responseJson(422, $ex->getMessage() ? $ex->getMessage() : throw new NotFoundException());
        }

    }

}
