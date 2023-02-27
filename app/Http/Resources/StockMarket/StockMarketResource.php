<?php

namespace App\Http\Resources\StockMarket;

use Illuminate\Http\Resources\Json\JsonResource;

class StockMarketResource extends JsonResource
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
            'id' => $this->id,
            'name' => $this->name,
            'name_e' => $this->name_e,
            'stock_market_url' => $this->stock_market_url,
        ];
    }
}
