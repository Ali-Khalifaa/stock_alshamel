<?php

namespace App\Http\Requests\ScreenHelpfile;

use Illuminate\Foundation\Http\FormRequest;

class StoreScreenHelpfileRequest extends FormRequest
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
    public function rules()
    {
        return [
            "screen_id" => "required|exists:sys_screens,id",
            "helpfile_id" => "required|exists:helpfiles,id",
        ];
    }
}
