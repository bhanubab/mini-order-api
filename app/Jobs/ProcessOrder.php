<?php

namespace App\Jobs;

use App\Mail\OrderPlaced;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class ProcessOrder implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries   = 3;
    public int $timeout = 60;

    public function __construct(public Order $order) {}

    public function handle(): void
    {
        Log::info("Processing order #{$this->order->id}");

        try {
            DB::transaction(function () {

                $this->order->load('items.product', 'user');

                // Verify stock still available
                foreach ($this->order->items as $item) {
                    $product = Product::lockForUpdate()->findOrFail($item->product_id);

                    if ($product->stock < $item->quantity) {
                        $this->order->update(['status' => 'failed']);
                        Log::warning("Order #{$this->order->id} failed — insufficient stock for {$product->name}");
                        return;
                    }
                }

                // Mark as processing
                $this->order->update(['status' => 'processing']);

                // Simulate processing
                sleep(2);

                // Mark as completed
                $this->order->update(['status' => 'completed']);

                // Send confirmation email
                Mail::to($this->order->user->email)
                    ->send(new OrderPlaced($this->order));

                Log::info("Order #{$this->order->id} completed — email sent to {$this->order->user->email}");
            });

        } catch (\Exception $e) {
            Log::error("Order #{$this->order->id} failed: " . $e->getMessage());
            $this->order->update(['status' => 'failed']);
            throw $e;
        }
    }

    public function failed(\Throwable $exception): void
    {
        Log::error("Order #{$this->order->id} permanently failed: " . $exception->getMessage());
        $this->order->update(['status' => 'failed']);
    }
}