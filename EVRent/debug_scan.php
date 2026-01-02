<?php
require __DIR__.'/vendor/autoload.php';
$app = require __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

try {
    $users = \App\Models\M_User::all();
    echo json_encode($users->map(function($u) {
        return [
            'id' => $u->id, 
            'username' => $u->username, 
            'role' => $u->role,
            'name' => $u->name
        ];
    }), JSON_PRETTY_PRINT);
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
