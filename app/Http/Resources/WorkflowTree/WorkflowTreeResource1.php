<?php

namespace App\Http\Resources\WorkflowTree;

use App\Http\Resources\Company\CompanyResource;
use App\Http\Resources\Module\ModuleResource;
use App\Http\Resources\Partner\PartnerRelationResource;
use App\Http\Resources\Screen\ScreenRelationResource;
use App\Models\WorkflowTree;
use Illuminate\Http\Resources\Json\JsonResource;

class WorkflowTreeResource1 extends JsonResource
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
            'id'         =>$this->id,
            'name'       => $this->name,
            'name_e'     => $this->name_e,
            "is_active"  => $this->is_active,
            'parent'  => new WorkflowTreeResource($this->parent),
            'partner'    => new PartnerRelationResource($this->partner),
            'company' => new CompanyResource($this->company),
            'module'  => new ModuleResource($this->module),
            'screen'  => new ScreenRelationResource($this->screen),
            'icon_url'   => $this->icon,
            'id_sort'    => $this->id_sort,
        ];
    }
}
