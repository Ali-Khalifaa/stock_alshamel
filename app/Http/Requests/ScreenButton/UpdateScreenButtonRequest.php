<?php

namespace App\Http\Requests\ScreenButton;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Request;

class UpdateScreenButtonRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(Request $request)
    {
        return [
            "screen_id" => "exists:sys_screens,id",
            "button_id" => "exists:sys_buttons,id",
        ];
    }
}
