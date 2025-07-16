<?php
session_start();
ini_set('display_errors', 1);
error_reporting(E_ALL);

if (!isset($_SESSION['user_id']) || !isset($_SESSION['rol'])) {
    header('Location: ../login.php');
    exit;
}
//if (!isset($_SESSION['usuario_id'])) {
//    header("Location: /ColchonesBqto/login.php");
//    exit();

$config = require __DIR__ . '/../src/config/config.php';
$modo         = $config['modo'];
$clientId     = $config[$modo]['ClientID'];
$clientSecret = $config[$modo]['ClientSecret'];
$dbConfig     = $config['db'];

$pdo = new PDO(
    "mysql:host={$dbConfig['host']};port={$dbConfig['puerto']};dbname={$dbConfig['basedatos']};charset=utf8",
    $dbConfig['usuario'],
    $dbConfig['clave'],
    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
);

require_once __DIR__ . '/../src/servicios/token_manager.php';
$userId       = $_SESSION['user_id'];
//$userId       = $_SESSION['usuario_id'];
$tokenManager = new TokenManagerDB($pdo);

// 🔄 Renovar token si es necesario
$tokenManager->verificarYRenovar($userId, $clientId, $clientSecret);
$datos = $tokenManager->cargar($userId);
$accessToken = $datos['access_token'];
$realmId     = $datos['realm_id'];

$query = urlencode("SELECT Id, DisplayName, Balance FROM Customer");
$url   = "https://sandbox-quickbooks.api.intuit.com/v3/company/{$realmId}/query?query={$query}";

$headers = [
    "Authorization: Bearer {$accessToken}",
    "Accept: application/json",
    "Content-Type: application/text"
];

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
$response = curl_exec($ch);
curl_close($ch);

$respuesta = json_decode($response, true);
$clientes = [];

if (isset($respuesta['QueryResponse']['Customer'])) {
    foreach ($respuesta['QueryResponse']['Customer'] as $item) {
        $deuda = floatval($item['Balance'] ?? 0);
        if ($deuda > 0) {
            $clientes[] = [
                'nombre' => $item['DisplayName'] ?? 'Sin nombre',
                'deuda'  => $deuda
            ];
        }
    }
}

function calcularDeudaTotal($clientes) {
    return array_sum(array_column($clientes, 'deuda'));
}

usort($clientes, fn($a, $b) => $b['deuda'] <=> $a['deuda']);
$topClientes = array_slice($clientes, 0, 5);
$totalDeuda  = calcularDeudaTotal($clientes);
?>
<!DOCTYPE html>
<html lang="es">
<head>
<h5 class="text-end me-3">
  👤 Conectado como: <strong><?= htmlspecialchars($_SESSION['nombre']) ?></strong>
</h5>
    <meta charset="UTF-8">
    <title>Dashboard</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<?php include 'header_usuario.php'; ?>
<div class="container py-5">
    <h1 class="text-center mb-4">Dashboard</h1>

    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card text-bg-success">
                <div class="card-body">
                    <h5 class="card-title">Clientes Totales</h5>
                    <p class="card-text fs-3"><?= count($clientes) ?></p>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card text-bg-danger">
                <div class="card-body">
                    <h5 class="card-title">Deuda Acumulada</h5>
                    <p class="card-text fs-3">$<?= number_format($totalDeuda, 2) ?></p>
                </div>
            </div>
        </div>
    </div>

    <div class="mt-5 d-flex justify-content-center gap-3">
	    <?php if ($_SESSION['rol'] === 'admin'): ?>
           <a href="usuarios.php" class="btn btn-outline-secondary">
               🧑‍💼 Administrar Usuarios
           </a>
        <?php endif; ?>    
        <a href="clientes_deuda.php" class="btn btn-outline-dark">📊 Ver Deudas por Cliente</a>
    </div>
	<div class="card mt-5 border-success text-success text-center shadow-sm">
    <div class="card-body py-2">
        <h6 class="mb-0">
            ✅ Conectado a <strong>QuickBooks</strong><br>
            <small>Última sincronización: <strong><?= date('d/m/Y \a \l\a\s H:i:s') ?></strong></small>
        </h6>
    </div>
</div>
</div>
</body>
</html>