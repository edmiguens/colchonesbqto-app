<?php
// requestTokenQuickBooks.php

$client_id     = 'YOUR_CLIENT_ID';
$client_secret = 'YOUR_CLIENT_SECRET';
$redirect_uri  = 'https://tuapp.com/callback';
$auth_code     = 'AUTH_CODE_FROM_INTUIT';

$token_url = 'https://oauth.platform.intuit.com/oauth2/v1/tokens/bearer';

$headers = [
    'Content-Type: application/x-www-form-urlencoded',
    'Authorization: Basic ' . base64_encode($client_id . ':' . $client_secret)
];

$post_fields = http_build_query([
    'grant_type'    => 'authorization_code',
    'code'          => $auth_code,
    'redirect_uri'  => $redirect_uri,
    'scope'         => 'com.intuit.quickbooks.accounting'
]);

$ch = curl_init($token_url);
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $post_fields);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "HTTP Status Code: " . $http_code . "\n";
echo "Response:\n" . $response;
?>