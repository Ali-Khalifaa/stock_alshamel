<?php

namespace App\Http\Requests\Store;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Request;

class UpdateStoreRequest extends FormRequest
{

    public function rules(Request $request)
    {
        return [
            "company_id" => "exists:companies,id",
            "branch_id"  => "exists:branches,id",
            "name"       => "string|max:100",
            "name_e"     => "string|max:100",
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
