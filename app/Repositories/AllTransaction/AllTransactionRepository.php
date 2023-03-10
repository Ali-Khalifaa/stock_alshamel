<?php

namespace App\Repositories\AllTransaction;

use Illuminate\Support\Facades\DB;
use App\Models\StockSalePurchaseDetail;
use App\Models\Stock;

class AllTransactionRepository implements AllTransactionInterface
{

    public function __construct(private StockSalePurchaseDetail $model)
    {
    }
    public function all($request)
    {

        $first = $this->model->whereDate('date', '>=', $request->start_date)
            ->where('wallet_id', $request->wallet_id);
        if ($request->stock_id){
            $first = $first->where('stock_id', $request->stock_id);
        }

        $first =$first->first();

        $models = $this->model
            ->whereBetween('date', [$request->start_date, $request->end_date])
            ->where('wallet_id', $request->wallet_id);
        if ($request->stock_id){
            $models = $models->where('stock_id', $request->stock_id);
        }
        // ->whereRaw('id in (select max(id) from sys_stock_sale_purchase_details group by (stock_id))')
        $models = $models->orderBy('id', 'desc')->get();



        $stock_ids= $this->model->whereDate('date', '>=', $request->start_date)
            ->where('wallet_id', $request->wallet_id)
            ->pluck('stock_id')->toArray();
        $stock_ids = array_unique ($stock_ids);
        $arr = [];
        foreach ($stock_ids as $id){
            $fir = $this->model->whereDate('date', '>=', $request->start_date)
                ->where('wallet_id', $request->wallet_id)
                ->where('stock_id', $id)
                ->first();
            array_push ($arr,$fir);
        }


        // if ($request->has('wallet_id')) {
        //     $models = $this->model->where('wallet_id', $request->wallet_id);
        //     // $models =  $this->model->whereRaw('id IN (select MAX(id) FROM sys_stock_sale_purchase_details GROUP BY stock_id)');
        // }
        // if ($request->has('stock_id')) {
        //     $models = $models->
        // }

        // if (!empty($request->stock_id)) {
        //     $models->whereRaw('id IN (select MAX(id) FROM sys_stock_sale_purchase_details GROUP BY stock_id)');
        // } else {
        //     $models =  $this->model->whereRaw('id IN (select MAX(id) FROM sys_stock_sale_purchase_details GROUP BY stock_id)');
        // }

        $models['first'] = $first;

//        return ['data' => $models, 'paginate' => false];
        return ['data' => $arr, 'paginate' => false];
    }

    // public function find($id)
    // {
    //     return $this->model->find($id);
    // }

    public function create($request)
    {
        DB::transaction(function () use ($request) {
            $this->model->create($request->all());
            cacheForget("alltransaction");
        });
    }



    // public function update($request, $id)
    // {
    //     DB::transaction(function () use ($id, $request) {
    //         $this->model->where("id", $id)->update($request->all());
    //         $this->forget($id);
    //     });
    // }

    // public function delete($id)
    // {
    //     $model = $this->find($id);
    //     $this->forget($id);
    //     $model->delete();
    // }


    // public function logs($id)
    // {
    //     return $this->model->find($id)->activities()->orderBy('created_at', 'DESC')->get();
    // }


    private function forget($id)
    {
        $keys = [
            "currency",
            "currency_" . $id,
        ];
        foreach ($keys as $key) {
            cacheForget($key);
        }
    }
}
