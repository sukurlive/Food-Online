<?php

namespace App\Services;

use App\Repositories\OrderRepository;
use App\Repositories\CustomerRepository;

class OrderService
{
    protected $orderRepository;
    protected $customerRepository;

    public function __construct(
        OrderRepository $orderRepository,
        CustomerRepository $customerRepository
    ) {
        $this->orderRepository = $orderRepository;
        $this->customerRepository = $customerRepository;
    }

    public function getAllOrders()
    {
        return $this->orderRepository->getAll();
    }

    public function getAllOrdersWithCustomer()
    {
        return $this->orderRepository->getAllWithCustomer();
    }

    public function getOrderById($id)
    {
        return $this->orderRepository->findById($id);
    }

    public function getOrderByIdWithCustomer($id)
    {
        return $this->orderRepository->findByIdWithCustomer($id);
    }

    public function getOrdersByCustomerId($customerId)
    {
        return $this->orderRepository->findByCustomerId($customerId);
    }

    public function getOrdersByStatus($status)
    {
        return $this->orderRepository->findByStatus($status);
    }

    public function createOrder($data)
    {
        // Validasi customer exists
        $customer = $this->customerRepository->findById($data['customer_id']);
        if (!$customer) {
            throw new \Exception('Customer not found', 404);
        }

        // Validate order total
        if ($data['order_total'] <= 0) {
            throw new \Exception('Order total must be greater than 0', 422);
        }

        return $this->orderRepository->create($data);
    }

    public function updateOrder($id, $data)
    {
        return $this->orderRepository->update($id, $data);
    }

    public function updateOrderStatus($id, $status)
    {
        $validStatuses = ['pending', 'paid', 'delivered', 'canceled'];
        
        if (!in_array($status, $validStatuses)) {
            throw new \Exception('Invalid order status', 422);
        }

        return $this->orderRepository->updateStatus($id, $status);
    }

    public function deleteOrder($id)
    {
        return $this->orderRepository->delete($id);
    }

    public function getRecentOrders($limit = 10)
    {
        return $this->orderRepository->getRecentOrders($limit);
    }

    public function getOrdersByCustomer($customerId)
    {
        return $this->orderRepository->getOrdersByCustomer($customerId);
    }

    public function searchOrders($keyword)
    {
        return $this->orderRepository->search($keyword);
    }

    public function getOrdersWithTotalAbove($amount)
    {
        return $this->orderRepository->getOrdersWithTotalAbove($amount);
    }

    public function getOrdersWithTotalBelow($amount)
    {
        return $this->orderRepository->getOrdersWithTotalBelow($amount);
    }

    public function getOrdersByStatusWithCustomer($status)
    {
        return $this->orderRepository->getOrdersByStatusWithCustomer($status);
    }

    public function getTodayOrders()
    {
        return $this->orderRepository->getTodayOrders();
    }

    public function getYesterdayOrders()
    {
        return $this->orderRepository->getYesterdayOrders();
    }

    public function getThisWeekOrders()
    {
        return $this->orderRepository->getThisWeekOrders();
    }

    public function getThisMonthOrders()
    {
        return $this->orderRepository->getThisMonthOrders();
    }

    public function getLastMonthOrders()
    {
        return $this->orderRepository->getLastMonthOrders();
    }

    public function countAllOrders()
    {
        return $this->orderRepository->countAll();
    }

    public function countOrdersByStatus($status)
    {
        return $this->orderRepository->countByStatus($status);
    }

    public function countOrdersByCustomer($customerId)
    {
        return $this->orderRepository->countByCustomer($customerId);
    }

    public function getTotalRevenue()
    {
        return $this->orderRepository->getTotalRevenue();
    }

    public function getTotalRevenueByStatus($status)
    {
        return $this->orderRepository->getTotalRevenueByStatus($status);
    }

    public function getTotalRevenueByCustomer($customerId)
    {
        return $this->orderRepository->getTotalRevenueByCustomer($customerId);
    }

    public function getAverageOrderValue()
    {
        return $this->orderRepository->getAverageOrderValue();
    }

    public function getAverageOrderValueByCustomer($customerId)
    {
        return $this->orderRepository->getAverageOrderValueByCustomer($customerId);
    }

    public function getOrderStatistics()
    {
        return $this->orderRepository->getOrderStatistics();
    }

    public function getOrdersByCustomerWithStatistics($customerId)
    {
        return $this->orderRepository->getOrdersByCustomerWithStatistics($customerId);
    }

    public function getDailyOrders($date = null)
    {
        return $this->orderRepository->getDailyOrders($date);
    }

    public function getDailyRevenue($date = null)
    {
        return $this->orderRepository->getDailyRevenue($date);
    }

    public function getOrdersByPeriod($period = 'today')
    {
        $validPeriods = ['today', 'yesterday', 'week', 'month', 'last_month'];
        
        if (!in_array($period, $validPeriods)) {
            throw new \Exception('Invalid period', 422);
        }

        return $this->orderRepository->getOrdersByPeriod($period);
    }

    public function getPaginatedOrders($perPage = 15, $status = null)
    {
        return $this->orderRepository->getPaginated($perPage, $status);
    }

    public function getTopCustomersByOrders($limit = 10)
    {
        return $this->orderRepository->getTopCustomersByOrders($limit);
    }

    public function getTopCustomersByRevenue($limit = 10)
    {
        return $this->orderRepository->getTopCustomersByRevenue($limit);
    }

    public function getOrdersSummary()
    {
        return $this->orderRepository->getOrdersSummary();
    }

    public function validateOrderStatusTransition($currentStatus, $newStatus)
    {
        $validTransitions = [
            'pending' => ['paid', 'canceled'],
            'paid' => ['delivered', 'canceled'],
            'delivered' => [],
            'canceled' => []
        ];

        if (!isset($validTransitions[$currentStatus])) {
            return false;
        }

        return in_array($newStatus, $validTransitions[$currentStatus]);
    }

    public function processOrderPayment($orderId)
    {
        $order = $this->orderRepository->findById($orderId);
        
        if (!$order) {
            throw new \Exception('Order not found', 404);
        }

        if ($order->status !== 'pending') {
            throw new \Exception('Order cannot be paid', 422);
        }

        return $this->orderRepository->updateStatus($orderId, 'paid');
    }

    public function processOrderDelivery($orderId)
    {
        $order = $this->orderRepository->findById($orderId);
        
        if (!$order) {
            throw new \Exception('Order not found', 404);
        }

        if ($order->status !== 'paid') {
            throw new \Exception('Order must be paid before delivery', 422);
        }

        return $this->orderRepository->updateStatus($orderId, 'delivered');
    }

    public function cancelOrder($orderId)
    {
        $order = $this->orderRepository->findById($orderId);
        
        if (!$order) {
            throw new \Exception('Order not found', 404);
        }

        if (!in_array($order->status, ['pending', 'paid'])) {
            throw new \Exception('Order cannot be canceled', 422);
        }

        return $this->orderRepository->updateStatus($orderId, 'canceled');
    }

    public function calculateOrderStatisticsByDateRange($startDate, $endDate)
    {
        $orders = $this->orderRepository->findByDateRange($startDate, $endDate);
        
        return [
            'total_orders' => $orders->count(),
            'total_revenue' => $orders->sum('order_total'),
            'average_order_value' => $orders->avg('order_total'),
            'pending_orders' => $orders->where('status', 'pending')->count(),
            'paid_orders' => $orders->where('status', 'paid')->count(),
            'delivered_orders' => $orders->where('status', 'delivered')->count(),
            'canceled_orders' => $orders->where('status', 'canceled')->count(),
            'start_date' => $startDate,
            'end_date' => $endDate
        ];
    }

    public function getRevenueTrend($days = 7)
    {
        $revenueTrend = [];
        
        for ($i = $days - 1; $i >= 0; $i--) {
            $date = now()->subDays($i)->format('Y-m-d');
            $revenue = $this->orderRepository->getDailyRevenue($date);
            
            $revenueTrend[$date] = $revenue;
        }
        
        return $revenueTrend;
    }

    public function getOrderGrowthRate()
    {
        $todayOrders = $this->orderRepository->getTodayOrders()->count();
        $yesterdayOrders = $this->orderRepository->getYesterdayOrders()->count();
        
        if ($yesterdayOrders == 0) {
            return $todayOrders > 0 ? 100 : 0;
        }
        
        return (($todayOrders - $yesterdayOrders) / $yesterdayOrders) * 100;
    }

    public function getRevenueGrowthRate()
    {
        $todayRevenue = $this->orderRepository->getDailyRevenue();
        $yesterdayRevenue = $this->orderRepository->getDailyRevenue(now()->subDay()->format('Y-m-d'));
        
        if ($yesterdayRevenue == 0) {
            return $todayRevenue > 0 ? 100 : 0;
        }
        
        return (($todayRevenue - $yesterdayRevenue) / $yesterdayRevenue) * 100;
    }

    public function getBestSellingDay()
    {
        $bestDay = null;
        $maxRevenue = 0;
        
        for ($i = 0; $i < 30; $i++) {
            $date = now()->subDays($i)->format('Y-m-d');
            $revenue = $this->orderRepository->getDailyRevenue($date);
            
            if ($revenue > $maxRevenue) {
                $maxRevenue = $revenue;
                $bestDay = $date;
            }
        }
        
        return [
            'date' => $bestDay,
            'revenue' => $maxRevenue
        ];
    }

    public function bulkCreateOrders($ordersData)
    {
        $createdOrders = [];
        $errors = [];

        foreach ($ordersData as $index => $orderData) {
            try {
                $order = $this->createOrder($orderData);
                $createdOrders[] = $order;
            } catch (\Exception $e) {
                $errors[] = [
                    'index' => $index,
                    'data' => $orderData,
                    'error' => $e->getMessage()
                ];
            }
        }

        return [
            'created' => $createdOrders,
            'errors' => $errors,
            'total_created' => count($createdOrders),
            'total_errors' => count($errors)
        ];
    }

    public function exportOrdersToArray($orderIds = null, $includeCustomer = true)
    {
        $orders = $orderIds 
            ? Order::whereIn('order_id', $orderIds)->get()
            : Order::all();

        $exportData = [];

        foreach ($orders as $order) {
            $item = [
                'order_id' => $order->order_id,
                'order_date' => $order->order_date->format('Y-m-d H:i:s'),
                'order_total' => $order->order_total,
                'status' => $order->status,
                'created_at' => $order->created_at->format('Y-m-d H:i:s')
            ];

            if ($includeCustomer && $order->customer) {
                $item['customer'] = [
                    'customer_id' => $order->customer->customer_id,
                    'name' => $order->customer->name,
                    'phone' => $order->customer->phone,
                    'email' => $order->customer->email
                ];
            }

            $exportData[] = $item;
        }

        return $exportData;
    }
}