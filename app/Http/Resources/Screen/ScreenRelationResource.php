<?php

namespace App\Http\Resources\Screen;

use Illuminate\Http\Resources\Json\JsonResource;

class ScreenRelationResource extends JsonResource
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
            'id'   =>$this->id,
            'name' => $this->name,
            'name_e' => $this->name_e
        ];
    }
}
