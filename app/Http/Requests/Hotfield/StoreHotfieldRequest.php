<?php

namespace App\Http\Requests\Hotfield;

use Illuminate\Foundation\Http\FormRequest;

class StoreHotfieldRequest extends FormRequest
{

    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'table_name'       => 'required|string|max:50' ,
            'field_name'       => 'required|string|max:50' ,
            'field_title'      => 'required|string|max:100',
            'field_title_en'   => 'required|string|max:100',
            'visibility'       => 'in:0,1',
        ];
    }

}
