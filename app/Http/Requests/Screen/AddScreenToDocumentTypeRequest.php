<?php

namespace App\Http\Requests\Screen;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;


class AddScreenToDocumentTypeRequest extends FormRequest
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
            'documentType_id' => 'required|exists:document_types,id',
            'screen_id' => 'required|exists:screens,id',
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

    protected function failedValidation(Validator $validator, $code=400)
    {
        throw new HttpResponseException(
            response()->json(
                [
                    'status' => $code,
                    'success' => false,
                    'message' => $validator->errors(),
                    'data' => null
                ],
                $code
            )
        );
    }

    protected function failedAuthorization()
    {
        throw new HttpResponseException(
            response()->json(
                [
                    'status' => false,
                    'message' => "Error: you are not authorized or do not have the permission",
                    'data' => null
                ],
                401
            )
        );
    }
}
