<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\BaseController;
use App\Http\Requests\Api\Order\CreateOrderRequest;
use App\Http\Requests\Api\Order\UpdateOrderRequest;
use App\Http\Requests\Api\Order\UpdateOrderStatusRequest;
use App\Http\Resources\OrderResource;
use App\Services\OrderService;
use Illuminate\Http\Request;

class OrderController extends BaseController
{
    protected $orderService;

    public function __construct(OrderService $orderService)
    {
        $this->orderService = $orderService;
    }

    public function index(Request $request)
    {
        $perPage = $request->get('per_page', 15);
        $status = $request->get('status');
        
        $orders = $this->orderService->getPaginatedOrders($perPage, $status);
        
        return $this->sendResponse($orders, 'Orders retrieved successfully.');
    }

    public function store(CreateOrderRequest $request)
    {
        try {
            $data = $request->validated();
            $order = $this->orderService->createOrder($data);
            
            return $this->sendResponse(new OrderResource($order), 'Order created successfully.', 201);
        } catch (\Exception $e) {
            return $this->sendError($e->getMessage(), [], 422);
        }
    }

    public function show($id)
    {
        try {
            $order = $this->orderService->getOrderByIdWithCustomer($id);
            return $this->sendResponse(new OrderResource($order), 'Order retrieved successfully.');
        } catch (\Exception $e) {
            return $this->sendError('Order not found.', [], 404);
        }
    }

    public function update(UpdateOrderRequest $request, $id)
    {
        try {
            $order = $this->orderService->updateOrder($id, $request->validated());
            return $this->sendResponse(new OrderResource($order), 'Order updated successfully.');
        } catch (\Exception $e) {
            return $this->sendError($e->getMessage(), [], 422);
        }
    }

    public function destroy($id)
    {
        try {
            $this->orderService->deleteOrder($id);
            return $this->sendResponse([], 'Order deleted successfully.');
        } catch (\Exception $e) {
            return $this->sendError('Order not found.', [], 404);
        }
    }

    public function updateStatus(UpdateOrderStatusRequest $request, $id)
    {
        try {
            $order = $this->orderService->updateOrderStatus($id, $request->status);
            return $this->sendResponse(new OrderResource($order), 'Order status updated successfully.');
        } catch (\Exception $e) {
            return $this->sendError($e->getMessage(), [], 422);
        }
    }

    public function search(Request $request)
    {
        $request->validate([
            'keyword' => 'required|string|min:2'
        ]);

        $orders = $this->orderService->searchOrders($request->keyword);
        return $this->sendResponse(OrderResource::collection($orders), 'Search results retrieved successfully.');
    }

    public function statistics()
    {
        $statistics = $this->orderService->getOrderStatistics();
        return $this->sendResponse($statistics, 'Order statistics retrieved successfully.');
    }

    public function todayOrders()
    {
        $orders = $this->orderService->getTodayOrders();
        return $this->sendResponse(OrderResource::collection($orders), 'Today\'s orders retrieved successfully.');
    }

    public function thisWeekOrders()
    {
        $orders = $this->orderService->getThisWeekOrders();
        return $this->sendResponse(OrderResource::collection($orders), 'This week\'s orders retrieved successfully.');
    }

    public function thisMonthOrders()
    {
        $orders = $this->orderService->getThisMonthOrders();
        return $this->sendResponse(OrderResource::collection($orders), 'This month\'s orders retrieved successfully.');
    }

    public function byStatus($status)
    {
        $orders = $this->orderService->getOrdersByStatusWithCustomer($status);
        return $this->sendResponse(OrderResource::collection($orders), 'Orders by status retrieved successfully.');
    }

    public function byCustomer($customerId)
    {
        $orders = $this->orderService->getOrdersByCustomer($customerId);
        return $this->sendResponse(OrderResource::collection($orders), 'Customer orders retrieved successfully.');
    }

    public function customerStatistics($customerId)
    {
        $statistics = $this->orderService->getOrdersByCustomerWithStatistics($customerId);
        return $this->sendResponse($statistics, 'Customer order statistics retrieved successfully.');
    }

    public function recentOrders()
    {
        $orders = $this->orderService->getRecentOrders();
        return $this->sendResponse(OrderResource::collection($orders), 'Recent orders retrieved successfully.');
    }

    public function topCustomersByOrders()
    {
        $customers = $this->orderService->getTopCustomersByOrders();
        return $this->sendResponse($customers, 'Top customers by orders retrieved successfully.');
    }

    public function topCustomersByRevenue()
    {
        $customers = $this->orderService->getTopCustomersByRevenue();
        return $this->sendResponse($customers, 'Top customers by revenue retrieved successfully.');
    }

    public function summary()
    {
        $summary = $this->orderService->getOrdersSummary();
        return $this->sendResponse($summary, 'Orders summary retrieved successfully.');
    }

    public function processPayment($id)
    {
        try {
            $order = $this->orderService->processOrderPayment($id);
            return $this->sendResponse(new OrderResource($order), 'Order payment processed successfully.');
        } catch (\Exception $e) {
            return $this->sendError($e->getMessage(), [], 422);
        }
    }

    public function processDelivery($id)
    {
        try {
            $order = $this->orderService->processOrderDelivery($id);
            return $this->sendResponse(new OrderResource($order), 'Order delivery processed successfully.');
        } catch (\Exception $e) {
            return $this->sendError($e->getMessage(), [], 422);
        }
    }

    public function cancel($id)
    {
        try {
            $order = $this->orderService->cancelOrder($id);
            return $this->sendResponse(new OrderResource($order), 'Order canceled successfully.');
        } catch (\Exception $e) {
            return $this->sendError($e->getMessage(), [], 422);
        }
    }

    public function revenueTrend(Request $request)
    {
        $days = $request->get('days', 7);
        $trend = $this->orderService->getRevenueTrend($days);
        
        return $this->sendResponse($trend, 'Revenue trend retrieved successfully.');
    }

    public function growthRate()
    {
        $orderGrowth = $this->orderService->getOrderGrowthRate();
        $revenueGrowth = $this->orderService->getRevenueGrowthRate();
        
        return $this->sendResponse([
            'order_growth_rate' => $orderGrowth,
            'revenue_growth_rate' => $revenueGrowth
        ], 'Growth rates retrieved successfully.');
    }

    public function bestSellingDay()
    {
        $bestDay = $this->orderService->getBestSellingDay();
        return $this->sendResponse($bestDay, 'Best selling day retrieved successfully.');
    }

    public function export(Request $request)
    {
        $orderIds = $request->get('order_ids', []);
        $includeCustomer = $request->get('include_customer', true);
        
        $data = $this->orderService->exportOrdersToArray($orderIds, $includeCustomer);
        
        return response()->json([
            'success' => true,
            'data' => $data,
            'count' => count($data)
        ]);
    }

    public function dateRangeStatistics(Request $request)
    {
        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date'
        ]);

        $statistics = $this->orderService->calculateOrderStatisticsByDateRange(
            $request->start_date,
            $request->end_date
        );
        
        return $this->sendResponse($statistics, 'Date range statistics retrieved successfully.');
    }

    public function bulkCreate(Request $request)
    {
        $request->validate([
            'orders' => 'required|array',
            'orders.*.customer_id' => 'required|exists:customers,customer_id',
            'orders.*.order_total' => 'required|numeric|min:0',
            'orders.*.status' => 'sometimes|in:pending,paid,delivered,canceled'
        ]);

        $result = $this->orderService->bulkCreateOrders($request->orders);
        return $this->sendResponse($result, 'Bulk order creation completed.');
    }
}