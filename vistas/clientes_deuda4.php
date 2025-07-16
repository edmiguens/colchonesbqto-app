<?php
session_start();
ini_set('display_errors', 1);
error_reporting(E_ALL);

if (!isset($_SESSION['usuario_id'])) {
    header("Location: /ColchonesBqto/login.php");
    exit();
}

$config = require __DIR__ . '/../src/config/config.php';
$modo   = $config['modo'];
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
$userId       = $_SESSION['usuario_id'];
$tokenManager = new TokenManagerDB($pdo);

// 🔄 Renovar token si está vencido
$tokenManager->verificarYRenovar($userId, $clientId, $clientSecret);
$row = $tokenManager->cargar($userId);
//echo "Token verificado a las: " . date('H:i:s') . "<br>";
//echo "Token expira a las: " . $row['access_expiration'] . "<br>";
//echo "Hora actual: " . date('Y-m-d H:i:s') . "<br>";
$datos = $tokenManager->cargar($userId);
$accessToken = $datos['access_token'];
$realmId     = $datos['realm_id'];

$query = urlencode("SELECT Id, GivenName, FamilyName, Balance, PrimaryEmailAddr, PrimaryPhone FROM Customer");
$url   = "https://sandbox-quickbooks.api.intuit.com/v3/company/{$realmId}/query?query={$query}";

$headers = [
    "Content-Type: application/text",
    "Authorization: Bearer {$accessToken}",
    "Accept: application/json"
];

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
$response = curl_exec($ch);
curl_close($ch);

$respuesta = json_decode($response, true);
$clientesDeuda = [];

if (isset($respuesta['QueryResponse']['Customer'])) {
    foreach ($respuesta['QueryResponse']['Customer'] as $cliente) {
        $deuda = floatval($cliente['Balance'] ?? 0);
        if ($deuda > 0) {
            $clientesDeuda[] = [
                'id'        => $cliente['Id'],
                'nombre'    => $cliente['GivenName'] ?? '',
                'apellido'  => $cliente['FamilyName'] ?? '',
                'telefono'  => $cliente['PrimaryPhone']['FreeFormNumber'] ?? '—',
                'email'     => $cliente['PrimaryEmailAddr']['Address'] ?? '—',
                'deuda'     => $deuda
            ];
        }
    }
}
?>
<!-- ... Tu HTML sigue como lo tenías con la tabla y botones ... -->
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Clientes con Deuda</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container my-5">
    

    <!-- 🔍 Buscador -->
    <div class="row mb-3">
        <div class="col-md-6 mx-auto">
            <input type="text" id="filtro" class="form-control" placeholder="Buscar cliente por nombre o email">
        </div>
    </div>

    <!-- 📋 Tabla de clientes -->
    <div class="row justify-content-center">
        <div class="col-lg-11">
            <div class="card border-0 shadow-sm">
                <div class="card-header text-white text-center" style="background: linear-gradient(90deg, #1976D2, #004BA0);">
                    <h4 class="mb-0">💳 Clientes con Deuda Activa</h4>
                </div>
                <div class="card-body p-4 bg-white">
                    <?php if (count($clientesDeuda) > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-striped table-bordered table-hover align-middle" id="tabla-clientes">
                                <thead class="table-dark text-center">
                                    <tr>
                                        <th>Nombre</th>
                                        <th>Apellido</th>
                                        <th>Teléfono</th>
                                        <th>Email</th>
                                        <th>💰 Deuda ($)</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($clientesDeuda as $cliente): 
                                        $resaltar = $cliente['deuda'] > 500 ? 'table-warning' : '';
                                    ?>
                                        <tr class="<?= $resaltar ?>">
                                            <td><?= htmlspecialchars($cliente['nombre']) ?></td>
                                            <td><?= htmlspecialchars($cliente['apellido']) ?></td>
                                            <td><?= htmlspecialchars($cliente['telefono']) ?></td>
                                            <td><?= htmlspecialchars($cliente['email']) ?></td>
                                            <td class="text-end fw-bold text-danger">
                                                $<?= number_format($cliente['deuda'], 2) ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <p class="text-center text-muted">No hay clientes con deudas registradas.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- 🔧 Buscador funcional -->
<script>
document.getElementById('filtro').addEventListener('input', function () {
    const valor = this.value.toLowerCase();
    const filas = document.querySelectorAll('#tabla-clientes tbody tr');
    filas.forEach(fila => {
        fila.style.display = fila.textContent.toLowerCase().includes(valor) ? '' : 'none';
    });
});
</script>

</body>
</html>