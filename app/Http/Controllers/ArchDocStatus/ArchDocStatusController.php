<?php

namespace App\Http\Controllers\ArchDocStatus;

use App\Http\Controllers\Controller;
use App\Http\Resources\ArchDocStatus\ArchDocStatusResource;
use App\Http\Requests\ArchDocStatus\ArchDocStatusRequest;
use App\Repositories\ArchDocStatus\ArchDocStatusInterface;
use Illuminate\Http\Request;

class ArchDocStatusController extends Controller
{
    public function __construct(private ArchDocStatusInterface $modelInterface) {

    }

    public function all(Request $request)
    {
        if (count($_GET) == 0) {
            $models = cacheGet('archDocStatus');
            if (!$models) {
                $models = $this->modelInterface->all($request);
                cachePut('archDocStatus', $models);
            }
        } else {
            $models = $this->modelInterface->all($request);
        }

        return responseJson(200, 'success', ArchDocStatusResource::collection($models['data']), $models['paginate'] ? getPaginates($models['data']) : null);
    }

    public function create(ArchDocStatusRequest $request)
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

    public function update(ArchDocStatusRequest $request, $id)
    {
        $model = $this->modelInterface->find($id);
        if (!$model) {
            return responseJson(404, 'data not found');
        }
        $this->modelInterface->update($request, $id);
        return responseJson(200, 'success');
    }
}
