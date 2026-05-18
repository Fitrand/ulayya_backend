<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::query()
            ->with(['category', 'images'])
            ->withAvg('reviews', 'rating')
            ->withCount('reviews')
            ->where('is_active', true);

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->integer('category_id'));
        }

        if ($request->filled('q')) {
            $query->where('name', 'like', '%'.$request->string('q').'%');
        }

        $paginated = $query->latest()->paginate(10);

        // Transform setiap produk agar image_url selalu berisi URL absolut
        $paginated->getCollection()->transform(fn ($p) => $this->transformProduct($p));

        return response()->json([
            'data' => $paginated,
        ]);
    }

    public function show(Product $product)
    {
        if (! $product->is_active) {
            abort(404, 'Produk tidak ditemukan.');
        }

        $product->load(['category', 'images', 'reviews.user']);

        return response()->json([
            'data' => $this->transformProduct($product),
        ]);
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    /**
     * Tambahkan APP_URL ke semua image_url yang masih berupa path relatif.
     * Hanya menyentuh output API — model & admin panel tidak terpengaruh.
     */
    private function transformProduct(Product $product): array
    {
        $data = $product->toArray();

        // Konversi image_url produk
        $data['image_url'] = $this->absoluteUrl($data['image_url'] ?? null);

        // Konversi setiap gambar dalam relasi images[]
        if (isset($data['images'])) {
            $data['images'] = array_map(function (array $img) {
                $img['image_url'] = $this->absoluteUrl($img['image_url'] ?? null);
                return $img;
            }, $data['images']);
        }

        return $data;
    }

    /**
     * Ubah /storage/... menjadi http://APP_URL/storage/...
     * Jika sudah absolut, kembalikan apa adanya.
     * Jika null/kosong, kembalikan null.
     */
    private function absoluteUrl(?string $url): ?string
    {
        if (empty($url)) {
            return null;
        }

        if (str_starts_with($url, 'http://') || str_starts_with($url, 'https://')) {
            return $url;
        }

        return rtrim(config('app.url'), '/') . '/' . ltrim($url, '/');
    }
}
