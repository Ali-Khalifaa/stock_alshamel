<?php

namespace App\Http\Requests\Hotfield;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Request;

class UpdateHotfieldRequest extends FormRequest
{

    public function authorize()
    {
        return true;
    }

    public function rules(Request $request)
    {
        return [
            'table_name'       => 'string|max:50' ,
            'field_name'       => 'string|max:50' ,
            'field_title'      => 'string|max:100',
            'field_title_en'   => 'string|max:100',
            'visibility'       => 'in:0,1',
        ];
    }

}
