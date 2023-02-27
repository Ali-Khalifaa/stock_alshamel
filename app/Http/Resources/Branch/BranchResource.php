<?php

namespace App\Http\Resources\Branch;

use App\Http\Resources\Company\CompanyResource;
use Illuminate\Http\Resources\Json\JsonResource;

class BranchResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request
     * @return array
     */
    public function toArray($request)
    {
        return [

            'id' => $this->id,
            'name' => $this->name,
            'name_e' => $this->name_e,
            'is_active' => $this->is_active,
            'company' => new CompanyResource($this->company),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
