<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\BaseController;
use App\Http\Requests\Api\Customer\CreateCustomerRequest;
use App\Http\Requests\Api\Customer\UpdateCustomerRequest;
use App\Http\Resources\CustomerResource;
use App\Services\CustomerService;
use Illuminate\Http\Request;

class CustomerController extends BaseController
{
    protected $customerService;

    public function __construct(CustomerService $customerService)
    {
        $this->customerService = $customerService;
    }

    public function index()
    {
        $customers = $this->customerService->getAllCustomersWithOrdersCount();
        return $this->sendResponse(CustomerResource::collection($customers), 'Customers retrieved successfully.');
    }

    public function paginated(Request $request)
    {
        $perPage = $request->get('per_page', 15);
        $customers = $this->customerService->getPaginatedCustomers($perPage);
        return $this->sendResponse($customers, 'Customers retrieved successfully.');
    }

    public function store(CreateCustomerRequest $request)
    {
        try {
            $data = $request->validated();
            $customer = $this->customerService->registerCustomer($data);
            return $this->sendResponse(new CustomerResource($customer), 'Customer created successfully.', 201);
        } catch (\Exception $e) {
            return $this->sendError($e->getMessage(), [], 422);
        }
    }

    public function show($id)
    {
        $customer = $this->customerService->getCustomerByIdWithOrders($id);
        
        if (!$customer) {
            return $this->sendError('Customer not found.', [], 404);
        }

        return $this->sendResponse(new CustomerResource($customer), 'Customer retrieved successfully.');
    }

    public function update(UpdateCustomerRequest $request, $id)
    {
        try {
            $customer = $this->customerService->updateCustomer($id, $request->validated());
            return $this->sendResponse(new CustomerResource($customer), 'Customer updated successfully.');
        } catch (\Exception $e) {
            return $this->sendError($e->getMessage(), [], 422);
        }
    }

    public function destroy($id)
    {
        $deleted = $this->customerService->deleteCustomer($id);
        
        if (!$deleted) {
            return $this->sendError('Customer not found.', [], 404);
        }

        return $this->sendResponse([], 'Customer deleted successfully.');
    }

    public function search(Request $request)
    {
        $request->validate([
            'keyword' => 'required|string|min:2'
        ]);

        $customers = $this->customerService->searchCustomers($request->keyword);
        return $this->sendResponse(CustomerResource::collection($customers), 'Search results retrieved successfully.');
    }

    public function noOrders()
    {
        $customers = $this->customerService->getCustomersWithNoOrders();
        return $this->sendResponse(CustomerResource::collection($customers), 'Customers with no orders retrieved successfully.');
    }

    public function stats($id)
    {
        $stats = $this->customerService->getCustomerStats($id);
        
        if (empty($stats)) {
            return $this->sendError('Customer not found.', [], 404);
        }

        return $this->sendResponse($stats, 'Customer statistics retrieved successfully.');
    }

    public function topSpending()
    {
        $customers = $this->customerService->getTopSpendingCustomers();
        return $this->sendResponse(CustomerResource::collection($customers), 'Top spending customers retrieved successfully.');
    }

    public function summary($id)
    {
        $summary = $this->customerService->getCustomerOrdersSummary($id);
        
        if (!$summary) {
            return $this->sendError('Customer not found.', [], 404);
        }

        return $this->sendResponse($summary, 'Customer orders summary retrieved successfully.');
    }

    public function retentionRate()
    {
        $rate = $this->customerService->getCustomerRetentionRate();
        return $this->sendResponse(['retention_rate' => $rate], 'Customer retention rate retrieved successfully.');
    }

    public function segmentation()
    {
        $segments = $this->customerService->getCustomerSegmentation();
        return $this->sendResponse($segments, 'Customer segmentation retrieved successfully.');
    }

    public function import(Request $request)
    {
        $request->validate([
            'customers' => 'required|array',
            'customers.*.name' => 'required|string',
            'customers.*.phone' => 'required|string',
            'customers.*.email' => 'required|email'
        ]);

        $result = $this->customerService->importCustomersFromArray($request->customers);
        return $this->sendResponse($result, 'Customers import completed.');
    }

    public function export(Request $request)
    {
        $customerIds = $request->get('customer_ids', []);
        $data = $this->customerService->exportCustomersToArray($customerIds);
        
        return response()->json([
            'success' => true,
            'data' => $data,
            'count' => count($data)
        ]);
    }

    public function validatePhone(Request $request)
    {
        $request->validate([
            'phone' => 'required|string'
        ]);

        $isAvailable = $this->customerService->validateCustomerPhone($request->phone);
        
        return $this->sendResponse([
            'phone' => $request->phone,
            'available' => $isAvailable
        ], 'Phone validation completed.');
    }

    public function validateEmail(Request $request)
    {
        $request->validate([
            'email' => 'required|email'
        ]);

        $isAvailable = $this->customerService->validateCustomerEmail($request->email);
        
        return $this->sendResponse([
            'email' => $request->email,
            'available' => $isAvailable
        ], 'Email validation completed.');
    }
}