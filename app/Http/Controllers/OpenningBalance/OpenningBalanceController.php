<?php

namespace App\Http\Controllers\OpenningBalance;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\OpenningBalance\OpenningBalanceRequest;
use App\Http\Resources\OpenningBalance\OpenningBalanceResource;
use App\Repositories\OpenningBalance\OpenningBalanceInterface;
use App\Repositories\OpenningBalance\OpenningBalanceRepository;
use App\Models\StockSalePurchaseDetail;

class OpenningBalanceController extends Controller
{

    public function __construct(private OpenningBalanceInterface $modelInterface)
    {
    }

    public function all(Request $request)
    {
        $models = $this->modelInterface->all($request);
        return responseJson(200, 'success', OpenningBalanceResource::collection($models['data']), $models['paginate'] ? getPaginates($models['data']) : null);
    }

    public function getAll(Request $request)
    {
        $models = $this->modelInterface->getAll($request);
        return responseJson(200, 'success', OpenningBalanceResource::collection($models['data']), $models['paginate'] ? getPaginates($models['data']) : null);
    }
    public function create(OpenningBalanceRequest $request)
    {
        $this->modelInterface->create($request);
        return responseJson(200, 'success');
    }

    public function delete($id)
    {

        $wallet = StockSalePurchaseDetail::where('wallet_id', $id)->first();
        // dd($wallet)
        if (empty($wallet)) {
            $model = $this->modelInterface->delete($id);
            if (!$model) {
                return responseJson(404, 'data not found');
            }
            return responseJson(200, 'success');
        } else {
            $stockError = 'You cannot delete this wallet entry because it has some sale/purchase transaction. Kindly delete the transactions first and then delete this entry';
            $models['stock'] = $stockError;
            $models['data'] = false;
            return responseJson(200, $models['stock'], $models['data']);
        }


        return responseJson(200, 'success');
    }

    public function bulkDelete(Request $request)
    {
        foreach ($request->ids as $id) {
            $this->modelInterface->delete($id);
        }
        return responseJson(200, __('Done'));
    }

    public function rowUpdate(Request $request)
    {

        $model = $this->modelInterface->rowUpdate($request);
        if (!$model) {
            return responseJson(404, 'data not found');
        }
        return responseJson(200, 'success');
    }
    public function getWalletEntier($id)
    {
        $models = $this->modelInterface->getWalletEntier($id);
        return responseJson(200, 'success', OpenningBalanceResource::collection($models['data']));
    }

    public function checkDate($id)
    {
        $models = $this->modelInterface->checkDate($id);
        if ($models['data'] == false) {
            return responseJson(200, $models['data']);
        }
        return responseJson(200, 'success', $models['data']);
    }
}
