<?php

namespace App\Http\Requests\Helpfile;

use Illuminate\Foundation\Http\FormRequest;

class StoreHelpfileRequest extends FormRequest
{

    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'name'       => 'required|string|max:100',
            'name_e'     => 'required|string|max:100',
            'url'        => 'required|string|max:200'
        ];
    }

}
