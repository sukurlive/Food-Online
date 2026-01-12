<?php

namespace App\Services;

use App\Repositories\ReportRepository;
use App\Http\Resources\Report\CompleteOrderResource;
use App\Http\Resources\Report\CustomerNoOrderResource;
use App\Http\Resources\Report\DailyOrderResource;
use App\Http\Resources\Report\MaxOrderCustomerResource;
use App\Http\Resources\Report\DailyAverageResource;

class ReportService
{
    protected $reportRepository;

    public function __construct(ReportRepository $reportRepository)
    {
        $this->reportRepository = $reportRepository;
    }

    /**
     * Get complete orders report
     */
    public function getCompleteOrders($paginate = 20)
    {
        try {
            $orders = $this->reportRepository->getCompleteOrders($paginate);
            return [
                'success' => true,
                'data' => CompleteOrderResource::collection($orders),
                'meta' => [
                    'total' => $orders->total(),
                    'current_page' => $orders->currentPage(),
                    'per_page' => $orders->perPage(),
                    'last_page' => $orders->lastPage()
                ]
            ];
        } catch (\Exception $e) {
            return $this->handleError($e);
        }
    }

    /**
     * Get customers with no orders
     */
    public function getCustomersWithNoOrders()
    {
        try {
            $customers = $this->reportRepository->getCustomersWithNoOrders();
            return [
                'success' => true,
                'data' => CustomerNoOrderResource::collection($customers),
                'meta' => [
                    'total_customers' => $customers->count(),
                    'percentage' => $customers->count() > 0 ? 
                        round(($customers->count() / \App\Models\Customer::count()) * 100, 2) : 0
                ]
            ];
        } catch (\Exception $e) {
            return $this->handleError($e);
        }
    }

    /**
     * Get daily orders for last 7 days
     */
    public function getDailyOrdersLast7Days()
    {
        try {
            $orders = $this->reportRepository->getDailyOrdersLast7Days();
            
            // Calculate totals
            $totalOrders = $orders->sum('total_orders');
            $totalRevenue = $orders->sum('total_revenue');
            
            return [
                'success' => true,
                'data' => DailyOrderResource::collection($orders),
                'summary' => [
                    'total_orders' => $totalOrders,
                    'total_revenue' => $totalRevenue,
                    'average_daily_orders' => round($totalOrders / max($orders->count(), 1)),
                    'average_daily_revenue' => round($totalRevenue / max($orders->count(), 1), 2)
                ]
            ];
        } catch (\Exception $e) {
            return $this->handleError($e);
        }
    }

    /**
     * Get maximum order per customer
     */
    public function getMaxOrderPerCustomer()
    {
        try {
            $customers = $this->reportRepository->getMaxOrderPerCustomer();
            
            // Calculate statistics
            $largestOrder = $customers->max('largest_order');
            $avgOrderValue = $customers->avg('largest_order');
            
            return [
                'success' => true,
                'data' => MaxOrderCustomerResource::collection($customers),
                'statistics' => [
                    'total_customers' => $customers->count(),
                    'largest_order_overall' => $largestOrder,
                    'average_largest_order' => round($avgOrderValue, 2),
                    'customers_with_multiple_orders' => $customers->where('total_orders', '>', 1)->count()
                ]
            ];
        } catch (\Exception $e) {
            return $this->handleError($e);
        }
    }

    /**
     * Get daily average vs today comparison
     */
    public function getDailyAverageVsToday()
    {
        try {
            $data = $this->reportRepository->getDailyAverageVsToday();
            
            return [
                'success' => true,
                'data' => new DailyAverageResource((object) $data)
            ];
        } catch (\Exception $e) {
            return $this->handleError($e);
        }
    }

    /**
     * Get order summary
     */
    public function getOrderSummary()
    {
        try {
            $summary = $this->reportRepository->getOrderSummary();
            
            return [
                'success' => true,
                'data' => $summary
            ];
        } catch (\Exception $e) {
            return $this->handleError($e);
        }
    }

    /**
     * Get revenue trend
     */
    public function getRevenueTrend($period = 'monthly', $limit = 12)
    {
        try {
            $trend = $this->reportRepository->getRevenueTrend($period, $limit);
            
            // Calculate growth
            $growth = [];
            if ($trend->count() > 1) {
                for ($i = 0; $i < $trend->count() - 1; $i++) {
                    $current = $trend[$i];
                    $previous = $trend[$i + 1];
                    
                    if ($previous->revenue > 0) {
                        $growthRate = (($current->revenue - $previous->revenue) / $previous->revenue) * 100;
                    } else {
                        $growthRate = $current->revenue > 0 ? 100 : 0;
                    }
                    
                    $growth[] = [
                        'period' => $period == 'monthly' ? "{$current->year}-{$current->month}" : $current->period,
                        'revenue' => $current->revenue,
                        'growth_rate' => round($growthRate, 2)
                    ];
                }
            }
            
            return [
                'success' => true,
                'data' => $trend,
                'growth_analysis' => $growth,
                'period' => $period,
                'total_periods' => $trend->count()
            ];
        } catch (\Exception $e) {
            return $this->handleError($e);
        }
    }

    /**
     * Handle errors
     */
    private function handleError(\Exception $e)
    {
        \Log::error('Report Service Error: ' . $e->getMessage(), [
            'trace' => $e->getTraceAsString()
        ]);
        
        return [
            'success' => false,
            'message' => 'Failed to generate report',
            'error' => config('app.debug') ? $e->getMessage() : null
        ];
    }
}