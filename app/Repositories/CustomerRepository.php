<?php

namespace App\Repositories;

use App\Models\Customer;
use App\Models\Order;
use Illuminate\Support\Facades\DB;

class CustomerRepository
{
    public function getAll()
    {
        return Customer::all();
    }

    public function getAllWithOrders()
    {
        return Customer::with('orders')->get();
    }

    public function getAllWithOrdersCount()
    {
        return Customer::withCount('orders')->get();
    }

    public function findById($id)
    {
        return Customer::findOrFail($id);
    }

    public function findByIdWithOrders($id)
    {
        return Customer::with('orders')->findOrFail($id);
    }

    public function findByPhone($phone)
    {
        return Customer::where('phone', $phone)->first();
    }

    public function findByEmail($email)
    {
        return Customer::where('email', $email)->first();
    }

    public function create($data)
    {
        return Customer::create([
            'name'       => $data['name'],
            'phone'      => $data['phone'],
            'email'      => $data['email'],
            'created_at' => now()
        ]);
    }

    public function update($id, $data)
    {
        $customer = Customer::findOrFail($id);
        $customer->update($data);
        
        return $customer;
    }

    public function delete($id)
    {
        $customer = Customer::findOrFail($id);
        return $customer->delete();
    }

    public function getCustomersWithNoOrders()
    {
        return Customer::whereDoesntHave('orders')->get();
    }

    public function getCustomersWithOrders()
    {
        return Customer::whereHas('orders')->get();
    }

    public function getRecentCustomers($limit = 10)
    {
        return Customer::orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    public function search($keyword)
    {
        return Customer::where('name', 'like', "%{$keyword}%")
            ->orWhere('phone', 'like', "%{$keyword}%")
            ->orWhere('email', 'like', "%{$keyword}%")
            ->get();
    }

    public function getCustomersByOrderStatus($status)
    {
        return Customer::whereHas('orders', function($query) use ($status) {
            $query->where('status', $status);
        })->get();
    }

    public function getTopSpendingCustomers($limit = 10)
    {
        return Customer::withSum('orders', 'order_total')
            ->orderBy('orders_sum_order_total', 'desc')
            ->limit($limit)
            ->get();
    }

    public function getCustomerStats($customerId)
    {
        $customer = $this->findByIdWithOrders($customerId);
        
        $orders = $customer->orders;

        return [
            'total_orders'      => $orders->count(),
            'total_spent'       => $orders->sum('order_total'),
            'avg_order_value'   => $orders->avg('order_total'),
            'first_order_date'  => $orders->min('order_date'),
            'last_order_date'   => $orders->max('order_date'),
            'status_counts'     => $orders->groupBy('status')->map->count()
        ];
    }

    public function getCustomersWithOrdersBetween($startDate, $endDate)
    {
        return Customer::whereHas('orders', function($query) use ($startDate, $endDate) {
            $query->whereBetween('order_date', [$startDate, $endDate]);
        })->with(['orders' => function($query) use ($startDate, $endDate) {
            $query->whereBetween('order_date', [$startDate, $endDate])
                  ->orderBy('order_date', 'desc');
        }])->get();
    }

    public function getCustomersOrderCountBetween($startDate, $endDate)
    {
        return Customer::withCount(['orders' => function($query) use ($startDate, $endDate) {
            $query->whereBetween('order_date', [$startDate, $endDate]);
        }])->orderBy('orders_count', 'desc')
          ->get();
    }

    public function countAll()
    {
        return Customer::count();
    }

    public function countWithOrders()
    {
        return Customer::whereHas('orders')->count();
    }

    public function countWithoutOrders()
    {
        return Customer::whereDoesntHave('orders')->count();
    }

    public function getPaginated($perPage = 15)
    {
        return Customer::withCount('orders')
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    public function bulkCreate($customersData)
    {
        return DB::transaction(function() use ($customersData) {
            $customers = [];
            foreach ($customersData as $data) {
                $customers[] = $this->create($data);
            }
            return $customers;
        });
    }

    public function bulkUpdate($ids, $data)
    {
        return DB::transaction(function() use ($ids, $data) {
            return Customer::whereIn('customer_id', $ids)->update($data);
        });
    }

    public function bulkDelete($ids)
    {
        return DB::transaction(function() use ($ids) {
            return Customer::whereIn('customer_id', $ids)->delete();
        });
    }

    public function getCustomersWithTotalOrderAmount()
    {
        return Customer::select('customers.*')
            ->selectSub(function($query) {
                $query->selectRaw('COALESCE(SUM(orders.order_total), 0)')
                    ->from('orders')
                    ->whereColumn('orders.customer_id', 'customers.customer_id');
            }, 'total_order_amount')
            ->orderBy('total_order_amount', 'desc')
            ->get();
    }

    public function getCustomerOrdersSummary($customerId)
    {
        $customer = $this->findByIdWithOrders($customerId);
        
        if (!$customer) {
            return null;
        }

        return [
            'customer' => $customer,
            'orders_summary' => [
                'total_orders'          => $customer->orders->count(),
                'total_amount'          => $customer->orders->sum('order_total'),
                'pending_orders'        => $customer->orders->where('status', 'pending')->count(),
                'delivered_orders'      => $customer->orders->where('status', 'delivered')->count(),
                'average_order_value'   => $customer->orders->avg('order_total'),
                'last_order_date'       => $customer->orders->max('order_date'),
                'first_order_date'      => $customer->orders->min('order_date')
            ]
        ];
    }
}