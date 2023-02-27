<?php

namespace App\Http\Controllers\StockMarket;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\StockMarket\StockMarketRequest;
use App\Http\Resources\StockMarket\StockMarketResource;
use App\Repositories\StockMarket\StockMarketInterface;

class StockMarketController extends Controller
{
    public function __construct(private StockMarketInterface $modelInterface) {

    }

    public function all(Request $request)
    {
        $models = $this->modelInterface->all($request);

        return responseJson(200, 'success', StockMarketResource::collection($models['data']), $models['paginate'] ? getPaginates($models['data']) : null);
    }

    public function create(StockMarketRequest $request)
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

    public function update(StockMarketRequest $request, $id)
    {
        $model = $this->modelInterface->find($id);
        if (!$model) {
            return responseJson(404, 'data not found');
        }
        $this->modelInterface->update($request, $id);
        return responseJson(200, 'success');
    }
}
