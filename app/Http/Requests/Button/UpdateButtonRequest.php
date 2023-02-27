<?php

namespace App\Http\Requests\Button;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Request;

class UpdateButtonRequest extends FormRequest
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
            'name' => 'string|max:100|unique:buttons,name,' . $request->id,
            'name_e' => 'string|max:100|unique:buttons,name_e,' . $request->id,
            "media" => "nullable|array",
            "media.*" => ["nullable", "exists:media,id", new \App\Rules\MediaRule()],
            'old_media.*' => ['exists:media,id', new \App\Rules\MediaRule("App\Models\Button")],
        ];
    }
}
