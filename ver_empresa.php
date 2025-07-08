<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Leer el token guardado
$tokenData = json_decode(file_get_contents('token.json'), true);

// Validar presencia de token y realmId
if (!$tokenData || !isset($tokenData['access_token'], $tokenData['realmId'])) {
    die('❌ token.json incompleto o ausente.');
}

$accessToken = $tokenData['access_token'];
$realmId     = $tokenData['realmId'];

// Construir URL para CompanyInfo en entorno de producción
$url = "https://quickbooks.api.intuit.com/v3/company/{$realmId}/companyinfo/{$realmId}";

// Ejecutar petición cURL
$ch = curl_init($url);
curl_setopt_array($ch, [
    CURLOPT_HTTPHEADER => [
        "Authorization: Bearer {$accessToken}",
        "Accept: application/json"
    ],
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_SSL_VERIFYPEER => false // ⚠️ Solo para desarrollo local
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

// Mostrar código de respuesta
echo "<h3>🔎 Código HTTP: {$httpCode}</h3>";

// Mostrar respuesta cruda
echo "<h4>📦 Respuesta completa:</h4>";
echo "<pre>" . htmlspecialchars($response) . "</pre>";

// Decodificar JSON
$data = json_decode($response, true);

// Mostrar datos de la empresa si están disponibles
echo "<h4>🏢 Información de la Empresa:</h4>";
echo "<pre>";
print_r($data['CompanyInfo'] ?? '❌ No se pudo obtener información.');
echo "</pre>";
?>