<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CustomerController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\ReportController;

// ==================== PUBLIC ROUTES (NO AUTH) ====================
Route::post('login', [AuthController::class, 'login']);
Route::post('register', [AuthController::class, 'register']);

// ==================== PROTECTED ROUTES (AUTH REQUIRED) ====================
Route::middleware(['auth:sanctum', 'extend.token'])->group(function() {
    
    // Auth routes
    Route::post('logout', [AuthController::class, 'logout']);
    Route::get('profile', [AuthController::class, 'profile']);
    
    // ===== ADMIN ONLY ROUTES =====
    Route::middleware(['role:admin'])->group(function() {
        // Customer management
        Route::apiResource('customers', CustomerController::class);
        
        // Reports
        Route::prefix('reports')->controller(ReportController::class)->group(function() {
            Route::get('orders-with-customers', 'ordersWithCustomers');
            Route::get('customers-no-orders', 'customersNoOrders');
            Route::get('orders-last-7-days', 'ordersLast7Days');
            Route::get('max-order-per-customer', 'maxOrderPerCustomer');
            Route::get('daily-avg-vs-today', 'dailyAvgVsToday');
        });
        
        // Order deletion (admin only)
        Route::delete('orders/{id}', [OrderController::class, 'destroy']);
    });
    
    // ===== ORDERS ROUTES (ADMIN & USER) =====
    Route::middleware(['role:admin|user'])->group(function() {
        // Order CRUD except delete
        Route::apiResource('orders', OrderController::class)->except(['destroy']);
        
        // Additional order routes
        Route::prefix('orders')->controller(OrderController::class)->group(function() {
            Route::post('/{id}/status', 'updateStatus');
            Route::get('/search', 'search');
            Route::get('/statistics', 'statistics');
            Route::get('/today', 'todayOrders');
            Route::get('/week', 'thisWeekOrders');
            Route::get('/month', 'thisMonthOrders');
            Route::get('/status/{status}', 'byStatus');
            Route::get('/customer/{customerId}', 'byCustomer');
            Route::get('/customer/{customerId}/statistics', 'customerStatistics');
            Route::get('/recent', 'recentOrders');
            Route::get('/top-customers/orders', 'topCustomersByOrders');
            Route::get('/top-customers/revenue', 'topCustomersByRevenue');
            Route::get('/summary', 'summary');
            Route::post('/{id}/process-payment', 'processPayment');
            Route::post('/{id}/process-delivery', 'processDelivery');
            Route::post('/{id}/cancel', 'cancel');
            Route::get('/revenue-trend', 'revenueTrend');
            Route::get('/growth-rate', 'growthRate');
            Route::get('/best-selling-day', 'bestSellingDay');
            Route::post('/bulk-create', 'bulkCreate');
            Route::get('/date-range-statistics', 'dateRangeStatistics');
            Route::get('/export', 'export');
        });
    });
});