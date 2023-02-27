<?php

namespace App\Http\Resources\ArchDocTypeField;

use App\Http\Resources\GenArchDocType\GenArchDocRelationResource;
use App\Http\Resources\DocumentField\DocumentFieldRelationResource;
use Illuminate\Http\Resources\Json\JsonResource;

class ArchDocTypeFieldResource extends JsonResource
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
            'gen_arch_doc_type_id' => new GenArchDocRelationResource($this->genArchDocType),
            'arch_doc_field_id' => new DocumentFieldRelationResource($this->archDocType),
            'field_order' => $this->field_order,
            'is_required' => $this->is_required,
            'field_characters' => $this->field_characters,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
