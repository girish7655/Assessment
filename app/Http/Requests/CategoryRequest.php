<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'max:25',
                'regex:/^[a-zA-Z\s]+$/',
                Rule::unique('categories', 'name')
                    ->whereNull('deleted_at')
                    ->ignore($this->route('category'))
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Category name is required.',
            'name.string' => 'Category name must be a valid string.',
            'name.max' => 'Category name cannot exceed 25 characters.',
            'name.regex' => 'Category name can only contain letters and spaces.',
            'name.unique' => 'Category name already exists.',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'name' => trim($this->name),
        ]);
    }
}