<?php

namespace App\Http\Resources\Hotfield;

use Illuminate\Http\Resources\Json\JsonResource;

class HotfieldResource extends JsonResource
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
            'id' => $this->id ,
            'table_name'    => $this->table_name    ,
            'field_name'    => $this->field_name    ,
            'field_title'   => $this->field_title   ,
            'field_title_en' => $this->field_title_en ,
            'visibility'    => $this->visibility  == 1 ? true : false  ,
        ];
    }
}
