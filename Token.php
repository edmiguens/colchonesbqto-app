<?php
// Cargar token.json como arreglo
$tokenData = json_decode(file_get_contents('token.json'), true);

// Validar contenido
if (!isset($tokenData['access_token'], $tokenData['realmId'])) {
    die('❌ El archivo token.json no contiene el access_token o realmId. Ejecuta el flujo desde config.php nuevamente.');
}

$access_token = $tokenData['access_token'];
$realm_id = $tokenData['realmId'];

// Endpoint de QuickBooks (sandbox)
$url = "https://sandbox-quickbooks.api.intuit.com/v3/company/{$realm_id}/companyinfo/{$realm_id}";

$headers = [
    "Authorization: Bearer {$access_token}",
    "Accept: application/json",
    "Content-Type: application/json"
];

// Hacer llamada cURL
$ch = curl_init($url);
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($ch);
$httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

// Mostrar respuesta
echo "<h3>HTTP Status Code: {$httpcode}</h3>";

$data = json_decode($response, true);

if ($httpcode === 200 && isset($data['CompanyInfo'])) {
    echo "<pre>" . print_r($data['CompanyInfo'], true) . "</pre>";
} else {
    echo "<pre>" . print_r($data, true) . "</pre>";
}
?>