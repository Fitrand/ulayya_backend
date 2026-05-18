<?php

namespace App\Console\Commands;

use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Console\Command;

/**
 * Normalizes stored image_url values so they contain only the relative
 * /storage/... path. The accessor on Product and ProductImage will then
 * prepend the current APP_URL at runtime, making URLs resilient to IP changes.
 */
class NormalizeImageUrls extends Command
{
    protected $signature   = 'images:normalize';
    protected $description = 'Konversi image_url absolut di DB menjadi path relatif /storage/...';

    public function handle(): void
    {
        $fixed = 0;

        // ── ProductImage ──────────────────────────────────────────────
        foreach (ProductImage::all() as $img) {
            $raw = $img->getOriginal('image_url');
            $relative = $this->toRelative($raw);

            if ($relative && $relative !== $raw) {
                $img->timestamps = false;                       // jangan update updated_at
                $img->forceFill(['image_url' => $relative])->saveQuietly();
                $this->line("  ProductImage #{$img->id}: {$raw} → {$relative}");
                $fixed++;
            }
        }

        // ── Product.image_url ─────────────────────────────────────────
        foreach (Product::all() as $product) {
            $raw = $product->getOriginal('image_url');
            if (! $raw) {
                continue;
            }

            $relative = $this->toRelative($raw);

            if ($relative && $relative !== $raw) {
                $product->timestamps = false;
                $product->forceFill(['image_url' => $relative])->saveQuietly();
                $this->line("  Product #{$product->id}: {$raw} → {$relative}");
                $fixed++;
            }
        }

        $this->newLine();
        $this->info("Selesai. {$fixed} baris diperbarui.");
        $this->info('Sekarang accessor akan selalu menggunakan APP_URL saat ini: ' . config('app.url'));
    }

    /**
     * Ubah URL absolut (atau path yang sudah relatif) menjadi /storage/...
     * Contoh:
     *   http://10.176.23.166:8000/storage/products/abc.jpg  →  /storage/products/abc.jpg
     *   /storage/products/abc.jpg                           →  /storage/products/abc.jpg (tidak berubah)
     *   products/abc.jpg                                    →  /storage/products/abc.jpg
     */
    private function toRelative(?string $url): ?string
    {
        if (empty($url)) {
            return null;
        }

        // Sudah relatif: /storage/...
        if (str_starts_with($url, '/storage/')) {
            return $url;
        }

        // Absolut: http(s)://host/storage/...
        if (preg_match('#/storage/(.+)$#', $url, $m)) {
            return '/storage/' . $m[1];
        }

        // Path mentah tanpa /storage/ prefix (mis. products/abc.jpg)
        if (! str_contains($url, '://')) {
            return '/storage/' . ltrim($url, '/');
        }

        return $url; // tidak dikenali, biarkan
    }
}
