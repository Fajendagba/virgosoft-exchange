<?php

namespace App\Http\Requests\Trading;

use App\Traits\CustomValidationErrorMessage;
use Illuminate\Foundation\Http\FormRequest;

class CancelOrderRequest extends FormRequest
{
    use CustomValidationErrorMessage;

    public function authorize(): bool
    {
        $order = $this->route('order');

        return $order && $order->user_id === $this->user()->id;
    }

    public function rules(): array
    {
        return [];
    }
}
