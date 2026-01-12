<?php

namespace App\Http\Resources\Report;

use Illuminate\Http\Resources\Json\JsonResource;

class DailyAverageResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'today' => [
                'date' => $this->today['date'],
                'orders' => (int) $this->today['orders'],
                'revenue' => (float) $this->today['revenue'],
                'average_order_value' => (float) $this->today['average_order_value']
            ],
            'last_30_days_average' => [
                'period' => $this->last_30_days_average['period'],
                'orders' => (float) $this->last_30_days_average['orders'],
                'revenue' => (float) $this->last_30_days_average['revenue'],
                'average_order_value' => (float) $this->last_30_days_average['average_order_value']
            ],
            'comparison' => [
                'orders_difference' => (float) $this->comparison['orders_difference'],
                'revenue_difference' => (float) $this->comparison['revenue_difference'],
                'percentage_change' => (float) $this->comparison['percentage_change'],
                'trend' => $this->comparison['orders_difference'] > 0 ? 'up' : 'down'
            ]
        ];
    }
}