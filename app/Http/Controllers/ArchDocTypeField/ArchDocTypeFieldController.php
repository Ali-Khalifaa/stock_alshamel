<?php

namespace App\Http\Controllers\ArchDocTypeField;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\ArchDocTypeField\ArchDocTypeFieldRequest;
use App\Http\Resources\ArchDocTypeField\ArchDocTypeFieldResource;
use App\Repositories\ArchDocTypeField\ArchDocTypeFieldInterface;

class ArchDocTypeFieldController extends Controller
{
    public function __construct(private ArchDocTypeFieldInterface $modelInterface) {

    }

    public function all(Request $request)
    {
        if (count($_GET) == 0) {
            $models = cacheGet('archDocTypeField');
            if (!$models) {
                $models = $this->modelInterface->all($request);
                cachePut('archDocTypeField', $models);
            }
        } else {
            $models = $this->modelInterface->all($request);
        }

        return responseJson(200, 'success', ArchDocTypeFieldResource::collection($models['data']), $models['paginate'] ? getPaginates($models['data']) : null);
    }

    public function create(ArchDocTypeFieldRequest $request)
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

    public function update(ArchDocTypeFieldRequest $request, $id)
    {
        $model = $this->modelInterface->find($id);
        if (!$model) {
            return responseJson(404, 'data not found');
        }
        $this->modelInterface->update($request, $id);
        return responseJson(200, 'success');
    }
}
