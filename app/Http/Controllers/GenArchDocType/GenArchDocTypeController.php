<?php

namespace App\Http\Controllers\GenArchDocType;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\GenArchDocType\GenArchDocTypeRequest;
use App\Http\Resources\GenArchDocType\GenArchDocTypeResource;
use App\Repositories\GenArchDocType\GenArchDocTypeInterface;

class GenArchDocTypeController extends Controller
{
    public function __construct(private GenArchDocTypeInterface $modelInterface) {

    }

    public function all(Request $request)
    {
        if (count($_GET) == 0) {
            $models = cacheGet('genArchDoctype');
            if (!$models) {
                $models = $this->modelInterface->all($request);
                cachePut('genArchDoctype', $models);
            }
        } else {
            $models = $this->modelInterface->all($request);
        }

        return responseJson(200, 'success', GenArchDocTypeResource::collection($models['data']), $models['paginate'] ? getPaginates($models['data']) : null);
    }

    public function create(GenArchDocTypeRequest $request)
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

    public function update(GenArchDocTypeRequest $request, $id)
    {
        $model = $this->modelInterface->find($id);
        if (!$model) {
            return responseJson(404, 'data not found');
        }
        $this->modelInterface->update($request, $id);
        return responseJson(200, 'success');
    }
}
