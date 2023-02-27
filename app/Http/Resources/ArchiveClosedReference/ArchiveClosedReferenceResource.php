<?php

namespace App\Http\Resources\ArchiveClosedReference;

use App\Http\Resources\DocumentField\DocumentFieldRelationResource;
use Illuminate\Http\Resources\Json\JsonResource;

class ArchiveClosedReferenceResource extends JsonResource
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
            'arch_docfields_id' => $this->arch_docfields_id,
            'arch_docfield' => new DocumentFieldRelationResource($this->documentField),
            'field_value' => $this->field_value,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
