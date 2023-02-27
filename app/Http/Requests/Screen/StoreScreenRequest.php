<?php

namespace App\Http\Requests\Screen;

use Illuminate\Foundation\Http\FormRequest;

class StoreScreenRequest extends FormRequest
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
            'name'       => 'required|string|max:100|unique:screens,name',
            'name_e'     => 'required|string|max:100|unique:screens,name_e',
            'title'      => 'required|string|max:100|unique:screens,title',
            'title_e'    => 'required|string|max:100|unique:screens,title_e',
            'serial_id'  => 'nullable',
        ];
    }

    public function messages()
    {
        return [
            // 'required'      => __('message.field is required'),
            'title.unique'        => __('message.field already exists'),
            'title_e.unique'        => __('message.field already exists'),
            'name.unique'        => __('message.field already exists'),
            'name_e.unique'        => __('message.field already exists'),
            'title.required'        => __('message.field already exists'),
            'title_e.required'        => __('message.field already exists'),
            'name.required'        => __('message.field already exists'),
            'name_e.required'        => __('message.field already exists'),
        ];
    }
}
