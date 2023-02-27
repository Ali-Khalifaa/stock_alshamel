<?php

namespace App\Http\Resources\ArchDocumentDtl;

use App\Http\Resources\GenArchDocType\GenArchDocRelationResource;
use App\Http\Resources\DocumentField\DocumentFieldRelationResource;
use Illuminate\Http\Resources\Json\JsonResource;

class ArchDocumentDtlResource extends JsonResource
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
            'field_value' => $this->field_value,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
