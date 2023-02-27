<?php

namespace App\Http\Requests\Store;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreStoreRequest extends FormRequest
{

    public function rules()
    {
        return [
            "company_id" => "required|exists:companies,id",
            "branch_id"  => "required|exists:branches,id",
            "name"       => "required|string|max:100",
            "name_e"     => "required|string|max:100",
            "is_active"  => "in:active,inactive",
        ];
    }



    public function authorize()
    {
        return true;
    }


    public function failedValidation ( Validator $validator )
    {
        throw new HttpResponseException(response()->json(
            [
                'status'    => 422,
                'success'   => false,
                'message'   => __ ('validation errors'),
                'data'      => $validator->errors()
            ]
        ));
    }
}
