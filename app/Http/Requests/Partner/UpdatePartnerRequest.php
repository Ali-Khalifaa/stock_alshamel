<?php

namespace App\Http\Requests\Partner;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePartnerRequest extends FormRequest
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
            'name' => 'required|string|max:255',
            'name_e' => 'required|string|max:255',
            "is_active" => "nullable|in:active,inactive",
            'email' => [
                'required',
                'email',
                'max:191',
                Rule::unique('partners', 'email')->ignore($this->route('id')),
            ],
            'mobile_no' => [
                'required',
                Rule::unique('partners', 'mobile_no')->ignore($this->route('id')),
            ],
            "phone_code"      => [],
            "country_code"      => [],
        ];
    }

    public function messages()
    {
        return [
            'required' => __('message.field is required'),
            'unique' => __('message.field already exists'),
            'is_active.in' => __('message.status must be active or inactive'),
        ];
    }
}
