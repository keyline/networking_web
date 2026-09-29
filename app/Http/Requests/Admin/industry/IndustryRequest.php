<?php

namespace App\Http\Requests\Admin\industry;

use App\Helpers\Helper;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class IndustryRequest extends FormRequest
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
            $id = Helper::decoded($this->route('industry_master'));
            $rules['name'][] = Rule::unique('industry_master', 'im_name')->ignore($id, 'im_id');
        } else if ($this->isMethod('post')) {
            $rules['name'][] = Rule::unique('industry_master', 'im_name');
        }

        return $rules;
    }

    public function attributes()
    {
        return [
            'name' => 'Industry name'
        ];
    }
}
