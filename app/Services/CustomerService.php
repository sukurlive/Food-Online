<?php

namespace App\Services;

use App\Repositories\CustomerRepository;

class CustomerService
{
    protected $customerRepository;

    public function __construct(CustomerRepository $customerRepository)
    {
        $this->customerRepository = $customerRepository;
    }

    public function getAllCustomers()
    {
        return $this->customerRepository->getAll();
    }

    public function getAllCustomersWithOrders()
    {
        return $this->customerRepository->getAllWithOrders();
    }

    public function getAllCustomersWithOrdersCount()
    {
        return $this->customerRepository->getAllWithOrdersCount();
    }

    public function getCustomerById($id)
    {
        return $this->customerRepository->findById($id);
    }

    public function getCustomerByIdWithOrders($id)
    {
        return $this->customerRepository->findByIdWithOrders($id);
    }

    public function getCustomerByPhone($phone)
    {
        return $this->customerRepository->findByPhone($phone);
    }

    public function getCustomerByEmail($email)
    {
        return $this->customerRepository->findByEmail($email);
    }

    public function createCustomer($data)
    {
        return $this->customerRepository->create($data);
    }

    public function updateCustomer($id, $data)
    {
        return $this->customerRepository->update($id, $data);
    }

    public function deleteCustomer($id)
    {
        return $this->customerRepository->delete($id);
    }

    public function getCustomersWithNoOrders()
    {
        return $this->customerRepository->getCustomersWithNoOrders();
    }

    public function getCustomersWithOrders()
    {
        return $this->customerRepository->getCustomersWithOrders();
    }

    public function getRecentCustomers($limit = 10)
    {
        return $this->customerRepository->getRecentCustomers($limit);
    }

    public function searchCustomers($keyword)
    {
        return $this->customerRepository->search($keyword);
    }

    public function getCustomersByOrderStatus($status)
    {
        return $this->customerRepository->getCustomersByOrderStatus($status);
    }

    public function getTopSpendingCustomers($limit = 10)
    {
        return $this->customerRepository->getTopSpendingCustomers($limit);
    }

    public function getCustomerStats($customerId)
    {
        return $this->customerRepository->getCustomerStats($customerId);
    }

    public function getCustomersWithOrdersBetween($startDate, $endDate)
    {
        return $this->customerRepository->getCustomersWithOrdersBetween($startDate, $endDate);
    }

    public function getCustomersOrderCountBetween($startDate, $endDate)
    {
        return $this->customerRepository->getCustomersOrderCountBetween($startDate, $endDate);
    }

    public function countAllCustomers()
    {
        return $this->customerRepository->countAll();
    }

    public function countCustomersWithOrders()
    {
        return $this->customerRepository->countWithOrders();
    }

    public function countCustomersWithoutOrders()
    {
        return $this->customerRepository->countWithoutOrders();
    }

    public function getPaginatedCustomers($perPage = 15)
    {
        return $this->customerRepository->getPaginated($perPage);
    }

    public function bulkCreateCustomers($customersData)
    {
        return $this->customerRepository->bulkCreate($customersData);
    }

    public function bulkUpdateCustomers($ids, $data)
    {
        return $this->customerRepository->bulkUpdate($ids, $data);
    }

    public function bulkDeleteCustomers($ids)
    {
        return $this->customerRepository->bulkDelete($ids);
    }

    public function getCustomersWithTotalOrderAmount()
    {
        return $this->customerRepository->getCustomersWithTotalOrderAmount();
    }

    public function getCustomerOrdersSummary($customerId)
    {
        return $this->customerRepository->getCustomerOrdersSummary($customerId);
    }

    public function validateCustomerPhone($phone)
    {
        $customer = $this->customerRepository->findByPhone($phone);
        return $customer ? false : true; // true jika phone belum terdaftar
    }

    public function validateCustomerEmail($email)
    {
        $customer = $this->customerRepository->findByEmail($email);
        return $customer ? false : true; // true jika email belum terdaftar
    }

    public function registerCustomer($data)
    {
        // Validasi phone unik
        if (!$this->validateCustomerPhone($data['phone'])) {
            throw new \Exception('Phone number already registered', 422);
        }

        // Validasi email unik
        if (!$this->validateCustomerEmail($data['email'])) {
            throw new \Exception('Email already registered', 422);
        }

        return $this->customerRepository->create($data);
    }

    public function updateCustomerPhone($customerId, $newPhone)
    {
        // Cek apakah phone sudah digunakan oleh customer lain
        $existingCustomer = $this->customerRepository->findByPhone($newPhone);
        if ($existingCustomer && $existingCustomer->customer_id != $customerId) {
            throw new \Exception('Phone number already used by another customer', 422);
        }

        return $this->customerRepository->update($customerId, ['phone' => $newPhone]);
    }

    public function updateCustomerEmail($customerId, $newEmail)
    {
        // Cek apakah email sudah digunakan oleh customer lain
        $existingCustomer = $this->customerRepository->findByEmail($newEmail);
        if ($existingCustomer && $existingCustomer->customer_id != $customerId) {
            throw new \Exception('Email already used by another customer', 422);
        }

        return $this->customerRepository->update($customerId, ['email' => $newEmail]);
    }

    public function getCustomerLifetimeValue($customerId)
    {
        $stats = $this->customerRepository->getCustomerStats($customerId);
        
        if (empty($stats)) {
            return 0;
        }

        return $stats['total_spent'] ?? 0;
    }

    public function getCustomerAverageOrderValue($customerId)
    {
        $stats = $this->customerRepository->getCustomerStats($customerId);
        
        if (empty($stats)) {
            return 0;
        }

        return $stats['avg_order_value'] ?? 0;
    }

    public function getCustomerOrderFrequency($customerId)
    {
        $customer = $this->customerRepository->findByIdWithOrders($customerId);
        
        if (!$customer || $customer->orders->count() < 2) {
            return null;
        }

        $firstOrder = $customer->orders->min('order_date');
        $lastOrder = $customer->orders->max('order_date');
        $totalOrders = $customer->orders->count();

        $daysBetween = $lastOrder->diffInDays($firstOrder);
        
        if ($daysBetween > 0) {
            return $totalOrders / $daysBetween; // orders per day
        }

        return $totalOrders;
    }

    public function getCustomerRetentionRate()
    {
        $totalCustomers = $this->customerRepository->countAll();
        $customersWithOrders = $this->customerRepository->countWithOrders();
        
        if ($totalCustomers == 0) {
            return 0;
        }

        return ($customersWithOrders / $totalCustomers) * 100;
    }

    public function getNewCustomersLast30Days()
    {
        $thirtyDaysAgo = now()->subDays(30);
        $customers = $this->customerRepository->getAll();
        
        return $customers->filter(function($customer) use ($thirtyDaysAgo) {
            return $customer->created_at >= $thirtyDaysAgo;
        })->count();
    }

    public function getRepeatCustomers()
    {
        $customers = $this->customerRepository->getAllWithOrdersCount();
        
        return $customers->filter(function($customer) {
            return $customer->orders_count > 1;
        })->count();
    }

    public function getCustomerSegmentation()
    {
        $customers = $this->customerRepository->getCustomersWithTotalOrderAmount();
        
        $segments = [
            'high_value' => [], // Total spend > 1,000,000
            'medium_value' => [], // Total spend 100,000 - 1,000,000
            'low_value' => [], // Total spend < 100,000
            'new' => [], // No orders yet
        ];

        foreach ($customers as $customer) {
            $totalSpent = $customer->total_order_amount ?? 0;
            
            if ($totalSpent > 1000000) {
                $segments['high_value'][] = $customer;
            } elseif ($totalSpent >= 100000) {
                $segments['medium_value'][] = $customer;
            } elseif ($totalSpent > 0) {
                $segments['low_value'][] = $customer;
            } else {
                $segments['new'][] = $customer;
            }
        }

        return $segments;
    }

    public function importCustomersFromArray($customersArray)
    {
        $imported = [];
        $failed = [];

        foreach ($customersArray as $index => $data) {
            try {
                // Validasi data
                if (empty($data['name']) || empty($data['phone']) || empty($data['email'])) {
                    $failed[] = [
                        'index' => $index,
                        'data' => $data,
                        'error' => 'Required fields missing'
                    ];
                    continue;
                }

                // Cek duplikat phone
                if (!$this->validateCustomerPhone($data['phone'])) {
                    $failed[] = [
                        'index' => $index,
                        'data' => $data,
                        'error' => 'Phone already exists'
                    ];
                    continue;
                }

                // Cek duplikat email
                if (!$this->validateCustomerEmail($data['email'])) {
                    $failed[] = [
                        'index' => $index,
                        'data' => $data,
                        'error' => 'Email already exists'
                    ];
                    continue;
                }

                $customer = $this->customerRepository->create($data);
                $imported[] = $customer;

            } catch (\Exception $e) {
                $failed[] = [
                    'index' => $index,
                    'data' => $data,
                    'error' => $e->getMessage()
                ];
            }
        }

        return [
            'imported' => $imported,
            'failed' => $failed,
            'total_imported' => count($imported),
            'total_failed' => count($failed)
        ];
    }

    public function exportCustomersToArray($customerIds = null)
    {
        if ($customerIds) {
            $customers = $this->customerRepository->getAll()->filter(function($customer) use ($customerIds) {
                return in_array($customer->customer_id, $customerIds);
            });
        } else {
            $customers = $this->customerRepository->getAllWithOrdersCount();
        }

        $exportData = [];

        foreach ($customers as $customer) {
            $exportData[] = [
                'customer_id' => $customer->customer_id,
                'name' => $customer->name,
                'phone' => $customer->phone,
                'email' => $customer->email,
                'created_at' => $customer->created_at->format('Y-m-d H:i:s'),
                'total_orders' => $customer->orders_count ?? 0,
                'total_spent' => $customer->orders->sum('order_total') ?? 0
            ];
        }

        return $exportData;
    }
}