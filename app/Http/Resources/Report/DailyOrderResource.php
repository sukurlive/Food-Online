<?php

namespace App\Http\Resources\Report;

use Illuminate\Http\Resources\Json\JsonResource;

class DailyOrderResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'date'                  => $this->date,
            'total_orders'          => (int) $this->total_orders,
            'total_revenue'         => (float) $this->total_revenue,
            'average_order_value'   => (float) $this->avg_order_value
        ];
    }
}