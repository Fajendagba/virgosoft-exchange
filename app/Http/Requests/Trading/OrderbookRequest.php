<?php

namespace App\Http\Requests\Trading;

use App\Traits\CustomValidationErrorMessage;
use Illuminate\Foundation\Http\FormRequest;

class OrderbookRequest extends FormRequest
{
    use CustomValidationErrorMessage;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'symbol' => ['required', 'string', 'in:BTC,ETH'],
        ];
    }

    public function messages(): array
    {
        return [
            'symbol.required' => 'The symbol field is required.',
            'symbol.in' => 'The selected symbol is invalid. Only BTC and ETH are supported.',
        ];
    }
}
