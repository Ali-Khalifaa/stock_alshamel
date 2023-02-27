<?php

namespace App\Http\Requests\Module;

use Illuminate\Foundation\Http\FormRequest;

class AddCompanyToModuleRequest extends FormRequest
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
            'module_id' => 'required|exists:modules,id',
            'company_id' => 'required|exists:companies,id',
            "start_date" => "required|date_format:Y-m-d|before:end_date",
            "end_date" => "required|date_format:Y-m-d|after:start_date",
            "allowed_users_no" => "required|integer",
        ];
    }

    public function messages()
    {
        return [
            'module_id.required' => __('message.module is required'),
            'module_id.exists' => __('message.module is not exists'),
            'company_id.required' => __('message.company is required'),
            'company_id.exists' => __('message.company is not exists'),
            'start_date.required' => __('message.start date is required'),
            'start_date.date_format' => __('message.start date is not valid'),
            'end_date.required' => __('message.end date is required'),
            'end_date.date_format' => __('message.end date is not valid'),
            'allowed_users_no.required' => __('message.allowed user num is required'),
            'allowed_users_no.integer' => __('message.allowed user num is not valid'),
        ];
    }
}
