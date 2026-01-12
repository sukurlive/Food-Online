<?php

namespace App\Repositories;

use App\Models\Customer;
use App\Models\Order;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ReportRepository
{
    /**
     * Get complete orders with customer details (Soal 2e.1)
     */
    public function getCompleteOrders($paginate = 20)
    {
        return Order::select([
                'orders.order_id',
                'orders.order_date',
                'orders.order_total',
                'orders.status',
                'customers.name as customer_name',
                'customers.phone as customer_phone',
                'customers.email as customer_email'
            ])
            ->join('customers', 'orders.customer_id', '=', 'customers.customer_id')
            ->orderBy('orders.order_date', 'DESC')
            ->paginate($paginate);
    }

    /**
     * Get customers with no orders (Soal 2e.2)
     */
    public function getCustomersWithNoOrders()
    {
        return Customer::select([
                'customers.customer_id',
                'customers.name',
                'customers.phone',
                'customers.email',
                'customers.created_at'
            ])
            ->leftJoin('orders', 'customers.customer_id', '=', 'orders.customer_id')
            ->whereNull('orders.order_id')
            ->get();
    }

    /**
     * Get daily order count for last 7 days (Soal 2e.3)
     */
    public function getDailyOrdersLast7Days()
    {
        $startDate = Carbon::now()->subDays(6)->startOfDay();
        $endDate = Carbon::now()->endOfDay();

        return Order::select([
                DB::raw('DATE(order_date) as date'),
                DB::raw('COUNT(*) as total_orders'),
                DB::raw('SUM(order_total) as total_revenue'),
                DB::raw('AVG(order_total) as avg_order_value')
            ])
            ->whereBetween('order_date', [$startDate, $endDate])
            ->groupBy(DB::raw('DATE(order_date)'))
            ->orderBy('date', 'DESC')
            ->get();
    }

    /**
     * Get maximum order per customer (Soal 2e.4)
     */
    public function getMaxOrderPerCustomer()
    {
        return Order::select([
                'customers.customer_id',
                'customers.name',
                'customers.phone',
                DB::raw('MAX(orders.order_total) as largest_order'),
                DB::raw('COUNT(orders.order_id) as total_orders'),
                DB::raw('SUM(orders.order_total) as total_spent')
            ])
            ->join('customers', 'orders.customer_id', '=', 'customers.customer_id')
            ->groupBy('customers.customer_id', 'customers.name', 'customers.phone')
            ->orderBy('largest_order', 'DESC')
            ->get();
    }

    /**
     * Get daily average vs today's orders (Soal 2e.5)
     */
    public function getDailyAverageVsToday()
    {
        $today = Carbon::today();
        
        // Get today's statistics
        $todayStats = Order::select([
                DB::raw('COUNT(*) as today_orders'),
                DB::raw('SUM(order_total) as today_revenue'),
                DB::raw('AVG(order_total) as today_avg')
            ])
            ->whereDate('order_date', $today)
            ->first();

        // Get average of last 30 days (excluding today)
        $last30DaysStats = Order::select([
                DB::raw('AVG(daily_orders) as avg_orders'),
                DB::raw('AVG(daily_revenue) as avg_revenue'),
                DB::raw('AVG(daily_avg) as avg_order_value')
            ])
            ->fromSub(function ($query) {
                $query->select([
                    DB::raw('DATE(order_date) as date'),
                    DB::raw('COUNT(*) as daily_orders'),
                    DB::raw('SUM(order_total) as daily_revenue'),
                    DB::raw('AVG(order_total) as daily_avg')
                ])
                ->from('orders')
                ->whereDate('order_date', '>=', Carbon::now()->subDays(30))
                ->whereDate('order_date', '<', Carbon::today())
                ->groupBy(DB::raw('DATE(order_date)'));
            }, 'daily_stats')
            ->first();

        return [
            'today' => [
                'orders' => $todayStats->today_orders ?? 0,
                'revenue' => $todayStats->today_revenue ?? 0,
                'average_order_value' => $todayStats->today_avg ?? 0,
                'date' => $today->format('Y-m-d')
            ],
            'last_30_days_average' => [
                'orders' => $last30DaysStats->avg_orders ?? 0,
                'revenue' => $last30DaysStats->avg_revenue ?? 0,
                'average_order_value' => $last30DaysStats->avg_order_value ?? 0,
                'period' => 'last_30_days'
            ],
            'comparison' => [
                'orders_difference' => ($todayStats->today_orders ?? 0) - ($last30DaysStats->avg_orders ?? 0),
                'revenue_difference' => ($todayStats->today_revenue ?? 0) - ($last30DaysStats->avg_revenue ?? 0),
                'percentage_change' => $last30DaysStats->avg_orders > 0 
                    ? (($todayStats->today_orders ?? 0) - $last30DaysStats->avg_orders) / $last30DaysStats->avg_orders * 100 
                    : 0
            ]
        ];
    }

    /**
     * Get order statistics summary
     */
    public function getOrderSummary()
    {
        return [
            'total_orders' => Order::count(),
            'total_revenue' => Order::sum('order_total'),
            'average_order_value' => Order::avg('order_total'),
            'pending_orders' => Order::where('status', 'pending')->count(),
            'paid_orders' => Order::where('status', 'paid')->count(),
            'delivered_orders' => Order::where('status', 'delivered')->count(),
            'cancelled_orders' => Order::where('status', 'cancelled')->count(),
            'today_orders' => Order::whereDate('order_date', Carbon::today())->count(),
            'this_month_orders' => Order::whereMonth('order_date', Carbon::now()->month)
                ->whereYear('order_date', Carbon::now()->year)
                ->count(),
            'unique_customers' => Order::distinct('customer_id')->count('customer_id')
        ];
    }

    /**
     * Get revenue trend by period
     */
    public function getRevenueTrend($period = 'monthly', $limit = 12)
    {
        $query = Order::query();
        
        switch ($period) {
            case 'daily':
                $query->select([
                    DB::raw('DATE(order_date) as period'),
                    DB::raw('SUM(order_total) as revenue'),
                    DB::raw('COUNT(*) as orders')
                ])
                ->groupBy(DB::raw('DATE(order_date)'))
                ->orderBy('period', 'DESC')
                ->limit($limit);
                break;
                
            case 'weekly':
                $query->select([
                    DB::raw('YEAR(order_date) as year'),
                    DB::raw('WEEK(order_date) as week'),
                    DB::raw('SUM(order_total) as revenue'),
                    DB::raw('COUNT(*) as orders')
                ])
                ->groupBy(DB::raw('YEAR(order_date)'), DB::raw('WEEK(order_date)'))
                ->orderBy('year', 'DESC')
                ->orderBy('week', 'DESC')
                ->limit($limit);
                break;
                
            case 'monthly':
            default:
                $query->select([
                    DB::raw('YEAR(order_date) as year'),
                    DB::raw('MONTH(order_date) as month'),
                    DB::raw('SUM(order_total) as revenue'),
                    DB::raw('COUNT(*) as orders')
                ])
                ->groupBy(DB::raw('YEAR(order_date)'), DB::raw('MONTH(order_date)'))
                ->orderBy('year', 'DESC')
                ->orderBy('month', 'DESC')
                ->limit($limit);
                break;
        }
        
        return $query->get();
    }
}