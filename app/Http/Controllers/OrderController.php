<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Product;
use App\Jobs\ProcessOrder;
use App\Http\Requests\StoreOrderRequest;
use App\Http\Resources\OrderResource;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    public function store(StoreOrderRequest $request)
    {
        $order = DB::transaction(function () use ($request) {
            $totalPrice = 0;
            $orderItems = [];

            foreach ($request->items as $item) {
                $product = Product::lockForUpdate()->findOrFail($item['product_id']);

                if ($product->stock < $item['quantity']) {
                    abort(422, "Insufficient stock for: {$product->name}");
                }

                $product->decrement('stock', $item['quantity']);
                $totalPrice += $product->price * $item['quantity'];

                $orderItems[] = [
                    'product_id' => $product->id,
                    'quantity'   => $item['quantity'],
                    'price'      => $product->price,
                ];
            }

            $order = Order::create([
                'user_id'     => auth()->id(),
                'total_price' => $totalPrice,
                'status'      => Order::STATUS_PENDING,
            ]);

            $order->items()->createMany($orderItems);

            return $order;
        });

        // Dispatch job to queue AFTER transaction commits
        ProcessOrder::dispatch($order);

        return new OrderResource($order->load('items.product'));
    }

    public function index()
    {
        $orders = Order::where('user_id', auth()->id())
                       ->with('items.product')
                       ->latest()
                       ->get();

        return OrderResource::collection($orders);
    }

    public function show($id)
    {
        $order = Order::where('user_id', auth()->id())
                      ->with('items.product')
                      ->findOrFail($id);

        return new OrderResource($order);
    }
}