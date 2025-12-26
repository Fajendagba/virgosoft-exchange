<?php

namespace App\Http\Requests\Auth;

use App\Traits\CustomValidationErrorMessage;
use Illuminate\Foundation\Http\FormRequest;

class LoginRequest extends FormRequest
{
    use CustomValidationErrorMessage;

    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'email' => strtolower($this->input('email')),
        ]);
    }

    public function rules(): array
    {
        return [
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ];
    }
}
