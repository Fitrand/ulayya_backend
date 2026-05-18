<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class BuyerFlowApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_buyer_can_add_cart_checkout_and_see_order(): void
    {
        $user = User::factory()->create([
            'is_admin' => false,
        ]);
        Sanctum::actingAs($user);

        $category = Category::query()->create([
            'name' => 'Kue Tradisional',
            'slug' => 'kue-tradisional',
        ]);

        $product = Product::query()->create([
            'category_id' => $category->id,
            'name' => 'Bhoi Original',
            'slug' => 'bhoi-original',
            'description' => 'Produk untuk test checkout.',
            'price' => 50000,
            'stock' => 20,
            'is_active' => true,
        ]);

        $this->postJson('/api/v1/cart/items', [
            'product_id' => $product->id,
            'quantity' => 2,
        ])->assertOk()
            ->assertJsonStructure([
                'message',
                'data' => [
                    'id',
                    'items',
                ],
            ]);

        $checkout = $this->postJson('/api/v1/checkout', [
            'shipping_address' => 'Jl. Ulayya No. 1, Banda Aceh',
            'payment_method' => 'bank_transfer',
        ]);

        $checkout->assertCreated()
            ->assertJsonPath('message', 'Checkout berhasil.')
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'order_number',
                    'status',
                    'items',
                    'payment',
                ],
            ]);

        $this->getJson('/api/v1/orders')
            ->assertOk()
            ->assertJsonStructure([
                'data' => [
                    'data',
                    'current_page',
                    'last_page',
                ],
            ]);
    }

    public function test_cart_update_and_delete_return_consistent_data_payload(): void
    {
        $user = User::factory()->create([
            'is_admin' => false,
        ]);
        Sanctum::actingAs($user);

        $category = Category::query()->create([
            'name' => 'Kue Basah',
            'slug' => 'kue-basah',
        ]);

        $product = Product::query()->create([
            'category_id' => $category->id,
            'name' => 'Kue Test',
            'slug' => 'kue-test',
            'description' => 'Produk test cart update.',
            'price' => 10000,
            'stock' => 50,
            'is_active' => true,
        ]);

        $add = $this->postJson('/api/v1/cart/items', [
            'product_id' => $product->id,
            'quantity' => 1,
        ])->assertOk();

        $itemId = $add->json('data.items.0.id');

        $this->patchJson('/api/v1/cart/items/' . $itemId, [
            'quantity' => 3,
        ])->assertOk()
            ->assertJsonStructure([
                'message',
                'data' => ['items'],
            ]);

        $this->deleteJson('/api/v1/cart/items/' . $itemId)
            ->assertOk()
            ->assertJsonStructure([
                'message',
                'data' => ['items'],
            ]);
    }
}
