<?php

namespace App\Http\Requests\Company;

use Illuminate\Foundation\Http\FormRequest;

class StoreCompanyRequest extends FormRequest
{

    public function authorize()
    {
        return true;
    }
    public function rules()
    {
        return [
            "partner_id" => "required|exists:partners,id",
            "name" => "required|string|max:100",
            "name_e" => "required|string|max:100",
            "url" => "required|url|string|max:200",
            "phone_code"      => [],
            "country_code"      => [],
            "address" => "required|string|max:200",
            "phone" => "required|numeric|digits_between:8,16",
            "cr" => "required|string",
            "tax_id" => "required|numeric|digits_between:1,10",
            "vat_no" => "required|numeric|digits_between:1,10",
            "email" => "required|email|unique:companies,email",
            "website" => "required|string|max:200",
            "is_active" => "in:active,inactive",
            "media" => "nullable|array",
            "media.*" => ["nullable", "exists:media,id", new \App\Rules\MediaRule()],
        ];
    }

    public function messages()
    {
        return [
            "parent_id.required" => __("message.field required"),
            "parent_id.exists" => __("message.field not exists"),
            "name.required" => __("message.field required"),
            "name.string" => __("message.field string must be string"),
            "name.max" => __("message.field max character is 100"),
            "name_e.required" => __("message.field required"),
            "name_e.string" => __("message.field string"),
            "name_e.max" => __("message.field max character is 100"),
            "url.required" => __("message.field required"),
            "url.url" => __("message.field invalid"),
            "url.string" => __("message.field string must be string"),
            "url.max" => __("message.field max character is 200"),
            "address.required" => __("message.field required"),
            "address.string" => __("message.field string must be string"),
            "address.max" => __("message.field max character is 200"),
            "phone.required" => __("message.field required"),
            "phone.numeric" => __("message.field must be numeric"),
            "phone.digits_between" => __("message.field must be between 8 and 16 digits"),
            "cr.required" => __("message.field required"),
            "cr.string" => __("message.field string must be string"),
            "tax_id.required" => __("message.field required"),
            "tax_id.numeric" => __("message.field must be numeric"),
            "tax_id.digits_between" => __("message.field must be between 1 and 10 digits"),
            "vat_no.required" => __("message.field required"),
            "vat_no.numeric" => __("message.field must be numeric"),
            "vat_no.digits_between" => __("message.field must be between 1 and 10 digits"),
            "email.required" => __("message.field required"),
            "email.email" => __("message.field invalid"),
            "email.unique" => __("message.field unique"),
            "website.required" => __("message.field required"),
            "website.string" => __("message.field string must be string"),
            "website.max" => __("message.field max character is 200"),
            "is_active.in" => __("message.field invalid"),
            "media.array" => __("message.field array"),
            "media.*.exists" => __("message.field not exists"),
            "media.*.media" => __("message.field invalid"),

        ];
    }
}
