<?php

namespace App\Http\Controllers\ArchDocumentDtl;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\ArchDocumentDtl\ArchDocumentDtlRequest;
use App\Http\Resources\ArchDocumentDtl\ArchDocumentDtlResource;
use App\Repositories\ArchDocumentDtl\ArchDocumentDtlInterface;

class ArchDocumentDtlController extends Controller
{
    public function __construct(private ArchDocumentDtlInterface $modelInterface) {

    }

    public function all(Request $request)
    {
        if (count($_GET) == 0) {
            $models = cacheGet('archDocumentDtl');
            if (!$models) {
                $models = $this->modelInterface->all($request);
                cachePut('archDocumentDtl', $models);
            }
        } else {
            $models = $this->modelInterface->all($request);
        }

        return responseJson(200, 'success', ArchDocumentDtlResource::collection($models['data']), $models['paginate'] ? getPaginates($models['data']) : null);
    }

    public function create(ArchDocumentDtlRequest $request)
    {
        $this->modelInterface->create($request);
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
            $this->modelInterface->delete($id);
        }
        return responseJson(200, __('Done'));
    }

    public function update(ArchDocumentDtlRequest $request, $id)
    {
        $model = $this->modelInterface->find($id);
        if (!$model) {
            return responseJson(404, 'data not found');
        }
        $this->modelInterface->update($request, $id);
        return responseJson(200, 'success');
    }

}
