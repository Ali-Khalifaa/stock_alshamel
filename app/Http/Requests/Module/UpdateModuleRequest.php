<?php

namespace App\Http\Requests\Module;

use Illuminate\Foundation\Http\FormRequest;

class UpdateModuleRequest extends FormRequest
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
        $id = $this->id;
        return [
            'name' => 'required|string|max:255|unique:modules,name,' . $id,
            'name_e' => 'required|string|max:255|unique:modules,name_e,' . $id,
            'parent_id' => ["nullable"],
            "is_active" => "nullable|in:active,inactive",
        ];
    }

    public function messages()
    {
        return [
            'name.required' => __('message.name is required'),
            'name_e.required' => __('message.name is required'),
            'name.unique' => __('message.name already exists'),
            'name_e.unique' => __('message.name already exists'),
            'parent_id.exists' => __('message.parent not found'),
            'is_active.in' => __('message.status must be active or inactive'),
        ];
    }
}
