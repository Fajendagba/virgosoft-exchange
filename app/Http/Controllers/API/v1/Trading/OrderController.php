<?php

namespace App\Http\Controllers\API\v1\Trading;

use App\Exceptions\InsufficientAssetException;
use App\Exceptions\InsufficientBalanceException;
use App\Exceptions\OrderNotFoundException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Trading\CancelOrderRequest;
use App\Http\Requests\Trading\OrderbookRequest;
use App\Http\Requests\Trading\PlaceOrderRequest;
use App\Http\Resources\OrderResource;
use App\Models\Order;
use App\Services\OrderMatchingService;
use App\Traits\JsonResponseTrait;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class OrderController extends Controller
{
    use JsonResponseTrait;

    public function __construct(
        private OrderMatchingService $matchingService
    ) {}

    public function index(OrderbookRequest $request): JsonResponse
    {
        try {
            $orders = Order::open()
                ->bySymbol($request->validated('symbol'))
                ->orderBy('price', 'desc')
                ->get()
                ->groupBy('side');

            $response = [
                'buy_orders' => OrderResource::collection($orders->get('buy', collect())),
                'sell_orders' => OrderResource::collection($orders->get('sell', collect())->sortBy('price')->values()),
            ];

            return $this->successResponse($response, 'Orderbook retrieved successfully');
        } catch (Exception $e) {
            Log::error('Orderbook retrieval error: ' . $e->getMessage(), [
                'symbol' => $request->validated('symbol'),
            ]);
            return $this->error('Failed to retrieve orderbook. Please try again.');
        }
    }

    public function userOrders(Request $request): JsonResponse
    {
        try {
            $orders = $request->user()
                ->orders()
                ->latest()
                ->get();

            $response = [
                'orders' => OrderResource::collection($orders),
            ];

            return $this->successResponse($response, 'Your orders retrieved successfully');
        } catch (Exception $e) {
            Log::error('User orders retrieval error: ' . $e->getMessage(), [
                'user_id' => $request->user()->id,
            ]);
            return $this->error('Failed to retrieve your orders. Please try again.');
        }
    }

    public function store(PlaceOrderRequest $request): JsonResponse
    {
        try {
            $order = $this->matchingService->placeOrder(
                $request->user(),
                $request->validated()
            );

            $response = [
                'order' => OrderResource::make($order),
            ];

            return $this->successResponse(
                $response,
                'Order placed successfully',
                Response::HTTP_CREATED
            );
        } catch (InsufficientBalanceException $e) {
            return $this->error($e->getMessage(), Response::HTTP_UNPROCESSABLE_ENTITY);
        } catch (InsufficientAssetException $e) {
            return $this->error($e->getMessage(), Response::HTTP_UNPROCESSABLE_ENTITY);
        } catch (Exception $e) {
            Log::error('Order placement error: ' . $e->getMessage(), [
                'user_id' => $request->user()->id,
                'data' => $request->validated(),
            ]);
            return $this->error('Failed to place order. Please try again.');
        }
    }

    public function cancel(CancelOrderRequest $request, Order $order): JsonResponse
    {
        try {
            $cancelledOrder = $this->matchingService->cancelOrder($order);

            $response = [
                'order' => OrderResource::make($cancelledOrder),
            ];

            return $this->successResponse($response, 'Order cancelled successfully');
        } catch (OrderNotFoundException $e) {
            return $this->error($e->getMessage(), Response::HTTP_NOT_FOUND);
        } catch (Exception $e) {
            Log::error('Order cancellation error: ' . $e->getMessage(), [
                'user_id' => $request->user()->id,
                'order_id' => $order->id,
            ]);
            return $this->error('Failed to cancel order. Please try again.');
        }
    }
}
