<?php
// Leer token.json
$tokenData = json_decode(file_get_contents('token.json'), true);
if (!$tokenData || !isset($tokenData['access_token'], $tokenData['realmId'])) {
    die('token.json incompleto o ausente.');
}

$accessToken = $tokenData['access_token'];
$realmId = $tokenData['realmId'];
$query = urlencode("select DisplayName, Balance, PrimaryEmailAddr, PrimaryPhone from Customer");
$url = "https://sandbox-quickbooks.api.intuit.com/v3/company/{$realmId}/query?query={$query}";

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

$response = curl_exec($ch);
$httpStatus = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpStatus !== 200) {
    die("Error HTTP {$httpStatus}");
}

$data = json_decode($response, true);
$clientes = $data['QueryResponse']['Customer'] ?? [];

// Cabeceras para Excel
header("Content-Type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=clientes.xls");
header("Pragma: no-cache");
header("Expires: 0");

// Imprimir tabla
echo "<table border='1'>";
echo "<tr><th>#</th><th>Nombre</th><th>Email</th><th>Teléfono</th><th>Saldo</th></tr>";

$total = 0;
foreach ($clientes as $i => $c) {
    $balance = isset($c['Balance']) ? (float)$c['Balance'] : 0;
    $total += $balance;
    echo "<tr>";
    echo "<td>" . ($i + 1) . "</td>";
    echo "<td>" . htmlspecialchars($c['DisplayName'] ?? 'N/D') . "</td>";
    echo "<td>" . htmlspecialchars($c['PrimaryEmailAddr']['Address'] ?? '—') . "</td>";
    echo "<td>" . htmlspecialchars($c['PrimaryPhone']['FreeFormNumber'] ?? '—') . "</td>";
    echo "<td>$" . number_format($balance, 2) . "</td>";
    echo "</tr>";
}

echo "<tr><td colspan='4'><strong>Total Adeudado</strong></td><td><strong>$" . number_format($total, 2) . "</strong></td></tr>";
echo "</table>";
exit;
?>