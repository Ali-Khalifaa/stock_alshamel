<?php

namespace App\Http\Resources\ScreenDocumentType;

use Illuminate\Http\Resources\Json\JsonResource;

class ScreenDocumentTypeResource extends JsonResource
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
            "screen_id"       => $this->screen_id,
            "document_type_id"         => $this->document_type_id,
        ];
    }
}
