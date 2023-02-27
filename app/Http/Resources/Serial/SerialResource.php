<?php

namespace App\Http\Resources\Serial;

use Illuminate\Http\Resources\Json\JsonResource;

class SerialResource extends JsonResource
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
            "start_no"       => $this->start_no,
            "perfix"         => $this->perfix,
            "suffix"         => $this->suffix,
            "restart_period" => $this->restart_period,
            "company_id"     => $this->company_id,
            "brach_id"       => $this->brach_id,
            "store_id"       => $this->store_id,
            "is_default"     => $this->is_default,
        ];
    }
}
