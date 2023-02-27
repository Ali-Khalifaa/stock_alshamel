<?php

namespace App\Http\Controllers\StockSector;

use App\Http\Controllers\Controller;
use App\Http\Requests\StockSector\StockSectorRequest;
use App\Http\Resources\StockSector\StockSectorResource;
use App\Repositories\StockSector\StockSectorInterface;
use Illuminate\Http\Request;

class StockSectorController extends Controller
{
    public function __construct(private StockSectorInterface $modelInterface) {

    }

    public function all(Request $request)
    {
        $models = $this->modelInterface->all($request);
        return responseJson(200, 'success', StockSectorResource::collection($models['data']), $models['paginate'] ? getPaginates($models['data']) : null);
    }

    public function create(StockSectorRequest $request)
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

    public function update(StockSectorRequest $request, $id)
    {
        $model = $this->modelInterface->find($id);
        if (!$model) {
            return responseJson(404, 'data not found');
        }
        $this->modelInterface->update($request, $id);
        return responseJson(200, 'success');
    }
}
