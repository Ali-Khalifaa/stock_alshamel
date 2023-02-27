<?php

namespace App\Http\Controllers\ArchiveClosedReference;

use App\Http\Controllers\Controller;
use App\Http\Requests\ArchiveClosedReference\ArchiveClosedReferenceRequest;
use App\Http\Resources\ArchiveClosedReference\ArchiveClosedReferenceResource;
use App\Repositories\ArchiveClosedReference\ArchiveClosedReferenceInterface;
use Illuminate\Http\Request;

class ArchiveClosedReferenceController extends Controller
{
    public function __construct(private ArchiveClosedReferenceInterface $modelInterface) {

    }

    public function all(Request $request)
    {
        if (count($_GET) == 0) {
            $models = cacheGet('archiveClosedReference');
            if (!$models) {
                $models = $this->modelInterface->all($request);
                cachePut('archiveClosedReference', $models);
            }
        } else {
            $models = $this->modelInterface->all($request);
        }

        return responseJson(200, 'success', ArchiveClosedReferenceResource::collection($models['data']), $models['paginate'] ? getPaginates($models['data']) : null);
    }

    public function create(ArchiveClosedReferenceRequest $request)
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

    public function update(ArchiveClosedReferenceRequest $request, $id)
    {
        $model = $this->modelInterface->find($id);
        if (!$model) {
            return responseJson(404, 'data not found');
        }
        $this->modelInterface->update($request, $id);
        return responseJson(200, 'success');
    }
}
