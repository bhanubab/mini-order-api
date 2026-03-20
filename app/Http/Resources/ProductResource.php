<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'          => $this->id,
            'name'        => $this->name,
            'description' => $this->description,
            'price'       => number_format($this->price, 2),
            'stock'       => $this->stock,
            'created_at'  => $this->created_at->toDateTimeString(),
        ];
    }
}