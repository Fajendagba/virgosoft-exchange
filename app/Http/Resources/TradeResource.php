<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TradeResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'symbol' => $this->symbol,
            'price' => $this->price,
            'amount' => $this->amount,
            'total' => $this->total,
            'commission' => $this->commission,
            'created_at' => $this->created_at,
        ];
    }
}
