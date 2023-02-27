<?php

namespace App\Http\Resources\ArchDepartment;

use App\Http\Resources\ArchDepartment\ArchDepartmentRelationResource;
use Illuminate\Http\Resources\Json\JsonResource;

class ArchDepartmentResource extends JsonResource
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
            'parent_id' => $this->parent_id,
            'arch_department' => new ArchDepartmentRelationResource($this->parent),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
