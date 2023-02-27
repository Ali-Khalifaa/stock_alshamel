<?php

namespace App\Http\Requests\Screen;

use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateScreenRequest extends FormRequest
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
            'name' => [
                'required',
                'string',
                'max:100',
                Rule::unique('screens', 'name')->ignore($this->route('id')),
            ],
            'name_e' => [
                'required',
                'string',
                'max:100',
                Rule::unique('screens', 'name_e')->ignore($this->route('id')),
            ],
            'title' => [
                'required',
                'string',
                'max:100',
                Rule::unique('screens', 'title')->ignore($this->route('id')),
            ],
            'title_e' => [
                'required',
                'string',
                'max:100',
                Rule::unique('screens', 'title_e')->ignore($this->route('id')),
            ],
            'serial_id'  => 'nullable',
        ];
    }

    public function messages()
    {
        return [
            'required'      => __('message.field is required'),
            'unique'        => __('message.field already exists'),
        ];
    }
}
