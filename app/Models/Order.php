<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use App\Models\OrderItem;
use App\Models\Coupon;

class Order extends Model
{
    protected $fillable = [
        'customer_name',
        'customer_phone',
        'customer_email',
        'shipping_address',
        'coupon_id',
        'discount_amount',
        'total_amount',
        'status'
    ];

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function coupon()
    {
        return $this->belongsTo(Coupon::class);
    }
}
