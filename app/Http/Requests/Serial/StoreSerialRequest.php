<?php

namespace App\Http\Requests\Serial;

use Illuminate\Foundation\Http\FormRequest;

class StoreSerialRequest extends FormRequest
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
            'start_no'      => 'required',
            'perfix'        => 'required|string|max:255',
            'suffix'        => 'required|string|max:255',
            'company_id'    => 'required',
            'branch_id'     => 'required',
            'store_id'      => 'required',
            
        ];
    }

    public function messages()
    {
        return [
            'required' => __('message.name is required'),
        ];
    }
}
