<?php

namespace App\Services;

use App\Events\OrderMatched;
use App\Exceptions\InsufficientAssetException;
use App\Exceptions\InsufficientBalanceException;
use App\Exceptions\OrderNotFoundException;
use App\Models\Asset;
use App\Models\Order;
use App\Models\Trade;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class OrderMatchingService
{
    private const COMMISSION_RATE = '0.015';

    public function placeOrder(User $user, array $data): Order
    {
        return DB::transaction(function () use ($user, $data) {
            $user = User::lockForUpdate()->find($user->id);

            $this->validateAndLockFunds($user, $data);

            $order = Order::create([
                'user_id' => $user->id,
                'symbol' => $data['symbol'],
                'side' => $data['side'],
                'price' => $data['price'],
                'amount' => $data['amount'],
                'status' => Order::STATUS_OPEN,
            ]);

            $this->attemptMatch($order);

            return $order->fresh();
        });
    }

    private function validateAndLockFunds(User $user, array $data): void
    {
        $total = bcmul($data['price'], $data['amount'], 2);

        if ($data['side'] === Order::SIDE_BUY) {
            if (bccomp($user->balance, $total, 2) < 0) {
                throw new InsufficientBalanceException('Insufficient USD balance to place this order');
            }
            $user->decrement('balance', $total);
        } else {
            $asset = Asset::where('user_id', $user->id)
                ->where('symbol', $data['symbol'])
                ->lockForUpdate()
                ->first();

            if (!$asset || bccomp($asset->amount, $data['amount'], 8) < 0) {
                throw new InsufficientAssetException('Insufficient ' . $data['symbol'] . ' balance to place this order');
            }

            $asset->decrement('amount', $data['amount']);
            $asset->increment('locked_amount', $data['amount']);
        }
    }

    private function attemptMatch(Order $order): void
    {
        $order = Order::lockForUpdate()->find($order->id);

        if (!$order->isOpen()) {
            return;
        }

        $match = $this->findMatch($order);

        if ($match) {
            $this->executeTrade($order, $match);
        }
    }

    private function findMatch(Order $order): ?Order
    {
        $query = Order::where('symbol', $order->symbol)
            ->where('side', '!=', $order->side)
            ->where('status', Order::STATUS_OPEN)
            ->where('user_id', '!=', $order->user_id)
            ->where('amount', $order->amount)
            ->lockForUpdate();

        if ($order->isBuy()) {
            $query->where('price', '<=', $order->price)
                ->orderBy('price', 'asc')
                ->orderBy('created_at', 'asc');
        } else {
            $query->where('price', '>=', $order->price)
                ->orderBy('price', 'desc')
                ->orderBy('created_at', 'asc');
        }

        return $query->first();
    }

    private function executeTrade(Order $newOrder, Order $matchOrder): void
    {
        $buyOrder = $newOrder->isBuy() ? $newOrder : $matchOrder;
        $sellOrder = $newOrder->isSell() ? $newOrder : $matchOrder;

        $tradePrice = $matchOrder->price;
        $tradeAmount = $newOrder->amount;
        $total = bcmul($tradePrice, $tradeAmount, 2);
        $commission = bcmul($total, self::COMMISSION_RATE, 2);

        $buyer = User::lockForUpdate()->find($buyOrder->user_id);
        $seller = User::lockForUpdate()->find($sellOrder->user_id);

        $this->transferAssets($buyer, $seller, $sellOrder->symbol, $tradeAmount, $total, $commission, $buyOrder, $sellOrder);

        $newOrder->update(['status' => Order::STATUS_FILLED]);
        $matchOrder->update(['status' => Order::STATUS_FILLED]);

        $trade = Trade::create([
            'buy_order_id' => $buyOrder->id,
            'sell_order_id' => $sellOrder->id,
            'buyer_id' => $buyer->id,
            'seller_id' => $seller->id,
            'symbol' => $buyOrder->symbol,
            'price' => $tradePrice,
            'amount' => $tradeAmount,
            'total' => $total,
            'commission' => $commission,
        ]);

        broadcast(new OrderMatched($trade, $buyer))->toOthers();
        broadcast(new OrderMatched($trade, $seller))->toOthers();
    }

    private function transferAssets(
        User $buyer,
        User $seller,
        string $symbol,
        string $amount,
        string $total,
        string $commission,
        Order $buyOrder,
        Order $sellOrder
    ): void {
        $buyerLocked = bcmul($buyOrder->price, $amount, 2);
        $refundToBuyer = bcsub($buyerLocked, $total, 2);

        if (bccomp($refundToBuyer, '0', 2) > 0) {
            $buyer->increment('balance', $refundToBuyer);
        }

        $sellerReceives = bcsub($total, $commission, 2);
        $seller->increment('balance', $sellerReceives);

        $sellerAsset = Asset::where('user_id', $seller->id)
            ->where('symbol', $symbol)
            ->lockForUpdate()
            ->first();
        $sellerAsset->decrement('locked_amount', $amount);

        $buyerAsset = Asset::firstOrCreate(
            ['user_id' => $buyer->id, 'symbol' => $symbol],
            ['amount' => 0, 'locked_amount' => 0]
        );
        $buyerAsset->increment('amount', $amount);
    }

    public function cancelOrder(Order $order): Order
    {
        return DB::transaction(function () use ($order) {
            $order = Order::lockForUpdate()->find($order->id);

            if (!$order->isOpen()) {
                throw new OrderNotFoundException('Order cannot be cancelled because it is not open');
            }

            $user = User::lockForUpdate()->find($order->user_id);

            if ($order->isBuy()) {
                $total = bcmul($order->price, $order->amount, 2);
                $user->increment('balance', $total);
            } else {
                $asset = Asset::where('user_id', $user->id)
                    ->where('symbol', $order->symbol)
                    ->lockForUpdate()
                    ->first();

                $asset->decrement('locked_amount', $order->amount);
                $asset->increment('amount', $order->amount);
            }

            $order->update(['status' => Order::STATUS_CANCELLED]);

            return $order;
        });
    }
}
