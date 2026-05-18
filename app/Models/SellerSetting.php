<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SellerSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'shop_name',
        'shop_description',
        'shop_email',
        'shop_phone',
        'shop_address',
        'shop_latitude',
        'shop_longitude',
        'bank_name',
        'bank_account_number',
        'bank_account_name',
        'email_notifications',
        'order_notifications',
        'low_stock_notifications',
        'promo_banner_image',
        'promo_banner_text',
    ];

    protected $casts = [
        'email_notifications' => 'boolean',
        'order_notifications' => 'boolean',
        'low_stock_notifications' => 'boolean',
    ];
}