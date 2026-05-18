<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

$app->boot();

$products = App\Models\Product::with('images')->get();

echo "=== AUDIT GAMBAR PRODUK ===\n";
echo sprintf("%-5s %-25s %-15s %-10s %s\n", "ID", "Nama", "image_url (raw)", "Images", "Status");
echo str_repeat("-", 80) . "\n";

foreach ($products as $p) {
    $rawUrl = $p->getOriginal('image_url') ?? 'NULL';
    $imgCount = $p->images->count();
    $hasFile = false;

    if ($rawUrl !== 'NULL') {
        // Periksa apakah file ada di disk
        $filePath = str_replace('/storage/', '', $rawUrl);
        $hasFile = \Illuminate\Support\Facades\Storage::disk('public')->exists($filePath);
    }

    $status = ($rawUrl === 'NULL' && $imgCount === 0)
        ? 'TIDAK ADA GAMBAR'
        : ($hasFile || $imgCount > 0 ? 'OK' : 'URL ADA TAPI FILE TIDAK ADA DI DISK');

    echo sprintf("%-5s %-25s %-40s %-10s %s\n",
        $p->id,
        substr($p->name, 0, 24),
        substr($rawUrl, 0, 39),
        $imgCount,
        $status
    );
}

echo "\n=== RINGKASAN ===\n";
echo "Total produk       : " . $products->count() . "\n";
echo "Punya image_url    : " . $products->whereNotNull(fn($p) => $p->getOriginal('image_url'))->count() . "\n";
echo "Punya rel images   : " . $products->filter(fn($p) => $p->images->count() > 0)->count() . "\n";
