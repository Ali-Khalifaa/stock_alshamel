<?php

namespace App\Http\Requests\CompanyModule;

use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateCompanyModuleRequest extends FormRequest
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
            'company_id'        => 'required',
            'module_id'         => 'required',
            "allowed_users_no"  => "required",
            "start_date"        => "required",
            "end_date"          => "",

        ];
    }

    public function messages()
    {
        return [
            'required'      => __('message.field is required'),

        ];
    }
}
