<?php

namespace App\Events;

use App\Models\Trade;
use App\Models\User;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class OrderMatched implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public Trade $trade,
        public User $user
    ) {}

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('user.' . $this->user->id),
        ];
    }

    public function broadcastAs(): string
    {
        return 'order.matched';
    }

    public function broadcastWith(): array
    {
        $isBuyer = $this->user->id === $this->trade->buyer_id;

        return [
            'trade' => [
                'id' => $this->trade->id,
                'symbol' => $this->trade->symbol,
                'side' => $isBuyer ? 'buy' : 'sell',
                'price' => $this->trade->price,
                'amount' => $this->trade->amount,
                'total' => $this->trade->total,
                'commission' => $this->trade->commission,
                'created_at' => $this->trade->created_at,
            ],
            'balance' => $this->user->fresh()->balance,
            'assets' => $this->user->fresh()->assets->map(fn ($a) => [
                'symbol' => $a->symbol,
                'amount' => $a->amount,
                'locked_amount' => $a->locked_amount,
            ]),
        ];
    }
}
