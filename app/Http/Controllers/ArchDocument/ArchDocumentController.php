<?php

namespace App\Http\Controllers\ArchDocument;

use App\Http\Controllers\Controller;
use App\Http\Requests\ArchDocument\ArchDocumentRequest;
use App\Http\Resources\ArchDocument\ArchDocumentResource;
use App\Repositories\ArchDocument\ArchDocumentInterface;
use Illuminate\Http\Request;

class ArchDocumentController extends Controller
{
    public function __construct(private ArchDocumentInterface $modelInterface) {

    }

    public function all(Request $request)
    {
        if (count($_GET) == 0) {
            $models = cacheGet('archDocument');
            if (!$models) {
                $models = $this->modelInterface->all($request);
                cachePut('archDocument', $models);
            }
        } else {
            $models = $this->modelInterface->all($request);
        }

        return responseJson(200, 'success', ArchDocumentResource::collection($models['data']), $models['paginate'] ? getPaginates($models['data']) : null);
    }

    public function create(ArchDocumentRequest $request)
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

    public function update(ArchDocumentRequest $request, $id)
    {
        $model = $this->modelInterface->find($id);
        if (!$model) {
            return responseJson(404, 'data not found');
        }
        $this->modelInterface->update($request, $id);
        return responseJson(200, 'success');
    }
}
