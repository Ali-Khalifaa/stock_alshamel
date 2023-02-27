<?php

namespace App\Http\Controllers\ArchDepartment;

use App\Http\Controllers\Controller;
use App\Http\Requests\ArchDepartment\ArchDepartmentRequest;
use App\Http\Resources\ArchDepartment\ArchDepartmentResource;
use App\Repositories\ArchDepartment\ArchDepartmentInterface;
use Illuminate\Http\Request;

class ArchDepartmentController extends Controller
{
    public function __construct(private ArchDepartmentInterface $modelInterface) {

    }

    public function all(Request $request)
    {
        if (count($_GET) == 0) {
            $models = cacheGet('arcDocument');
            if (!$models) {
                $models = $this->modelInterface->all($request);
                cachePut('arcDocument', $models);
            }
        } else {
            $models = $this->modelInterface->all($request);
        }

        return responseJson(200, 'success', ArchDepartmentResource::collection($models['data']), $models['paginate'] ? getPaginates($models['data']) : null);
    }

    public function create(ArchDepartmentRequest $request)
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

    public function update(ArchDepartmentRequest $request, $id)
    {
        $model = $this->modelInterface->find($id);
        if (!$model) {
            return responseJson(404, 'data not found');
        }
        $this->modelInterface->update($request, $id);
        return responseJson(200, 'success');
    }
}
