<?php

namespace App\Http\Resources\CompanyModule;

use App\Http\Resources\Company\CompanyResource;
use App\Http\Resources\Module\ModuleResource;
use Illuminate\Http\Resources\Json\JsonResource;

class CompanyModuleResource extends JsonResource
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
            "id"     => $this->id,
            "company"     =>  new CompanyResource($this->company),
            "module"       =>  new ModuleResource($this->module),
            "start_date"         => $this->start_date,
            "end_date"         => $this->end_date,
            "allowed_users_no" => $this->allowed_users_no,
            "created_at"       => $this->created_at,
            "updated_at"       => $this->updated_at,
        ];
    }
}
