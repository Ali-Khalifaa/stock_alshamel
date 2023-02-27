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
            "is_active" => "nullable|in:active,inactive",
        ];
    }

    public function messages()
    {
        return [
            'required'      => __('message.name is required'),
            'is_active.in'  => __('message.status must be active or inactive'),
        ];
    }
}
