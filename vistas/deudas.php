<?php
session_start();
require_once __DIR__ . '/../src/Config/conexion.php';

$accessToken = 'TU_ACCESS_TOKEN_AQUI';
$realmId = 'TU_REALM_ID_AQUI';

// Consulta a QuickBooks
function obtenerFacturasImpagas($accessToken, $realmId) {
  $url = "https://quickbooks.api.intuit.com/v3/company/$realmId/query";
  $query = "SELECT Id, Balance, CustomerRef, CustomField FROM Invoice WHERE Balance > '0'";

  $headers = [
    "Authorization: Bearer $accessToken",
    "Content-Type: application/text",
    "Accept: application/json"
  ];

  $ch = curl_init($url);
  curl_setopt($ch, CURLOPT_POST, true);
  curl_setopt($ch, CURLOPT_POSTFIELDS, $query);
  curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

  $response = curl_exec($ch);
  curl_close($ch);

  return json_decode($response, true);
}

$raw = obtenerFacturasImpagas($accessToken, $realmId);
$agrupado = [];

foreach ($raw['QueryResponse']['Invoice'] ?? [] as $factura) {
  $cliente = $factura['CustomerRef']['name'] ?? 'Cliente desconocido';
  $saldo = floatval($factura['Balance']);
  $vendedor = 'Sin vendedor';

  foreach ($factura['CustomField'] ?? [] as $campo) {
    if (($campo['Name'] ?? '') === 'Vendedor') {
      $vendedor = $campo['StringValue'] ?? 'Sin vendedor';
      break;
    }
  }

  $agrupado[$vendedor][$cliente] = ($agrupado[$vendedor][$cliente] ?? 0) + $saldo;
}

$vendedores = array_keys($agrupado);
?>