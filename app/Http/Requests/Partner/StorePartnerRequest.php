<?php

namespace App\Http\Requests\Partner;

use Illuminate\Foundation\Http\FormRequest;

class StorePartnerRequest extends FormRequest
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
            'name'      => 'required|string|max:255',
            'name_e'    => 'required|string|max:255',
            'email'     => 'required|email|unique:partners,email',
            'password'  => 'required|min:8',
            'mobile_no' => 'nullable|unique:partners,mobile_no',
            "phone_code"      => [],
            "country_code"      => [],
            "is_active" => "nullable|in:active,inactive",
        ];
    }

    public function messages()
    {
        return [
            'required'      => __('message.field is required'),
            'unique'        => __('message.field already exists'),
            'is_active.in'  => __('message.status must be active or inactive'),
        ];
    }
}
