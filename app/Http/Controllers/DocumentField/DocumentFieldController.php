<?php

namespace App\Http\Controllers\DocumentField;

use App\Http\Controllers\Controller;
use App\Http\Requests\DocumentField\DocumentFieldRequest;
use App\Http\Resources\DocumentField\DocumentFieldResource;
use App\Repositories\DocumentField\DocumentFieldInterface;
use Illuminate\Http\Request;

class DocumentFieldController extends Controller
{
    public function __construct(private DocumentFieldInterface $modelInterface) {

    }

    public function all(Request $request)
    {
        if (count($_GET) == 0) {
            $models = cacheGet('DocumentField');
            if (!$models) {
                $models = $this->modelInterface->all($request);
                cachePut('DocumentField', $models);
            }
        } else {
            $models = $this->modelInterface->all($request);
        }

        return responseJson(200, 'success', DocumentFieldResource::collection($models['data']), $models['paginate'] ? getPaginates($models['data']) : null);
    }

    public function create(DocumentFieldRequest $request)
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

    public function update(DocumentFieldRequest $request, $id)
    {
        $model = $this->modelInterface->find($id);
        if (!$model) {
            return responseJson(404, 'data not found');
        }
        $this->modelInterface->update($request, $id);
        return responseJson(200, 'success');
    }
}
