<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$sub = App\Models\PushSubscription::first();
if (!$sub) {
    echo "No subscriptions found.\n";
    exit;
}

$ctrl = new App\Http\Controllers\ChatController();
$r = new ReflectionMethod($ctrl, 'sendWebPush');
$r->setAccessible(true);
$r->invoke($ctrl, $sub, ['title' => 'Test', 'body' => 'Test Body', 'icon' => '/icons/icon-192.png', 'badge' => '/icons/badge-72.png', 'image' => null, 'data' => []]);
echo "Done executing push script.\n";
