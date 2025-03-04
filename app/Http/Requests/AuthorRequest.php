<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AuthorRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $id = $this->route('author');
        return [
            'name' => [
                'required',
                'string',
                'max:25',
                'regex:/^[a-zA-Z\s]+$/',
                Rule::unique('authors', 'name')
                    ->whereNull('deleted_at')
                    ->ignore($id)
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'The author name is required.',
            'name.string' => 'The author name must be a valid string.',
            'name.max' => 'The author name must not exceed 25 characters.',
            'name.regex' => 'Only alphabets and spaces are allowed.',
            'name.unique' => 'This author name already exists.',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'name' => trim($this->name),
        ]);
    }
}
