<?php
session_start();
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Verificar sesión
if (!isset($_SESSION['usuario_id'])) {
    header("Location: /ColchonesBqto/login.php");
    exit();
}

// Cargar token
$tokenFile = __DIR__ . '/../src/token.json';
$tokenData = file_exists($tokenFile) ? json_decode(file_get_contents($tokenFile), true) : null;

if ($tokenData && isset($tokenData['access_token'], $tokenData['realmId'], $tokenData['expires_in'])) {
    $expiraEn     = $tokenData['expires_in'];
    $guardadoHace = time() - filemtime($tokenFile);

    if ($guardadoHace > ($expiraEn - 300)) {
        include_once __DIR__ . '/../src/servicios/renovar_token.php';
        $tokenData = json_decode(file_get_contents($tokenFile), true);
    }
} else {
    header("Location: /ColchonesBqto/autorizar.php");
    exit();
}

// Consulta a QuickBooks
$accessToken = $tokenData['access_token'];
$realmId     = $tokenData['realmId'];
$query       = urlencode("SELECT Id, DisplayName, Balance, PrimaryEmailAddr, PrimaryPhone FROM Customer");
$url         = "https://sandbox-quickbooks.api.intuit.com/v3/company/{$realmId}/query?query={$query}";

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
curl_close($ch);

if ($response === false || $httpStatus !== 200) {
    echo "<script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
    <script>
    Swal.fire({
        title: 'Error en la consulta',
        text: 'Código HTTP: {$httpStatus}',
        icon: 'error'
    });
    </script>";
    exit();
}

// Procesar datos
$data     = json_decode($response, true);
$clientes = $data['QueryResponse']['Customer'] ?? [];

$totalClientes = count($clientes);
$totalBalance = 0;
$sinEmail = 0;
$sinTelefono = 0;

foreach ($clientes as $c) {
    $balance = isset($c['Balance']) ? (float)$c['Balance'] : 0;
    $totalBalance += $balance;

    if (empty($c['PrimaryEmailAddr']['Address'])) $sinEmail++;
    if (empty($c['PrimaryPhone']['FreeFormNumber'])) $sinTelefono++;
}

$saldoPromedio = $totalClientes > 0 ? $totalBalance / $totalClientes : 0;
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Dashboard Gerencial</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body class="bg-light">

<nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-4">
  <div class="container-fluid">
    <a class="navbar-brand" href="#">?? Panel Gerencial</a>
    <span class="navbar-text text-white">Bienvenido, <?= htmlspecialchars($_SESSION["usuario"]) ?></span>
  </div>
</nav>

<div class="container py-4">
  <div class="row text-center mb-4">
    <div class="col-md-3">
      <div class="card border-primary shadow-sm">
        <div class="card-body">
          <h5 class="card-title">Clientes Totales</h5>
          <p class="display-6"><?= $totalClientes ?></p>
        </div>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card border-success shadow-sm">
        <div class="card-body">
          <h5 class="card-title">Balance Total</h5>
          <p class="display-6">$<?= number_format($totalBalance, 2) ?></p>
        </div>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card border-warning shadow-sm">
        <div class="card-body">
          <h5 class="card-title">Saldo Promedio</h5>
          <p class="display-6">$<?= number_format($saldoPromedio, 2) ?></p>
        </div>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card border-danger shadow-sm">
        <div class="card-body">
          <h5 class="card-title">Sin Contacto</h5>
          <p class="small">Sin email: <?= $sinEmail ?> | Sin teléfono: <?= $sinTelefono ?></p>
        </div>
      </div>
    </div>
  </div>

  <div class="text-center mt-4">
    <a href="/ColchonesBqto/vistas/clientes.php" class="btn btn-outline-primary px-5 py-2 fs-5 w-50">?? Ver Lista de Clientes</a>
    <a href="/ColchonesBqto/vistas/usuarios.php" class="btn btn-outline-primary px-5 py-2 fs-5 w-50 mt-3">????? Ver Usuarios Registrados</a>
  </div>
</div>

</body>
</html>