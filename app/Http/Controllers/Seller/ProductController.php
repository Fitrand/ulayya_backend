<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::query()->with('category')->latest();

        if ($q = trim((string) $request->query('q', ''))) {
            $query->where('name', 'like', '%' . $q . '%');
        }

        if ($categoryId = $request->integer('category_id')) {
            $query->where('category_id', $categoryId);
        }

        $products = $query->get()->map(function (Product $product) {
            $product->setAttribute('display_image', $this->productImage($product));
            $product->setAttribute('display_category', optional($product->category)->name ?? '-');
            $product->setAttribute('display_price', 'Rp ' . number_format((float) $product->price, 0, ',', '.'));
            $product->setAttribute('display_status_label', $product->is_active ? 'Aktif' : 'Nonaktif');
            $product->setAttribute('display_status_class', $product->is_active ? 'pill-emerald' : 'pill-red');

            return $product;
        });

        $categories = Category::orderBy('name')->get();

        return view('seller.products', [
            'pageTitle' => 'Produk',
            'pageDescription' => 'Daftar produk',
            'products' => $products,
            'categories' => $categories,
            'queryText' => $request->query('q', ''),
            'selectedCategory' => $request->integer('category_id'),
        ]);
    }

    public function create()
    {
        $categories = Category::orderBy('name')->get();

        return view('seller.products.create', [
            'categories' => $categories,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'category_id' => ['required', 'exists:categories,id'],
            'description' => ['nullable', 'string'],
            'price' => ['required', 'numeric'],
            'stock' => ['required', 'integer'],
            'is_active' => ['nullable', 'boolean'],
            'image' => ['nullable', 'image', 'max:2048'],
        ]);

        $data = [
            'category_id' => $validated['category_id'],
            'name' => $validated['name'],
            'slug' => Str::slug($validated['name']),
            'description' => $validated['description'] ?? null,
            'price' => $validated['price'],
            'stock' => $validated['stock'],
            'is_active' => $request->boolean('is_active'),
        ];

        // handle single image field for backward compatibility
        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('products', 'public');
            $data['image_url'] = '/storage/' . $path;
        }

        $product = Product::create($data);

        // handle multiple images upload (images[])
        if ($request->hasFile('images')) {
            $files = $request->file('images');
            $sort = $product->images()->max('sort_order') ?? 0;

            foreach ($files as $file) {
                $path = $file->store('products', 'public');
                $product->images()->create([
                    'image_url' => '/storage/' . $path,
                    'sort_order' => ++$sort,
                ]);
            }
        }
        return Redirect::route('seller.products')->with('status', 'Produk berhasil ditambahkan.');
    }

    public function edit(Product $product)
    {
        $categories = Category::orderBy('name')->get();

        return view('seller.products.edit', [
            'product' => $product,
            'categories' => $categories,
        ]);
    }

    public function update(Request $request, Product $product)
    {
        $validated = $request->validate([
            'name'        => ['required', 'string', 'max:255'],
            'category_id' => ['required', 'exists:categories,id'],
            'description' => ['nullable', 'string'],
            'price'       => ['required', 'numeric'],
            'stock'       => ['required', 'integer'],
            'is_active'   => ['nullable', 'boolean'],
            'image'       => ['nullable', 'image', 'max:2048'],
        ]);

        // Buat slug unik — abaikan produk ini sendiri
        $baseSlug = \Illuminate\Support\Str::slug($validated['name']);
        $slug     = $baseSlug;
        $counter  = 1;
        while (
            \App\Models\Product::where('slug', $slug)
                ->where('id', '!=', $product->id)
                ->exists()
        ) {
            $slug = $baseSlug . '-' . $counter++;
        }

        $product->fill([
            'category_id' => $validated['category_id'],
            'name'        => $validated['name'],
            'slug'        => $slug,
            'description' => $validated['description'] ?? null,
            'price'       => $validated['price'],
            'stock'       => $validated['stock'],
            'is_active'   => $request->boolean('is_active'),
        ]);

        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('products', 'public');
            $product->image_url = '/storage/' . $path;
        }

        $product->save();

        // append multiple images if provided
        if ($request->hasFile('images')) {
            $files = $request->file('images');
            $sort  = $product->images()->max('sort_order') ?? 0;

            foreach ($files as $file) {
                $path = $file->store('products', 'public');
                $product->images()->create([
                    'image_url'  => '/storage/' . $path,
                    'sort_order' => ++$sort,
                ]);
            }
        }

        return Redirect::route('seller.products')->with('status', 'Produk berhasil diperbarui.');
    }

    public function destroy(Product $product)
    {
        $this->deleteProductFiles($product);
        $product->delete();

        return Redirect::route('seller.products')->with('status', 'Produk berhasil dihapus.');
    }

    private function deleteProductFiles(Product $product): void
    {
        $this->deleteStoredPath($product->image_url);

        $product->loadMissing('images');

        foreach ($product->images as $image) {
            $this->deleteStoredPath($image->image_url);
        }
    }

    private function deleteStoredPath(?string $url): void
    {
        if (! $url) {
            return;
        }

        $path = null;

        if (str_starts_with($url, '/storage/')) {
            $path = substr($url, strlen('/storage/'));
        } elseif (str_contains($url, '/storage/')) {
            $path = substr($url, strpos($url, '/storage/') + strlen('/storage/'));
        }

        if ($path) {
            Storage::disk('public')->delete($path);
        }
    }

    private function productImage(Product $product): string
    {
        if ($product->image_url) {
            return $product->image_url;
        }

        $firstImage = $product->images->first()?->image_url;

        if ($firstImage) {
            return $firstImage;
        }

        return 'https://images.unsplash.com/photo-1718395012128-a6d204223093?auto=format&fit=crop&w=1200&q=80';
    }
}
