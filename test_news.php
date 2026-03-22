<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

try {
    putenv('CURL_CA_BUNDLE=' . base_path('cacert.pem'));
    $api = new \jcobhams\NewsApi\NewsApi('10a5f38b677c4b62be0b11a38f044f1b');
    $response = $api->getTopHeadlines(null, null, 'in', 'general', 30, 1);
    if (empty($response->articles)) {
        echo "No error, but articles array is empty! Response:\n";
        print_r($response);
    } else {
        print_r("Success: " . count($response->articles) . " articles found.\n");
        print_r($response->articles[0]->title ?? 'No title');
    }
} catch (\Exception $e) {
    echo "ERROR: " . $e->getMessage();
}
