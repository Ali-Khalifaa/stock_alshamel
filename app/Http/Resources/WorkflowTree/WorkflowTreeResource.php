<?php

namespace App\Http\Resources\WorkflowTree;

use App\Http\Resources\FileResource;
use App\Http\Resources\Partner\PartnerRelationResource;
use App\Http\Resources\Screen\ScreenRelationResource;
use Illuminate\Http\Resources\Json\JsonResource;

class WorkflowTreeResource extends JsonResource
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
            'parent_id'  => $this->parent_id ,//== null ?null :optional($this->child)->name,
            'partner'    => new PartnerRelationResource($this->partner),
            'company_id' => $this->company_id,
            'module_id'  => $this->module_id,
            'partner_id'  => $this->partner_id,
            'screen_id'  => $this->screen_id,
            'screen'  => new ScreenRelationResource($this->screen),
            'module'  => new ScreenRelationResource($this->module),
            'company'  => new ScreenRelationResource($this->company),
            'icon_url'   => $this->icon,
            'id_sort'    => $this->id_sort,
            'deleted_at' => $this->deleted_at,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            "media" => isset($this->files) ? FileResource::collection($this->files) : null,
        ];
    }
}
