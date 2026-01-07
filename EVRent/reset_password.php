<?php

use Illuminate\Support\Facades\Hash;
use App\Modules\Auth\Models\M_User;

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$emails = ['owner1@rental.com', 'owner2@rental.com', 'owner3@rental.com'];

foreach ($emails as $email) {
    $user = M_User::where('email', $email)->first();
    if ($user) {
        $user->password = Hash::make('password');
        $user->save();
        echo "Password for {$email} reset to 'password'.\n";
    } else {
        echo "User {$email} not found.\n";
    }
}
