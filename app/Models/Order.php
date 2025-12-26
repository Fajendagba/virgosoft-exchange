<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Order extends Model
{
    use HasUlids;

    public const STATUS_OPEN = 1;
    public const STATUS_FILLED = 2;
    public const STATUS_CANCELLED = 3;

    public const SIDE_BUY = 'buy';
    public const SIDE_SELL = 'sell';

    protected $fillable = [
        'user_id',
        'symbol',
        'side',
        'price',
        'amount',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'amount' => 'decimal:8',
            'status' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function trade(): HasOne
    {
        return $this->hasOne(Trade::class, 'buy_order_id')
            ->orWhere('sell_order_id', $this->id);
    }

    public function getTotalAttribute(): string
    {
        return bcmul($this->price, $this->amount, 2);
    }

    public function isOpen(): bool
    {
        return $this->status === self::STATUS_OPEN;
    }

    public function isBuy(): bool
    {
        return $this->side === self::SIDE_BUY;
    }

    public function isSell(): bool
    {
        return $this->side === self::SIDE_SELL;
    }

    public function scopeOpen($query)
    {
        return $query->where('status', self::STATUS_OPEN);
    }

    public function scopeBySymbol($query, string $symbol)
    {
        return $query->where('symbol', $symbol);
    }
}
