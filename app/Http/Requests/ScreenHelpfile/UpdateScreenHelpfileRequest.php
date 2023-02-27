<?php

namespace App\Http\Requests\ScreenHelpfile;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Request;

class UpdateScreenHelpfileRequest extends FormRequest
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
            "screen_id" => "exists:screens,id",
            "helpfile_id" => "exists:helpfiles,id",
        ];
    }
}
