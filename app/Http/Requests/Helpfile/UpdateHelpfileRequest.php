<?php

namespace App\Http\Requests\Helpfile;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Request;

class UpdateHelpfileRequest extends FormRequest
{

    public function authorize()
    {
        return true;
    }

    public function rules(Request $request)
    {
        return [
            'name'       => 'string|max:100' ,
            'name_e'     => 'string|max:100' ,
            'url'        => 'string|max:200'
        ];
    }

}
