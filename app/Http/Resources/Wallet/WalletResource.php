<?php

namespace App\Http\Resources\Wallet;

use App\Models\ClosingBalance;
use App\Models\StockSalePurchaseDetail;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\DB;

class WalletResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $num = DB::table('stock_sale_purchase_details')
            ->select('stock_id', DB::raw('SUM(new_qty * new_average) as totalrow'))
            ->where('wallet_id', $this->id)
            ->whereIn('updated_at', function ($query) {
                $query->select(DB::raw('MAX(updated_at)'))
                    ->from('stock_sale_purchase_details')
                    ->where('wallet_id', $this->id)
                    ->groupBy('stock_id');
            })
            ->groupBy('stock_id')
            ->get()
            ->sum('totalrow');

        $gt = DB::table('stock_sale_purchase_details')
            ->select('stock_id', DB::raw('SUM(new_qty) as new_qty'))
            ->where('wallet_id', $this->id)
            ->whereIn('updated_at', function ($query) {
                $query->select(DB::raw('MAX(updated_at)'))
                    ->from('stock_sale_purchase_details')
                    ->where('wallet_id', $this->id)
                    ->groupBy('stock_id');
            })
            ->groupBy('stock_id')
            ->get();
        $co = 0;
        foreach ($gt as $item){
            $d = DB::table ('closing_balances')->where ('stock_id',$item->stock_id)->orderByDesc ('date')->first ();
            $s = @$d->amount?:0;
            $co += $s * $item->new_qty;
        }
        return [
            'id' => $this->id,
            'name' => $this->name,
            'name_e' => $this->name_e,
            'stock_balance' => $this->stock_balance,
            'total_value_stocks'=>$num,
            'total_closing_price'=>$co,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at
        ];
    }
}
