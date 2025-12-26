<?php

namespace App\Http\Requests\Trading;

use App\Models\Order;
use App\Traits\CustomValidationErrorMessage;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PlaceOrderRequest extends FormRequest
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
            'side' => ['required', Rule::in([Order::SIDE_BUY, Order::SIDE_SELL])],
            'price' => ['required', 'numeric', 'gt:0', 'decimal:0,2'],
            'amount' => ['required', 'numeric', 'gt:0', 'decimal:0,8'],
        ];
    }

    public function messages(): array
    {
        return [
            'symbol.in' => 'The selected symbol is invalid. Only BTC and ETH are supported.',
            'side.in' => 'The side must be either buy or sell.',
            'price.gt' => 'The price must be greater than zero.',
            'amount.gt' => 'The amount must be greater than zero.',
        ];
    }
}
