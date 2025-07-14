<?php
// manual_token.php
date_default_timezone_set('America/Caracas');

// 1) Tus credenciales de producción
$clientId     = 'ABCdF0BQFmcaxBa9KI9wtNRq9GbIMYbB2cWNA1UAvEa8t6hfmz';
$clientSecret = 't1IRhPgphog6kZqAtH7TA3aXGAjwh8ZIpZHfQaZb';
$redirectUri  = 'https://colchonesbqto-app.onrender.com/callback.php';

// 2) Captura el code de la URL: manual_token.php?code=TU_CODE
$code = $_GET['code'] ?? '';
if (!$code) {
    exit("❌ Debes pasar el parámetro code en la URL.\n\nEjemplo:\nmanual_token.php?code=TU_CODE");
}

// 3) Punto de intercambio de producción
$endpoint = 'https://oauth.platform.intuit.com/oauth2/v1/tokens/bearer';

// 4) Construye la petición
$headers = [
    'Authorization: Basic ' . base64_encode("$clientId:$clientSecret"),
    'Accept: application/json',
    'Content-Type: application/x-www-form-urlencoded',
];
$body = http_build_query([
    'grant_type'   => 'authorization_code',
    'code'         => $code,
    'redirect_uri' => $redirectUri
]);

// 5) Ejecuta cURL
$ch = curl_init($endpoint);
curl_setopt($ch, CURLOPT_HTTPHEADER,     $headers);
curl_setopt($ch, CURLOPT_POST,           true);
curl_setopt($ch, CURLOPT_POSTFIELDS,     $body);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($ch);
$error    = curl_error($ch);
curl_close($ch);

// 6) Imprime REQUEST y RESPONSE
header('Content-Type: text/plain; charset=UTF-8');
echo "=== REQUEST TO TOKEN ENDPOINT ===\n";
echo "URL: $endpoint\n\n";

echo "HEADERS:\n";
foreach ($headers as $h) {
    echo "  $h\n";
}
echo "\nBODY:\n$body\n\n";

echo "=== RESPONSE ===\n$response\n\n";
if ($error) {
    echo "=== CURL ERROR ===\n$error\n";
}