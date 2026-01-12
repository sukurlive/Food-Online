<?php

namespace App\Repositories;

use App\Models\Order;
use App\Models\Customer;
use Illuminate\Support\Facades\DB;

class OrderRepository
{
    public function getAll()
    {
        return Order::all();
    }

    public function getAllWithCustomer()
    {
        return Order::with('customer')->get();
    }

    public function findById($id)
    {
        return Order::findOrFail($id);
    }

    public function findByIdWithCustomer($id)
    {
        return Order::with('customer')->findOrFail($id);
    }

    public function findByCustomerId($customerId)
    {
        return Order::where('customer_id', $customerId)->get();
    }

    public function findByStatus($status)
    {
        return Order::where('status', $status)->get();
    }

    public function findByDateRange($startDate, $endDate)
    {
        return Order::whereBetween('order_date', [$startDate, $endDate])->get();
    }

    public function create($data)
    {
        return Order::create([
            'customer_id' => $data['customer_id'],
            'order_date' => now(),
            'order_total' => $data['order_total'],
            'status' => $data['status'] ?? 'pending'
        ]);
    }

    public function update($id, $data)
    {
        $order = Order::findOrFail($id);
        $order->update($data);
        
        return $order;
    }

    public function updateStatus($id, $status)
    {
        $order = Order::findOrFail($id);
        $order->update(['status' => $status]);
        
        return $order;
    }

    public function delete($id)
    {
        $order = Order::findOrFail($id);
        return $order->delete();
    }

    public function getRecentOrders($limit = 10)
    {
        return Order::with('customer')
            ->orderBy('order_date', 'desc')
            ->limit($limit)
            ->get();
    }

    public function getOrdersByCustomer($customerId)
    {
        return Order::where('customer_id', $customerId)
            ->with('customer')
            ->orderBy('order_date', 'desc')
            ->get();
    }

    public function search($keyword)
    {
        return Order::whereHas('customer', function($query) use ($keyword) {
            $query->where('name', 'like', "%{$keyword}%")
                  ->orWhere('phone', 'like', "%{$keyword}%")
                  ->orWhere('email', 'like', "%{$keyword}%");
        })->orWhere('order_id', 'like', "%{$keyword}%")
          ->with('customer')
          ->get();
    }

    public function getOrdersWithTotalAbove($amount)
    {
        return Order::where('order_total', '>', $amount)
            ->with('customer')
            ->get();
    }

    public function getOrdersWithTotalBelow($amount)
    {
        return Order::where('order_total', '<', $amount)
            ->with('customer')
            ->get();
    }

    public function getOrdersByStatusWithCustomer($status)
    {
        return Order::where('status', $status)
            ->with('customer')
            ->orderBy('order_date', 'desc')
            ->get();
    }

    public function getTodayOrders()
    {
        return Order::whereDate('order_date', today())
            ->with('customer')
            ->get();
    }

    public function getYesterdayOrders()
    {
        return Order::whereDate('order_date', today()->subDay())
            ->with('customer')
            ->get();
    }

    public function getThisWeekOrders()
    {
        return Order::whereBetween('order_date', [
            now()->startOfWeek(),
            now()->endOfWeek()
        ])->with('customer')->get();
    }

    public function getThisMonthOrders()
    {
        return Order::whereBetween('order_date', [
            now()->startOfMonth(),
            now()->endOfMonth()
        ])->with('customer')->get();
    }

    public function getLastMonthOrders()
    {
        return Order::whereBetween('order_date', [
            now()->subMonth()->startOfMonth(),
            now()->subMonth()->endOfMonth()
        ])->with('customer')->get();
    }

    public function countAll()
    {
        return Order::count();
    }

    public function countByStatus($status)
    {
        return Order::where('status', $status)->count();
    }

    public function countByCustomer($customerId)
    {
        return Order::where('customer_id', $customerId)->count();
    }

    public function getTotalRevenue()
    {
        return Order::sum('order_total');
    }

    public function getTotalRevenueByStatus($status)
    {
        return Order::where('status', $status)->sum('order_total');
    }

    public function getTotalRevenueByCustomer($customerId)
    {
        return Order::where('customer_id', $customerId)->sum('order_total');
    }

    public function getTotalRevenueByDateRange($startDate, $endDate)
    {
        return Order::whereBetween('order_date', [$startDate, $endDate])
            ->sum('order_total');
    }

    public function getAverageOrderValue()
    {
        return Order::avg('order_total');
    }

    public function getAverageOrderValueByCustomer($customerId)
    {
        return Order::where('customer_id', $customerId)->avg('order_total');
    }

    public function getMaxOrderValue()
    {
        return Order::max('order_total');
    }

    public function getMinOrderValue()
    {
        return Order::min('order_total');
    }

    public function getOrderStatistics()
    {
        return [
            'total_orders' => $this->countAll(),
            'total_revenue' => $this->getTotalRevenue(),
            'average_order_value' => $this->getAverageOrderValue(),
            'max_order_value' => $this->getMaxOrderValue(),
            'min_order_value' => $this->getMinOrderValue(),
            'pending_orders' => $this->countByStatus('pending'),
            'paid_orders' => $this->countByStatus('paid'),
            'delivered_orders' => $this->countByStatus('delivered'),
            'canceled_orders' => $this->countByStatus('canceled')
        ];
    }

    public function getOrdersByCustomerWithStatistics($customerId)
    {
        $orders = $this->getOrdersByCustomer($customerId);
        
        return [
            'customer' => Customer::find($customerId),
            'orders' => $orders,
            'statistics' => [
                'total_orders' => $orders->count(),
                'total_spent' => $orders->sum('order_total'),
                'average_order_value' => $orders->avg('order_total'),
                'pending_orders' => $orders->where('status', 'pending')->count(),
                'paid_orders' => $orders->where('status', 'paid')->count(),
                'delivered_orders' => $orders->where('status', 'delivered')->count(),
                'canceled_orders' => $orders->where('status', 'canceled')->count()
            ]
        ];
    }

    public function getDailyOrders($date = null)
    {
        $date = $date ?: today();
        
        return Order::whereDate('order_date', $date)
            ->with('customer')
            ->orderBy('order_date', 'desc')
            ->get();
    }

    public function getDailyRevenue($date = null)
    {
        $date = $date ?: today();
        
        return Order::whereDate('order_date', $date)->sum('order_total');
    }

    public function getOrdersByPeriod($period = 'today')
    {
        $query = Order::with('customer');
        
        switch ($period) {
            case 'today':
                $query->whereDate('order_date', today());
                break;
            case 'yesterday':
                $query->whereDate('order_date', today()->subDay());
                break;
            case 'week':
                $query->whereBetween('order_date', [
                    now()->startOfWeek(),
                    now()->endOfWeek()
                ]);
                break;
            case 'month':
                $query->whereBetween('order_date', [
                    now()->startOfMonth(),
                    now()->endOfMonth()
                ]);
                break;
            case 'last_month':
                $query->whereBetween('order_date', [
                    now()->subMonth()->startOfMonth(),
                    now()->subMonth()->endOfMonth()
                ]);
                break;
        }
        
        return $query->orderBy('order_date', 'desc')->get();
    }

    public function getPaginated($perPage = 15, $status = null)
    {
        $query = Order::with('customer');
        
        if ($status) {
            $query->where('status', $status);
        }
        
        return $query->orderBy('order_date', 'desc')
            ->paginate($perPage);
    }

    public function getTopCustomersByOrders($limit = 10)
    {
        return Order::select('customer_id', DB::raw('COUNT(*) as order_count'))
            ->with('customer')
            ->groupBy('customer_id')
            ->orderBy('order_count', 'desc')
            ->limit($limit)
            ->get();
    }

    public function getTopCustomersByRevenue($limit = 10)
    {
        return Order::select('customer_id', DB::raw('SUM(order_total) as total_revenue'))
            ->with('customer')
            ->groupBy('customer_id')
            ->orderBy('total_revenue', 'desc')
            ->limit($limit)
            ->get();
    }

    public function bulkCreate($ordersData)
    {
        return DB::transaction(function() use ($ordersData) {
            $orders = [];
            foreach ($ordersData as $data) {
                $orders[] = $this->create($data);
            }
            return $orders;
        });
    }

    public function bulkUpdate($ids, $data)
    {
        return DB::transaction(function() use ($ids, $data) {
            return Order::whereIn('order_id', $ids)->update($data);
        });
    }

    public function bulkDelete($ids)
    {
        return DB::transaction(function() use ($ids) {
            return Order::whereIn('order_id', $ids)->delete();
        });
    }

    public function getOrdersSummary()
    {
        return [
            'total_orders' => $this->countAll(),
            'total_revenue' => $this->getTotalRevenue(),
            'today_orders' => $this->getTodayOrders()->count(),
            'today_revenue' => $this->getDailyRevenue(),
            'pending_orders' => $this->countByStatus('pending'),
            'average_order_value' => $this->getAverageOrderValue()
        ];
    }
}