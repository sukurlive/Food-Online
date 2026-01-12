<?php

namespace App\Http\Resources\Report;

use Illuminate\Http\Resources\Json\JsonResource;

class CompleteOrderResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'order_id'      => $this->order_id,
            'order_date'    => $this->order_date,
            'order_total'   => (float) $this->order_total,
            'status'        => $this->status,
            'customer'      => [
                'name'  => $this->customer_name,
                'phone' => $this->customer_phone,
                'email' => $this->customer_email
            ]
        ];
    }
}