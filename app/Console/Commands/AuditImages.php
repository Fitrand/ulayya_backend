<?php

namespace App\Console\Commands;

use App\Models\Product;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class AuditImages extends Command
{
    protected $signature   = 'audit:images';
    protected $description = 'Audit gambar produk di database vs file di disk';

    public function handle(): void
    {
        $products = Product::with('images')->get();

        $this->table(
            ['ID', 'Nama', 'image_url (raw)', 'images', 'File di disk?'],
            $products->map(function (Product $p) {
                $raw = $p->getOriginal('image_url') ?? 'NULL';
                $imgCount = $p->images->count();

                $fileExists = false;
                if ($raw !== 'NULL') {
                    // Strip /storage/ prefix untuk cek disk
                    $filePath = ltrim(str_replace('/storage/', '', $raw), '/');
                    $fileExists = Storage::disk('public')->exists($filePath);
                }

                $status = $fileExists ? '✓ Ada' : ($imgCount > 0 ? '✓ di rel images' : '✗ TIDAK ADA');

                return [$p->id, $p->name, $raw, $imgCount, $status];
            })->toArray()
        );

        $this->newLine();
        $this->info('Total produk       : ' . $products->count());
        $this->info('Punya image_url    : ' . $products->filter(fn ($p) => $p->getOriginal('image_url'))->count());
        $this->info('Punya rel images   : ' . $products->filter(fn ($p) => $p->images->count() > 0)->count());

        $this->newLine();
        $this->line('=== DETAIL product_images ===');
        $this->table(
            ['img_id', 'product_id', 'image_url (raw)', 'File di disk?'],
            \App\Models\ProductImage::all()->map(function ($img) {
                $raw = $img->getOriginal('image_url') ?? 'NULL';
                $filePath = ltrim(str_replace('/storage/', '', $raw), '/');
                $fileExists = \Illuminate\Support\Facades\Storage::disk('public')->exists($filePath);
                return [$img->id, $img->product_id, $raw, $fileExists ? '✓ Ada' : '✗ TIDAK ADA'];
            })->toArray()
        );
    }
}
