<?php

namespace Database\Factories;

use App\Models\Customer;
use App\Models\Order;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Order>
 */
class OrderFactory extends Factory
{
    protected $model = Order::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'customer_id' => Customer::factory(),
            'order_date'  => $this->faker->dateTimeBetween('-30 days', 'now'),
            'order_total' => $this->faker->randomFloat(2, 10000, 5000000),
            'status'      => $this->faker->randomElement([
                'pending',
                'paid',
                'canceled',
                'delivered',
            ]),
            'created_at'  => now(),
            'updated_at'  => now(),
        ];
    }

    /**
     * State khusus: order sudah dibayar
     */
    public function paid()
    {
        return $this->state(fn () => [
            'status' => 'paid',
        ]);
    }

     /**
     * State khusus: order dikirim
     */
    public function delivered()
    {
        return $this->state(fn () => [
            'status' => 'delivered',
        ]);
    }
}
