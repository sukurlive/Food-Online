<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $primaryKey = 'order_id';
    public $timestamps = true;

    use HasFactory;

    protected $fillable = [
        'customer_id',
        'order_date',
        'order_total',
        'status'
    ];

    protected $casts = [
        'order_date' => 'datetime',
        'order_total' => 'decimal:2'
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id', 'customer_id');
    }
}
