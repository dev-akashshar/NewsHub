<?php
$context = stream_context_create([
    'ssl' => [
        'verify_peer'      => false,
        'verify_peer_name' => false,
    ]
]);
$cert = file_get_contents('https://curl.se/ca/cacert.pem', false, $context);
file_put_contents(__DIR__ . '/cacert.pem', $cert);
echo "Cert downloaded successfully.\n";
