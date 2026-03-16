<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

use App\Models\User;
use Illuminate\Support\Facades\Auth;

$user = User::first();
if (!$user) {
    echo "No user found\n";
    exit;
}

echo "Current remember_token: " . ($user->remember_token ?: 'NULL') . "\n";

// Simulate login with remember true
$credentials = ['email' => $user->email, 'password' => 'password']; // Assuming password is 'password' from seeder
$remember = true;

if (Auth::attempt($credentials, $remember)) {
    $user->refresh();
    echo "Login success!\n";
    echo "New remember_token: " . ($user->remember_token ?: 'NULL') . "\n";
} else {
    echo "Login failed (check credentials)\n";
}
