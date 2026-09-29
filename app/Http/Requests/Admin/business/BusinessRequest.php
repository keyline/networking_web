<?php

namespace App\Http\Requests\Admin\business;

use App\Helpers\Helper;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BusinessRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        $rules = [
            'name' => [
                'required',
                'string',
                'max:255',
                'regex:/^[^<>]*$/',

            ],
        ];
        if ($this->isMethod('patch')) {
            $id = Helper::decoded($this->route('business_master'));
            $rules['name'][] = Rule::unique('business_type_master', 'btm_name')->ignore($id, 'btm_id');
        } else if ($this->isMethod('post')) {
            $rules['name'][] = Rule::unique('business_type_master', 'btm_name');
        }

        return $rules;
    }

    public function attributes()
    {
        return [
            'name' => 'Business name'
        ];
    }
}
