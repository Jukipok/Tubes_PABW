<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Modules\Auth\Models\M_User;

try {
    $user = M_User::first();
    if (!$user) {
        echo "No users found.";
        exit;
    }
    
    echo "Attempting to create token for user: " . $user->email . "\n";
    $token = $user->createToken('test-token');
    echo "Token created successfully: " . $token->plainTextToken . "\n";
} catch (\Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}
