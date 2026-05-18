<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::orderBy('name')->get();

        return view('seller.categories', [
            'pageTitle' => 'Kategori',
            'pageDescription' => 'Daftar kategori',
            'categoryCards' => $categories->map(function (Category $c) {
                return [
                    'name' => $c->name,
                    'slug' => $c->slug,
                    'count' => $c->products()->count(),
                    'active' => $c->products()->where('is_active', true)->count(),
                    'stock' => $c->products()->sum('stock'),
                    'id' => $c->id,
                ];
            })->values(),
        ]);
    }

    public function create()
    {
        return view('seller.categories.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255'],
        ]);

        Category::create([
            'name' => $validated['name'],
            'slug' => $validated['slug'] ? Str::slug($validated['slug']) : Str::slug($validated['name']),
        ]);

        return Redirect::route('seller.categories')->with('status', 'Kategori berhasil ditambahkan.');
    }

    public function edit(Category $category)
    {
        return view('seller.categories.edit', [
            'category' => $category,
        ]);
    }

    public function update(Request $request, Category $category)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255'],
        ]);

        $category->fill([
            'name' => $validated['name'],
            'slug' => $validated['slug'] ? Str::slug($validated['slug']) : Str::slug($validated['name']),
        ]);

        $category->save();

        return Redirect::route('seller.categories')->with('status', 'Kategori berhasil diperbarui.');
    }

    public function destroy(Category $category)
    {
        // Prevent deletion if category still has products
        if ($category->products()->count() > 0) {
            return Redirect::route('seller.categories')->with('status', 'Tidak dapat menghapus kategori yang masih memiliki produk.');
        }

        $category->delete();

        return Redirect::route('seller.categories')->with('status', 'Kategori berhasil dihapus.');
    }
}
