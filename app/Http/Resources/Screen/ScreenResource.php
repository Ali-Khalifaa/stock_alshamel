<?php

namespace App\Http\Resources\Screen;

use App\Models\Screen;
use Illuminate\Http\Resources\Json\JsonResource;

class ScreenResource extends JsonResource
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
            'title'      => $this->title,
            'title_e'    => $this->title_e,
            'serial_id ' => $this->serial_id == null ? 'no serial' :$this->serial_id, //optional( $this->serial)->name,
            'deleted_at' => $this->deleted_at,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
