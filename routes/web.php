<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});
Route::get('/preview-email', function () {
    $order = \App\Models\Order::with('items.product', 'user')->first();
    return new \App\Mail\OrderPlaced($order);
});
