<?php

namespace App\Http\Requests\ArchDocument;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;

class ArchDocumentRequest extends FormRequest
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
            'gen_arch_doc_type' => ['required', 'integer'],
            'doc_description' => ['required', 'string', 'max:500'],
            'doc_status' => ['required', 'integer'],
            'url_reference' => ['required', "url", "string", "max:200"],
        ];
    }
    public function messages()
    {
        return [
            'required'    => 'field is required',
        ];
    }

    protected function failedValidation(Validator $validator, $code = 400)
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
