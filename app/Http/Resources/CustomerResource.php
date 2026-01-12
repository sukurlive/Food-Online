<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CustomerResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'customer_id'   => $this->customer_id,
            'name'          => $this->name,
            'phone'         => $this->phone,
            'email'         => $this->email,
            'created_at'    => $this->created_at->format('Y-m-d H:i:s'),
            'updated_at'    => $this->updated_at->format('Y-m-d H:i:s'),
            'total_orders'  => $this->whenLoaded('orders', function() {
                return $this->orders->count();
            }),
            'total_spent'   => $this->whenLoaded('orders', function() {
                return $this->orders->sum('order_total');
            }),
            'orders'        => OrderResource::collection($this->whenLoaded('orders')),
            'links'         => [
                'self' => route('customers.show', $this->customer_id),
                //'orders' => route('customers.orders', $this->customer_id),
            ]
        ];
    }
}