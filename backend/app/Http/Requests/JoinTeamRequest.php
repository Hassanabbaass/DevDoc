<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class JoinTeamRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'invite_code' => 'required|string|exists:teams,invite_code',
        ];
    }

    public function messages(): array
    {
        return [
            'invite_code.required' => 'Please provide an invite code.',
            'invite_code.exists' => 'That invite code is invalid.',
        ];
    }
}