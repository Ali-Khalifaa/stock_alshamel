<?php

namespace App\Http\Requests\Country;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCountryRequest extends FormRequest
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
     * @return array<string, mixed>
     */public function rules()
    {
        return [
            'name' => 'string|max:255|unique:general_countries,name,' . $this->id,
            'name_e' => 'string|max:255|unique:general_countries,name,' . $this->id,
            "is_default" => "in:0,1",
            "phone_key" => "unique:general_countries,phone_key," . $this->id,
            'national_id_length' => "integer",
            'long_name' => "max:100",
            'long_name_e' => "max:100",
            'short_code' => "max:10",
            'is_active' => 'nullable|in:active,inactive',
            "media" => "nullable|array",
            "media.*" => ["exists:media,id", new \App\Rules\MediaRule()],
            'old_media.*' => ['exists:media,id', new \App\Rules\MediaRule("App\Models\Country")],
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array<string, string>
     */

    public function messages()
    {
        return [
            'name.string' => __('message.field must be string'),
            'name.max' => __('message.field must be less than 255 character'),
            'name.unique' => __('message.field must be unique'),
            'name_e.string' => __('message.field must be string'),
            'name_e.max' => __('message.field must be less than 255 character'),
            'name_e.unique' => __('message.field must be unique'),
            'is_default.in' => __('message.field must be in 0,1'),
            'phone_key.unique' => __('message.field must be unique'),
            'national_id_length.integer' => __('message.field must be integer'),
            'long_name.max' => __('message.field must be less than 100 character'),
            'long_name_e.max' => __('message.field must be less than 100 character'),
            'short_code.max' => __('message.field must be less than 10 character'),
            'is_active.in' => __('message.field must be in active,inactive'),
            'media.exists' => __('message.field must be exists'),
            'media.media' => __('message.field must be media'),

        ];
    }
}
