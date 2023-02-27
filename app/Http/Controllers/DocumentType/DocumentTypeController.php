<?php

namespace App\Http\Controllers\DocumentType;

use App\Http\Requests\DocumentTypeRequest\DocumentTypeRequest;
use App\Http\Resources\DocumentType\DocumentTypeResource;
use App\Repositories\DocumentType\DocumentTypeInterface;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class DocumentTypeController extends Controller
{
    public function __construct(private DocumentTypeInterface $modelInterface)
    {}

    public function find($id)
    {
        $model = cacheGet('DocumentType_' . $id);
        if (!$model) {
            $model = $this->modelInterface->find($id);
            if (!$model) {
                return responseJson(404, 'data not found');
            } else {
                cachePut('DocumentType_' . $id, $model);
            }
        }
        return responseJson(200, 'success', new DocumentTypeResource($model));
    }

    public function all(Request $request)
    {
        if (count($_GET) == 0) {
            $models = cacheGet('DocumentType');
            if (!$models) {
                $models = $this->modelInterface->all($request);
                cachePut('DocumentType', $models);
            }
        } else {
            $models = $this->modelInterface->all($request);
        }

        return responseJson(200, 'success', DocumentTypeResource::collection($models['data']), $models['paginate'] ? getPaginates($models['data']) : null);
    }

    public function create(DocumentTypeRequest $request)
    {
        $this->modelInterface->create($request);
        return responseJson(200, 'success');
    }

    public function update(DocumentTypeRequest $request, $id)
    {
        $model = $this->modelInterface->find($id);
        if (!$model) {
            return responseJson(404, 'data not found');
        }
        $this->modelInterface->update($request, $id);
        return responseJson(200, 'success');
    }

    public function delete($id)
    {
        $model = $this->modelInterface->find($id);
        if (!$model) {
            return responseJson(404, 'data not found');
        }
        $this->modelInterface->delete($id);

        return responseJson(200, 'success');
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
        $model = $this->modelInterface->find($id);
        if (!$model) {
            return responseJson(404, __('message.data not found'));
        }

        $logs = $this->modelInterface->logs($id);
        return responseJson(200, 'success', \App\Http\Resources\Log\LogResource::collection($logs));

    }

}
