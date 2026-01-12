<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'order_id'      => $this->order_id,
            'customer_id'   => $this->customer_id,
            'order_date'    => $this->order_date->format('Y-m-d H:i:s'),
            'order_total'   => (float) $this->order_total,
            'status'        => $this->status,
            'created_at'    => $this->created_at->format('Y-m-d H:i:s'),
            'updated_at'    => $this->updated_at->format('Y-m-d H:i:s'),
            'customer'      => new CustomerResource($this->whenLoaded('customer')),
            'links'         => [
                'self'          => route('orders.show', $this->order_id),
                //'customer'      => route('customers.show', $this->customer_id),
                //'update_status' => route('orders.status.update', $this->order_id)
            ]
        ];
    }
}