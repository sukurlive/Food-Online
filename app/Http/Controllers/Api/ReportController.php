<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\ReportService;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    protected $reportService;

    public function __construct(ReportService $reportService)
    {
        $this->reportService = $reportService;
    }

    /**
     * Get complete orders with customer details (Soal 2e.1)
     */
    public function ordersWithCustomers(Request $request)
    {
        $paginate = $request->get('per_page', 20);
        
        $result = $this->reportService->getCompleteOrders($paginate);
        
        if (!$result['success']) {
            return response()->json($result, 500);
        }
        
        return response()->json([
            'status' => 'success',
            'message' => 'Complete orders retrieved successfully',
            'data' => $result['data'],
            'meta' => $result['meta'] ?? null
        ]);
    }

    /**
     * Get customers with no orders (Soal 2e.2)
     */
    public function customersNoOrders()
    {
        $result = $this->reportService->getCustomersWithNoOrders();
        
        if (!$result['success']) {
            return response()->json($result, 500);
        }
        
        return response()->json([
            'status' => 'success',
            'message' => 'Customers with no orders retrieved successfully',
            'data' => $result['data'],
            'meta' => $result['meta'] ?? null
        ]);
    }

    /**
     * Get daily orders count for last 7 days (Soal 2e.3)
     */
    public function ordersLast7Days()
    {
        $result = $this->reportService->getDailyOrdersLast7Days();
        
        if (!$result['success']) {
            return response()->json($result, 500);
        }
        
        return response()->json([
            'status' => 'success',
            'message' => 'Daily orders for last 7 days retrieved successfully',
            'data' => $result['data'],
            'summary' => $result['summary'] ?? null
        ]);
    }

    /**
     * Get maximum order per customer (Soal 2e.4)
     */
    public function maxOrderPerCustomer()
    {
        $result = $this->reportService->getMaxOrderPerCustomer();
        
        if (!$result['success']) {
            return response()->json($result, 500);
        }
        
        return response()->json([
            'status' => 'success',
            'message' => 'Maximum order per customer retrieved successfully',
            'data' => $result['data'],
            'statistics' => $result['statistics'] ?? null
        ]);
    }

    /**
     * Get daily average vs today's orders (Soal 2e.5)
     */
    public function dailyAvgVsToday()
    {
        $result = $this->reportService->getDailyAverageVsToday();
        
        if (!$result['success']) {
            return response()->json($result, 500);
        }
        
        return response()->json([
            'status' => 'success',
            'message' => 'Daily average vs today comparison retrieved successfully',
            'data' => $result['data']
        ]);
    }

    /**
     * Get order summary
     */
    public function summary(Request $request)
    {
        $result = $this->reportService->getOrderSummary();
        
        if (!$result['success']) {
            return response()->json($result, 500);
        }
        
        return response()->json([
            'status' => 'success',
            'message' => 'Order summary retrieved successfully',
            'data' => $result['data']
        ]);
    }

    /**
     * Get revenue trend
     */
    public function revenueTrend(Request $request)
    {
        $validated = $request->validate([
            'period' => 'sometimes|in:daily,weekly,monthly',
            'limit' => 'sometimes|integer|min:1|max:100'
        ]);
        
        $period = $validated['period'] ?? 'monthly';
        $limit = $validated['limit'] ?? 12;
        
        $result = $this->reportService->getRevenueTrend($period, $limit);
        
        if (!$result['success']) {
            return response()->json($result, 500);
        }
        
        return response()->json([
            'status' => 'success',
            'message' => 'Revenue trend retrieved successfully',
            'period' => $result['period'],
            'data' => $result['data'],
            'growth_analysis' => $result['growth_analysis'] ?? null
        ]);
    }

    /**
     * Export report
     */
    public function export(Request $request)
    {
        $validated = $request->validate([
            'report_type' => 'required|in:orders,customers,summary',
            'format' => 'sometimes|in:json,csv,pdf',
            'start_date' => 'sometimes|date',
            'end_date' => 'sometimes|date|after_or_equal:start_date'
        ]);
        
        $reportType = $validated['report_type'];
        $format = $validated['format'] ?? 'json';
        
        // Generate report based on type
        switch ($reportType) {
            case 'orders':
                $result = $this->reportService->getCompleteOrders(1000); // Get all orders
                $fileName = 'orders_report_' . date('Ymd_His');
                break;
                
            case 'customers':
                $result = $this->reportService->getCustomersWithNoOrders();
                $fileName = 'customers_report_' . date('Ymd_His');
                break;
                
            case 'summary':
                $result = $this->reportService->getOrderSummary();
                $fileName = 'summary_report_' . date('Ymd_His');
                break;
                
            default:
                return response()->json([
                    'status' => 'error',
                    'message' => 'Invalid report type'
                ], 400);
        }
        
        if (!$result['success']) {
            return response()->json($result, 500);
        }
        
        // Return based on format
        if ($format === 'json') {
            return response()->json([
                'status' => 'success',
                'message' => 'Report exported successfully',
                'data' => $result['data'],
                'meta' => $result['meta'] ?? null
            ]);
        } else {
            //
            return response()->json([
                'status' => 'info',
                'message' => 'CSV/PDF export not implemented yet. Returning JSON format.',
                'data' => $result['data'],
                'meta' => $result['meta'] ?? null
            ]);
        }
    }
}