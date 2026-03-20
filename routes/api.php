<?php


use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\OrderController;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login',    [AuthController::class, 'login']);

Route::middleware('auth:api')->group(function () {
    Route::post('/logout',           [AuthController::class, 'logout']);
    Route::apiResource('products',   ProductController::class);
    Route::post('/orders',           [OrderController::class, 'store']);
    Route::get('/orders',            [OrderController::class, 'index']);
    Route::get('/orders/{id}',       [OrderController::class, 'show']);
});

Route::get('/test-email', function () {
    $order = \App\Models\Order::with('items.product', 'user')->first();

    if (!$order) {
        return response()->json(['error' => 'No orders found — place an order first']);
    }

    \Illuminate\Support\Facades\Mail::to($order->user->email)
        ->send(new \App\Mail\OrderPlaced($order));

    return response()->json(['message' => 'Email sent to ' . $order->user->email]);
});