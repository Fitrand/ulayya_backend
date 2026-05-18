<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Str;

class ProductImageController extends Controller
{
    public function store(Request $request, Product $product)
    {
        $validated = $request->validate([
            'images' => ['required', 'array'],
            'images.*' => ['image', 'max:4096'],
        ]);

        $sort = $product->images()->max('sort_order') ?? 0;

        foreach ($validated['images'] as $index => $img) {
            // $img is instance of UploadedFile
            $path = $request->file('images')[$index]->store('products', 'public');
            $product->images()->create([
                'image_url' => '/storage/' . $path,
                'sort_order' => ++$sort,
            ]);
        }

        return response()->json(['success' => true, 'message' => 'Images uploaded']);
    }

    public function destroy(ProductImage $image)
    {
        // remove file from storage if possible
        $url = $image->image_url;

        if ($url) {
            $publicPrefix = '/storage/';
            if (str_starts_with($url, $publicPrefix)) {
                $relative = substr($url, strlen($publicPrefix));
                Storage::disk('public')->delete($relative);
            } else {
                // try to parse after /storage/
                $pos = strpos($url, $publicPrefix);
                if ($pos !== false) {
                    $relative = substr($url, $pos + strlen($publicPrefix));
                    Storage::disk('public')->delete($relative);
                }
            }
        }

        $image->delete();

        return response()->json(['success' => true]);
    }

    public function reorder(Request $request, Product $product)
    {
        $validated = $request->validate([
            'order' => ['required', 'array'],
            'order.*' => ['integer'],
        ]);

        foreach ($validated['order'] as $sort => $id) {
            $img = $product->images()->where('id', $id)->first();
            if ($img) {
                $img->sort_order = $sort;
                $img->save();
            }
        }

        return response()->json(['success' => true]);
    }
}
