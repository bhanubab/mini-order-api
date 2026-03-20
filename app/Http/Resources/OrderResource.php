<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'          => $this->id,
            'status'      => $this->status,
            'total_price' => number_format($this->total_price, 2),
            'items'       => OrderItemResource::collection($this->whenLoaded('items')),
            'created_at'  => $this->created_at->toDateTimeString(),
        ];
    }
}