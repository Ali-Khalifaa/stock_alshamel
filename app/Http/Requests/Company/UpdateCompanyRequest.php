<?php

namespace App\Http\Requests\Company;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCompanyRequest extends FormRequest
{

    public function authorize()
    {
        return true;
    }
    public function rules()
    {
        return [
            "partner_id" => "exists:partners,id",
            "name" => "string|max:100",
            "name_e" => "string|max:100",
            "url" => "url|string|max:200",
            "address" => "string|max:200",
            "phone" => "numeric|digits_between:8,16",
            "cr" => "string",
            "tax_id" => "numeric|digits_between:1,10",
            "vat_no" => "numeric|digits_between:1,10",
            "email" => "email|unique:companies,email,". $this->id,
            "website" => "string|max:200",
            "is_active" => "in:active,inactive",
            "phone_code"      => [],
            "country_code"      => [],
            "media" => "nullable|array",
            "media.*" => ["nullable", "exists:media,id", new \App\Rules\MediaRule()],
            'old_media.*' => ['exists:media,id', new \App\Rules\MediaRule("App\Models\Company")],
        ];
    }

    public function messages()
    {
        return [
            "parent_id.exists" => __("message.field not exists"),
            "name.string" => __("message.field string must be string"),
            "name.max" => __("message.field max character is 100"),
            "name_e.string" => __("message.field string"),
            "name_e.max" => __("message.field max character is 100"),
            "url.url" => __("message.field invalid"),
            "url.string" => __("message.field string must be string"),
            "url.max" => __("message.field max character is 200"),
            "address.string" => __("message.field string must be string"),
            "address.max" => __("message.field max character is 200"),
            "phone.numeric" => __("message.field must be numeric"),
            "phone.digits_between" => __("message.field must be between 8 and 16 digits"),
            "cr.string" => __("message.field string must be string"),
            "tax_id.numeric" => __("message.field must be numeric"),
            "tax_id.digits_between" => __("message.field must be between 1 and 10 digits"),
            "vat_no.numeric" => __("message.field must be numeric"),
            "vat_no.digits_between" => __("message.field must be between 1 and 10 digits"),
            "email.email" => __("message.field invalid"),
            "email.unique" => __("message.field unique"),
            "website.string" => __("message.field string must be string"),
            "website.max" => __("message.field max character is 200"),
            "is_active.in" => __("message.field invalid"),
            "media.array" => __("message.field array"),
            "media.*.exists" => __("message.field not exists"),
            "media.*.media" => __("message.field invalid"),

        ];
    }
}
