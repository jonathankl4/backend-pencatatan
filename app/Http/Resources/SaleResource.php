<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SaleResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'sale_code' => $this->sale_code,
            'total_cost' => (float) $this->total_cost,
            'total_revenue' => (float) $this->total_revenue,
            'gross_profit' => (float) $this->gross_profit,
            'notes' => $this->notes,
            'sale_date' => $this->sale_date->toDateString(),
            'created_at' => $this->created_at,
            'items' => $this->whenLoaded('items', function () {
                return $this->items->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'product_id' => $item->product_id,
                        'product_name' => $item->product_name,
                        'cost_price' => (float) $item->cost_price,
                        'sell_price' => (float) $item->sell_price,
                        'quantity' => $item->quantity,
                        'subtotal_cost' => (float) $item->subtotal_cost,
                        'subtotal_revenue' => (float) $item->subtotal_revenue,
                        'subtotal_profit' => (float) $item->subtotal_profit,
                    ];
                });
            }),
        ];
    }
}
