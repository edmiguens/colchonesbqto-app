<?php
// 🔐 Seguridad básica
session_start();

if (!isset($_SESSION['user_id']) || !isset($_GET['id'])) {
    http_response_code(403);
    exit("Acceso denegado.");
}

$customerId   = $_GET['id'];
$config       = require __DIR__ . '/../src/config/config.php';
$modo         = $config['modo'];
$dbConfig     = $config['db'];
$clientId     = $config[$modo]['ClientID'];
$clientSecret = $config[$modo]['ClientSecret'];

$pdo = new PDO(
    "mysql:host={$dbConfig['host']};port={$dbConfig['puerto']};dbname={$dbConfig['basedatos']};charset=utf8",
    $dbConfig['usuario'],
    $dbConfig['clave'],
    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
);

require_once __DIR__ . '/../src/servicios/token_manager.php';
$userId       = $_SESSION['user_id'];
$tokenManager = new TokenManagerDB($pdo);
$tokenManager->verificarYRenovar($userId, $clientId, $clientSecret);
$datos        = $tokenManager->cargar($userId);
$accessToken  = $datos['access_token'];
$realmId      = $datos['realm_id'];

// 🔎 Consulta de facturas
$queryFacturas = urlencode("SELECT Id, TxnDate, TotalAmt, Balance, DocNumber FROM Invoice WHERE Balance > '0' AND CustomerRef = '{$customerId}'");
$urlFacturas   = "https://sandbox-quickbooks.api.intuit.com/v3/company/{$realmId}/query?query={$queryFacturas}";

$headers = [
    "Authorization: Bearer {$accessToken}",
    "Accept: application/json",
    "Content-Type: application/text"
];

$ch = curl_init($urlFacturas);
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
$response = curl_exec($ch);
curl_close($ch);

$respuesta = json_decode($response, true);
$facturas  = $respuesta['QueryResponse']['Invoice'] ?? [];

if (count($facturas) === 0) {
    echo "<p class='text-center text-muted'>No hay facturas pendientes para este cliente.</p>";
    exit;
}
?>

<!-- 📋 Tabla de facturas -->
<table class="table table-bordered table-hover align-middle">
    <thead class="table-light text-center">
        <tr>
            <th># Factura</th>
            <th>Fecha</th>
            <th>Monto</th>
            <th>Pendiente</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($facturas as $f): ?>
            <tr>
                <td><?= htmlspecialchars($f['DocNumber'] ?? $f['Id']) ?></td>
                <td><?= htmlspecialchars($f['TxnDate']) ?></td>
                <td class="text-end">$<?= number_format($f['TotalAmt'], 2) ?></td>
                <td class="text-end fw-bold text-danger">$<?= number_format($f['Balance'], 2) ?></td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>