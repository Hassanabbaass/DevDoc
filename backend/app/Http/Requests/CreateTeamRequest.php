<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateTeamRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:50|unique:teams,name',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Please give your workspace a name.',
            'name.max' => 'Workspace name cannot exceed 50 characters.',
            'name.unique' => 'A workspace with that name already exists.',
        ];
    }
}