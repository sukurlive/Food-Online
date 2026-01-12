<?php

namespace App\Http\Resources\Report;

use Illuminate\Http\Resources\Json\JsonResource;

class CustomerNoOrderResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'customer_id'   => $this->customer_id,
            'name'          => $this->name,
            'phone'         => $this->phone,
            'email'         => $this->email,
            'registered_at' => $this->created_at
        ];
    }
}