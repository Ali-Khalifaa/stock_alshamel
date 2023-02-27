<?php

namespace App\Http\Resources\ScreenButton;

use App\Http\Resources\Button\ButtonResource;
use App\Http\Resources\Screen\ScreenResource;
use App\Models\Button;
use App\Models\Screen;
use Illuminate\Http\Resources\Json\JsonResource;

class ScreenButtonResource extends JsonResource
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
            'screen_id'=>$this->screen_id,
            'screen'=>new ScreenResource(Screen::query ()->find ($this->screen_id)),
            'button_id'=>$this->button_id,
            'button'=>new ButtonResource(Button::query ()->find ($this->button_id))
        ];
    }
}
