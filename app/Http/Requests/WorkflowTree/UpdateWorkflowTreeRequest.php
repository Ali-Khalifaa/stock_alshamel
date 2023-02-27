<?php

namespace App\Http\Requests\WorkflowTree;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateWorkflowTreeRequest extends FormRequest
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
            'name' => 'nullable|string|max:255',
            'name_e' => 'nullable|string|max:255',
            'is_active' => 'nullable|in:active,inactive',
            'parent_id' => 'nullable',
            'partner_id' => 'nullable',
            'company_id' => 'nullable',
            'module_id'  => 'nullable',
            'screen_id'  => 'nullable',
            'id_sort'    => 'nullable',
            "media" => "nullable|array",
            "media.*" => ["nullable", "exists:media,id", new \App\Rules\MediaRule()],
            'old_media.*' => ['exists:media,id', new \App\Rules\MediaRule("App\Models\WorkflowTree")],
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
