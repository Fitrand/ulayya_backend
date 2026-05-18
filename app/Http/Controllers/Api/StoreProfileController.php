<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SellerSetting;

class StoreProfileController extends Controller
{
    public function show()
    {
        $profile = SellerSetting::query()->first() ?? SellerSetting::create([
            'shop_name' => config('app.name', 'Ulayya'),
            'shop_description' => 'Kue tradisional Aceh dengan resep turun temurun.',
            'shop_email' => 'admin@ulayya.com',
            'shop_phone' => '0812-3456-7890',
            'shop_address' => 'Jl. Raya Banda Aceh No. 123',
            'bank_name' => 'Bank BCA',
            'bank_account_number' => '1234567890',
            'bank_account_name' => config('app.name', 'Ulayya'),
        ]);

        return response()->json([
            'data' => [
                'shop_name'          => $profile->shop_name,
                'shop_description'   => $profile->shop_description,
                'shop_phone'         => $profile->shop_phone,
                'shop_email'         => $profile->shop_email,
                'shop_address'       => $profile->shop_address,
                'shop_latitude'      => $profile->shop_latitude  ? (float) $profile->shop_latitude  : null,
                'shop_longitude'     => $profile->shop_longitude ? (float) $profile->shop_longitude : null,
                'promo_banner_text'  => $profile->promo_banner_text,
                'promo_banner_image' => $profile->promo_banner_image ? rtrim(config('app.url'), '/') . '/storage/' . $profile->promo_banner_image : null,
            ],
        ]);
    }
}
