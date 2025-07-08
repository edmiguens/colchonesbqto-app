<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
header('Content-Type: application/json');

ob_start(); // Asegura que no se envíe contenido antes del header

if (!isset($_GET['id'])) {
  ob_clean();
  echo json_encode(['error' => 'Cliente no especificado.']);
  exit;
}

$clienteId = htmlspecialchars($_GET['id'], ENT_QUOTES);

$tokenData = json_decode(file_get_contents('token.json'), true);

if (
  !$tokenData ||
  !isset($tokenData['access_token'], $tokenData['realmId'])
) {
  ob_clean();
  echo json_encode(['error' => '❌ token.json está incompleto o ausente.']);
  exit;
}

$accessToken = $tokenData['access_token'];
$realmId     = $tokenData['realmId'];

$query = urlencode("SELECT Id, DocNumber, TxnDate, TotalAmt, Balance FROM Invoice WHERE CustomerRef='${clienteId}'");
$url   = "https://sandbox-quickbooks.api.intuit.com/v3/company/{$realmId}/query?query={$query}";

$ch = curl_init($url);
curl_setopt_array($ch, [
  CURLOPT_HTTPHEADER => [
    "Authorization: Bearer {$accessToken}",
    "Accept: application/json",
    "Content-Type: application/text"
  ],
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_SSL_VERIFYPEER => false
]);

$response   = curl_exec($ch);
$httpStatus = curl_getinfo($ch, CURLINFO_HTTP_CODE);

if (curl_errno($ch)) {
  ob_clean();
  echo json_encode(['error' => '❌ Error cURL: ' . curl_error($ch)]);
  curl_close($ch);
  exit;
}

curl_close($ch);

if ($httpStatus !== 200) {
  ob_clean();
  echo json_encode(['error' => "🔒 Token inválido. Código HTTP {$httpStatus}."]);
  exit;
}

$data     = json_decode($response, true);
$facturas = $data['QueryResponse']['Invoice'] ?? [];

ob_clean(); // Limpia cualquier salida previa antes de enviar JSON real
echo json_encode(['facturas' => $facturas]);