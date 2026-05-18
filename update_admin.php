<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\Hash;

$admin = User::first();

if ($admin) {
    $admin->email = 'admin@ulayya.com';
    $admin->password = Hash::make('ulayya12345');
    $admin->save();
    echo "Admin updated successfully. Email: admin@ulayya.com\n";
} else {
    echo "No admin user found.\n";
}
