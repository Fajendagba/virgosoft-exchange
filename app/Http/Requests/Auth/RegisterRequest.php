<?php

namespace App\Http\Requests\Auth;

use App\Traits\CustomValidationErrorMessage;
use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ];
    }
}
