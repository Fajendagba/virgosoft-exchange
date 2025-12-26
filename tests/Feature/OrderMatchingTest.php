<?php

namespace Tests\Feature;

use App\Models\Asset;
use App\Models\Order;
use App\Models\Trade;
use App\Models\User;
use App\Services\OrderMatchingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderMatchingTest extends TestCase
{
    use RefreshDatabase;

    private OrderMatchingService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(OrderMatchingService::class);
    }

    public function test_buy_order_matches_with_sell_order(): void
    {
        $seller = User::factory()->create(['balance' => 0]);
        Asset::create([
            'user_id' => $seller->id,
            'symbol' => 'BTC',
            'amount' => 1.0,
            'locked_amount' => 0,
        ]);

        $buyer = User::factory()->create(['balance' => 100000]);

        $this->service->placeOrder($seller, [
            'symbol' => 'BTC',
            'side' => 'sell',
            'price' => 95000,
            'amount' => 0.5,
        ]);

        $this->service->placeOrder($buyer, [
            'symbol' => 'BTC',
            'side' => 'buy',
            'price' => 95000,
            'amount' => 0.5,
        ]);

        $this->assertDatabaseCount('trades', 1);
        $this->assertEquals(2, Order::where('status', Order::STATUS_FILLED)->count());

        $trade = Trade::first();
        $total = 95000 * 0.5;
        $commission = $total * 0.015;

        $seller->refresh();
        $buyer->refresh();

        $this->assertEquals(bcadd(0, bcsub($total, $commission, 2), 2), $seller->balance);
        $this->assertEquals(bcsub(100000, $total, 2), $buyer->balance);

        $this->assertDatabaseHas('assets', [
            'user_id' => $buyer->id,
            'symbol' => 'BTC',
            'amount' => 0.5,
        ]);
    }

    public function test_order_cancellation_refunds_funds(): void
    {
        $user = User::factory()->create(['balance' => 10000]);

        $order = $this->service->placeOrder($user, [
            'symbol' => 'BTC',
            'side' => 'buy',
            'price' => 1000,
            'amount' => 1,
        ]);

        $user->refresh();
        $this->assertEquals(9000, $user->balance);

        $this->service->cancelOrder($order);

        $user->refresh();
        $this->assertEquals(10000, $user->balance);
    }

    public function test_insufficient_balance_throws_exception(): void
    {
        $this->expectException(\App\Exceptions\InsufficientBalanceException::class);
        $this->expectExceptionMessage('Insufficient USD balance to place this order');

        $user = User::factory()->create(['balance' => 100]);

        $this->service->placeOrder($user, [
            'symbol' => 'BTC',
            'side' => 'buy',
            'price' => 95000,
            'amount' => 1,
        ]);
    }

    public function test_insufficient_asset_throws_exception(): void
    {
        $this->expectException(\App\Exceptions\InsufficientAssetException::class);

        $user = User::factory()->create(['balance' => 10000]);

        $this->service->placeOrder($user, [
            'symbol' => 'BTC',
            'side' => 'sell',
            'price' => 95000,
            'amount' => 1,
        ]);
    }
}
