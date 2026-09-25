<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
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
            'table_id' => $this->table_id,
            'status' => $this->status,
            'total_price' => $this->total_price,
            'paid_at' => $this->paid_at,
            'order_items' => $this->whenLoaded('orderItems'),
            'table' => $this->whenLoaded('table'),
            'history' => $this->whenLoaded('orderStatusHistories'),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
