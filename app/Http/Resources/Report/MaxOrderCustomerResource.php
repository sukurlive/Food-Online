<?php

namespace App\Http\Resources\Report;

use Illuminate\Http\Resources\Json\JsonResource;


class MaxOrderCustomerResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'customer_id'   => $this->customer_id,
            'name'          => $this->name,
            'phone'         => $this->phone,
            'largest_order' => (float) $this->largest_order,
            'total_orders'  => (int) $this->total_orders,
            'total_spent'   => (float) $this->total_spent
        ];
    }
}