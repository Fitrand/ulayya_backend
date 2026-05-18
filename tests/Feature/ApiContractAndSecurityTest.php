<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\SellerSetting;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ApiContractAndSecurityTest extends TestCase
{
    use RefreshDatabase;

    public function test_non_admin_cannot_access_seller_web_portal(): void
    {
        $buyer = User::factory()->create([
            'is_admin' => false,
        ]);

        $this->actingAs($buyer)
            ->get('/penjual')
            ->assertForbidden();
    }

    public function test_forgot_password_response_does_not_expose_plain_password(): void
    {
        $user = User::factory()->create([
            'email' => 'buyer@example.com',
        ]);

        $response = $this->postJson('/api/v1/auth/forgot-password', [
            'email' => $user->email,
        ]);

        $response->assertOk()
            ->assertJsonMissingPath('new_password')
            ->assertJsonPath(
                'message',
                'Permintaan reset password diterima. Silakan hubungi admin untuk reset manual sementara.'
            );
    }

    public function test_products_index_uses_consistent_data_wrapper_contract(): void
    {
        $category = Category::query()->create([
            'name' => 'Kue',
            'slug' => 'kue',
        ]);

        Product::query()->create([
            'category_id' => $category->id,
            'name' => 'Produk Test',
            'slug' => 'produk-test',
            'description' => 'Produk uji',
            'price' => 25000,
            'stock' => 10,
            'is_active' => true,
        ]);

        $response = $this->getJson('/api/v1/products');
        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    'data',
                    'current_page',
                    'last_page',
                ],
            ]);
    }

    public function test_orders_index_uses_consistent_data_wrapper_contract(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        Order::query()->create([
            'user_id' => $user->id,
            'order_number' => 'ORD-TEST-001',
            'status' => 'pending',
            'subtotal' => 100000,
            'shipping_cost' => 0,
            'total_amount' => 100000,
            'shipping_address' => 'Alamat test',
        ]);

        $response = $this->getJson('/api/v1/orders');
        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    'data',
                    'current_page',
                    'last_page',
                ],
            ]);
    }

    public function test_store_profile_exposes_shop_phone_for_mobile_whatsapp(): void
    {
        SellerSetting::query()->create([
            'shop_name' => 'Ulayya',
            'shop_description' => 'Toko test',
            'shop_email' => 'seller@example.com',
            'shop_phone' => '0812-9999-0000',
            'shop_address' => 'Alamat test',
        ]);

        $this->getJson('/api/v1/store-profile')
            ->assertOk()
            ->assertJsonPath('data.shop_phone', '0812-9999-0000');
    }
}
