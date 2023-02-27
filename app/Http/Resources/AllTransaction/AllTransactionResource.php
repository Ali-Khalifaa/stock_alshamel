<?php

namespace App\Http\Resources\AllTransaction;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\Wallet\WalletRelationResource;
use App\Http\Resources\Stock\StoctRelationResource;
use App\Http\Resources\ClosingBalance\ClosingBalanceRelationResource;
use App\Models\StockSalePurchaseDetail;
use App\Models\Wallet;
use Illuminate\Support\Facades\DB;

class AllTransactionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'id' =>  $this->id,
            'wallet_id' => $this->wallet_id,
            'stock_id' => $this->stock_id,
            'stock' => new StoctRelationResource($this->stock),
            'wallet' => new WalletRelationResource($this->wallet),
            'closing' => new ClosingBalanceRelationResource($this->closing),
            'date' => $this->date,
            'time' => $this->time,
            'type' => $this->type,
            'sellAmount' => $this->sellAmount,
            'first' => $this->first,
            "buyAmount" => $this->buyAmount,
            "buyqty" => $this->buyqty,
            'sellqty' => $this->sellqty,
            "old_price" => $this->old_price,
            "old_sell_qty" => $this->old_sell_qty,
            "old_sell_price" => $this->old_sell_price,
            'qty' => $this->qty,
            'price' => $this->price,
            'fixed_commission' => $this->fixed_commission,
            'other_commission' => $this->other_commission,
            'net_value' => $this->net_value,
            'trade_average' => $this->trade_average,
            'old_qty' => $this->old_qty,
            'old_average' => $this->old_average,
            'new_qty' => $this->new_qty,
            'new_average' => $this->new_average,
            'profit' => $this->profit,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'raw_arr' => $this->raw_arr,
            'last' => $this->sumValue($this->date, $this->wallet_id, $this->stock_id, $request->end_date),

            'before_total_cost_price'=>$this->new_average * $this->old_qty,
            'before_closing_price'=>@$this->closing?@$this->closing->amount:0 ,
            'before_closing_total'=>@$this->closing?(@$this->closing->amount * $this->old_qty):0 ,
            'last_sell_qty'=>@$this->getLast ($request,$this->wallet_id,$this->stock_id)->old_sell_qty?:0,
            'last_sell_value'=>(@$this->getLast ($request,$this->wallet_id,$this->stock_id)->old_sell_qty?:0) * (@$this->getLast ($request,$this->wallet_id,$this->stock_id)->old_sell_price?:0),
            'qty_purchase'=>@$this->sumValue1 ($this->date, $this->wallet_id, $this->stock_id, $request->end_date)->t_qty?:0,
            'val_purchase'=>@$this->sumValue1 ($this->date, $this->wallet_id, $this->stock_id, $request->end_date)->t_price?:0,
            'current_qty'=>@$this->getLast ($request,$this->wallet_id,$this->stock_id)->new_qty?:0,
            'current_price'=>@$this->getLast ($request,$this->wallet_id,$this->stock_id)->new_average?:0,
            'current_total'=>(@$this->getLast ($request,$this->wallet_id,$this->stock_id)->new_average?:0)*(@$this->getLast ($request,$this->wallet_id,$this->stock_id)->new_qty?:0),
            'current_closing_price'=>@$this->getLast ($request,$this->wallet_id,$this->stock_id)->closing->amount?:0,
            'current_closing_total'=>(@$this->getLast ($request,$this->wallet_id,$this->stock_id)->closing->amount?:0)*(@$this->getLast ($request,$this->wallet_id,$this->stock_id)->new_qty?:0),
        ];
    }

    public function sumValue($date, $wallet_id, $stock_id, $endDate)
    {
        $data = StockSalePurchaseDetail::whereDate('date', '>=', $date)
            ->whereDate('date', '<=', $endDate)
            ->where('wallet_id', $wallet_id)
            ->where('stock_id', $stock_id)
            ->where('type', 'Buy')
            ->select("*", DB::raw('SUM(qty*price) as t_price'), DB::raw('SUM(qty) as t_qty'))
            ->get();
        return $data;
    }
    public function sumValue1($date, $wallet_id, $stock_id, $endDate)
    {
        $data = StockSalePurchaseDetail::whereDate('date', '>=', $date)
            ->whereDate('date', '<=', $endDate)
            ->where('wallet_id', $wallet_id)
            ->where('stock_id', $stock_id)
            ->where('type', 'Buy')
            ->select("*", DB::raw('SUM(qty*price) as t_price'), DB::raw('SUM(qty) as t_qty'))
            ->first();
        return $data;
    }

    public function getLast($request,$wallet_id,$stock_id){
        $last = StockSalePurchaseDetail::whereBetween('date', [$request->start_date, $request->end_date])
            ->where('wallet_id', $wallet_id)
            ->where('stock_id', $stock_id)
            ->orderBy('id', 'desc')->first();
        return $last;
    }
}
