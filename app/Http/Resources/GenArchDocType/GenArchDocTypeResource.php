<?php

namespace App\Http\Resources\GenArchDocType;

use  App\Http\Resources\GenArchDocType\GenArchDocRelationResource;
use Illuminate\Http\Resources\Json\JsonResource;

class GenArchDocTypeResource extends JsonResource
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
            'is_valid' => $this->is_valid,
            'parent_id' => new GenArchDocRelationResource($this->parent),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
