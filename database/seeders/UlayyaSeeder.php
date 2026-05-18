<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\User;
use App\Models\Cart;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UlayyaSeeder extends Seeder
{
    public function run(): void
    {
        // ── Admin Penjual ─────────────────────────────────────────────────
        User::firstOrCreate(
            ['email' => 'admin@ulayya.com'],
            [
                'name'     => 'Admin Ulayya',
                'phone'    => '08100000000',
                'password' => Hash::make('admin123'),
                'is_admin' => true,
            ]
        );

        // ── User Demo ────────────────────────────────────────────────────
        $user = User::firstOrCreate(
            ['email' => 'demo@ulayya.com'],
            [
                'name'     => 'Fitra',
                'phone'    => '08123456789',
                'password' => Hash::make('password'),
                'is_admin' => false,
            ]
        );
        Cart::firstOrCreate(['user_id' => $user->id]);

        // ── Kategori ─────────────────────────────────────────────────────
        $semua   = Category::firstOrCreate(['slug' => 'semua'],   ['name' => 'Semua']);
        $keju    = Category::firstOrCreate(['slug' => 'keju'],    ['name' => 'Keju']);
        $cokelat = Category::firstOrCreate(['slug' => 'cokelat'], ['name' => 'Cokelat']);
        $pandan  = Category::firstOrCreate(['slug' => 'pandan'],  ['name' => 'Pandan']);
        Category::firstOrCreate(['slug' => 'spesial'], ['name' => 'Spesial']);

        // ── Produk ───────────────────────────────────────────────────────
        $products = [
            [
                'category_id' => $semua->id,
                'name'        => 'Bolu Gulung',
                'slug'        => 'bolu-gulung',
                'description' => 'Bolu gulung lembut dengan isian krim yang lezat. Dipanggang segar setiap hari menggunakan resep turun-temurun khas Aceh.',
                'price'       => 45000,
                'stock'       => 50,
                'is_active'   => true,
                'image_url'   => 'https://images.unsplash.com/photo-1557925923-33b251d59245?auto=format&fit=crop&w=400&q=80',
                'images'      => [
                    'https://images.unsplash.com/photo-1557925923-33b251d59245?auto=format&fit=crop&w=1000&q=80',
                    'https://images.unsplash.com/photo-1519864600265-abb23847ef2c?auto=format&fit=crop&w=1000&q=80',
                ],
            ],
            [
                'category_id' => $pandan->id,
                'name'        => 'Kue Bhoi Pandan',
                'slug'        => 'kue-bhoi-pandan',
                'description' => 'Kue Bhoi dengan aroma pandan alami yang harum. Tekstur lembut dan rasa autentik Aceh yang tidak terlupakan.',
                'price'       => 50000,
                'stock'       => 40,
                'is_active'   => true,
                'image_url'   => 'https://images.unsplash.com/photo-1483695028939-5bb13f8648b0?auto=format&fit=crop&w=400&q=80',
                'images'      => [
                    'https://images.unsplash.com/photo-1483695028939-5bb13f8648b0?auto=format&fit=crop&w=1000&q=80',
                ],
            ],
            [
                'category_id' => $cokelat->id,
                'name'        => 'Kue Bhoi Cokelat',
                'slug'        => 'kue-bhoi-cokelat',
                'description' => 'Perpaduan sempurna antara Kue Bhoi tradisional dengan cokelat premium Belgia. Rasa manis yang pas dan tekstur moist.',
                'price'       => 52000,
                'stock'       => 35,
                'is_active'   => true,
                'image_url'   => 'https://images.unsplash.com/photo-1603532648955-039310d9ed75?auto=format&fit=crop&w=400&q=80',
                'images'      => [
                    'https://images.unsplash.com/photo-1603532648955-039310d9ed75?auto=format&fit=crop&w=1000&q=80',
                    'https://images.unsplash.com/photo-1578985545062-69928b1d9587?auto=format&fit=crop&w=1000&q=80',
                ],
            ],
            [
                'category_id' => $keju->id,
                'name'        => 'Kue Bhoi Keju',
                'slug'        => 'kue-bhoi-keju',
                'description' => 'Kue Bhoi bertabur keju pilihan yang gurih dan lembut. Favorit pelanggan setia Ulayya.',
                'price'       => 55000,
                'stock'       => 30,
                'is_active'   => true,
                'image_url'   => 'https://images.unsplash.com/photo-1587668178277-295251f900ce?auto=format&fit=crop&w=400&q=80',
                'images'      => [
                    'https://images.unsplash.com/photo-1587668178277-295251f900ce?auto=format&fit=crop&w=1000&q=80',
                ],
            ],
            [
                'category_id' => $keju->id,
                'name'        => 'Kue Bhoi Keju Spesial',
                'slug'        => 'kue-bhoi-keju-spesial',
                'description' => 'Edisi spesial dengan lapisan keju ganda dan topping keju mozzarella leleh. Best seller sepanjang masa!',
                'price'       => 60000,
                'stock'       => 25,
                'is_active'   => true,
                'image_url'   => 'https://images.unsplash.com/photo-1571115177098-24ec42ed204d?auto=format&fit=crop&w=400&q=80',
                'images'      => [
                    'https://images.unsplash.com/photo-1571115177098-24ec42ed204d?auto=format&fit=crop&w=1000&q=80',
                ],
            ],
            [
                'category_id' => $cokelat->id,
                'name'        => 'Kue Bhoi Double Cokelat',
                'slug'        => 'kue-bhoi-double-cokelat',
                'description' => 'Dua lapisan cokelat rich yang menggoyang lidah. Cocok untuk pecinta cokelat sejati.',
                'price'       => 58000,
                'stock'       => 20,
                'is_active'   => true,
                'image_url'   => 'https://images.unsplash.com/photo-1606313564200-e75d5e30476c?auto=format&fit=crop&w=400&q=80',
                'images'      => [
                    'https://images.unsplash.com/photo-1606313564200-e75d5e30476c?auto=format&fit=crop&w=1000&q=80',
                ],
            ],
        ];

        foreach ($products as $data) {
            $images = $data['images'] ?? [];
            unset($data['images']);

            $product = Product::firstOrCreate(['slug' => $data['slug']], $data);

            foreach ($images as $index => $imageUrl) {
                ProductImage::firstOrCreate(
                    ['product_id' => $product->id, 'image_url' => $imageUrl],
                    ['sort_order' => $index]
                );
            }
        }

        $this->command->info('✅ Seeder selesai: 1 admin, 1 user demo, ' . count($products) . ' produk, 5 kategori.');
    }
}
