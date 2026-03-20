<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class OrderItemResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'product'  => new ProductResource($this->whenLoaded('product')),
            'quantity' => $this->quantity,
            'price'    => number_format($this->price, 2),
            'subtotal' => number_format($this->price * $this->quantity, 2),
        ];
    }
}