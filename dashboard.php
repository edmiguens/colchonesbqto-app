<?php
session_start();
if (!isset($_SESSION["usuario"])) {
  header("Location: login.php");
  exit();
}
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Mostrar estado del token visualmente
$tokenData = json_decode(file_get_contents('token.json'), true);
if (!$tokenData || !isset($tokenData['access_token'], $tokenData['realmId'])) {
  echo "<script>
    Swal.fire({
      title: 'Token no disponible',
      text: 'El archivo token.json no contiene datos válidos. Por favor autoriza nuevamente.',
      icon: 'error',
      confirmButtonText: 'Ok'
    });
  </script>";
}

// Mostrar advertencia si la API QuickBooks falla
if (isset($http1) && $http1 !== 200) {
  echo "<div class='alert alert-danger text-center'>
    Error al consultar clientes desde QuickBooks. Código HTTP: {$http1}
  </div>";
}

if (isset($http2) && $http2 !== 200) {
  echo "<div class='alert alert-danger text-center'>
    Error al consultar facturas pendientes. Código HTTP: {$http2}
  </div>";
}

require_once 'conexion.php'; // conexi贸n mysqli
$usuario = $_SESSION["usuario"];
$rol     = $_SESSION["rol"];

// 馃М Usuarios registrados
$sqlUsuarios     = "SELECT COUNT(*) AS total FROM usuarios";
$resUsuarios     = mysqli_query($conn, $sqlUsuarios);
$rowUsuarios     = mysqli_fetch_assoc($resUsuarios);
$totalUsuarios   = $rowUsuarios['total'] ?? 0;

// 馃攼 Leer token
$tokenData = json_decode(file_get_contents('token.json'), true);
$totalAdeudado = 0;
$totalClientes = 0;
$facturasPendientes = 0;

if ($tokenData && isset($tokenData['access_token'], $tokenData['realmId'])) {
  $accessToken = $tokenData['access_token'];
  $realmId     = $tokenData['realmId'];

  // 馃摝 Clientes
  $queryClientes = urlencode("SELECT Balance FROM Customer");
  $urlClientes = "https://quickbooks.api.intuit.com/v3/company/{$realmId}/query?query={$queryClientes}";
    //$urlClientes   = "https://sandbox-quickbooks.api.intuit.com/v3/company/{$realmId}/query?query={$queryClientes}";
  $ch1 = curl_init($urlClientes);
  curl_setopt_array($ch1, [
    CURLOPT_HTTPHEADER => [
      "Authorization: Bearer {$accessToken}",
      "Accept: application/json",
      "Content-Type: application/text"
    ],
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_SSL_VERIFYPEER => false
  ]);
  $resp1 = curl_exec($ch1);
  echo "<pre>" . htmlentities($resp1) . "</pre>";
  $http1 = curl_getinfo($ch1, CURLINFO_HTTP_CODE);
  curl_close($ch1);

  if ($http1 === 200) {
    $data1 = json_decode($resp1, true);
    $clientes = $data1['QueryResponse']['Customer'] ?? [];
    $totalClientes = count($clientes);
    foreach ($clientes as $c) {
      $totalAdeudado += isset($c['Balance']) ? (float)$c['Balance'] : 0;
    }
  }

  // 馃搼 Facturas con saldo
  $queryFacturas = urlencode("SELECT Id FROM Invoice WHERE Balance > '0'");
  $urlFacturas = "https://quickbooks.api.intuit.com/v3/company/{$realmId}/query?query={$queryFacturas}";
  //$urlFacturas   = "https://sandbox-quickbooks.api.intuit.com/v3/company/{$realmId}/query?query={$queryFacturas}";
  $ch2 = curl_init($urlFacturas);
  curl_setopt_array($ch2, [
    CURLOPT_HTTPHEADER => [
      "Authorization: Bearer {$accessToken}",
      "Accept: application/json",
      "Content-Type: application/text"
    ],
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_SSL_VERIFYPEER => false
  ]);
  $resp2 = curl_exec($ch2);
  echo "<pre>" . htmlentities($resp2) . "</pre>";
  $http2 = curl_getinfo($ch2, CURLINFO_HTTP_CODE);
  curl_close($ch2);

  if ($http2 === 200) {
    $data2 = json_decode($resp2, true);
    $facturas = $data2['QueryResponse']['Invoice'] ?? [];
    $facturasPendientes = count($facturas);
  }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Dashboard</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <script>
    function mostrarEnConstruccion() {
      Swal.fire({
        title: '馃毀 En construcci贸n',
        text: 'Esta secci贸n estar谩 disponible pr贸ximamente.',
        icon: 'info',
        confirmButtonText: 'Entendido',
        confirmButtonColor: '#3085d6'
      });
    }
  </script>
</head>
<body class="bg-light">
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
  <div class="container-fluid">
    <span class="navbar-brand">馃憢 Bienvenido, <?= htmlspecialchars($usuario) ?></span>
    <div class="d-flex">
      <a href="logout.php" class="btn btn-outline-light">馃敀 Cerrar sesi贸n</a>
    </div>
  </div>
</nav>

<div class="container mt-4">

  <!-- 馃攷 M茅tricas -->
  <div class="row text-center mb-4">
    <div class="col-md-3 mb-3">
      <div class="card border-secondary shadow-sm">
        <div class="card-body">
          <i class="fas fa-users fa-2x text-secondary mb-2"></i>
          <h5 class="card-title">Usuarios</h5>
          <p class="fs-4 fw-bold"><?= $totalUsuarios ?></p>
        </div>
      </div>
    </div>
    <div class="col-md-3 mb-3">
      <div class="card border-info shadow-sm">
        <div class="card-body">
          <i class="fas fa-user-friends fa-2x text-info mb-2"></i>
          <h5 class="card-title">Clientes Activos</h5>
          <p class="fs-4 fw-bold"><?= $totalClientes ?></p>
        </div>
      </div>
    </div>
    <div class="col-md-3 mb-3">
      <div class="card border-warning shadow-sm">
        <div class="card-body">
          <i class="fas fa-file-invoice-dollar fa-2x text-warning mb-2"></i>
          <h5 class="card-title">Facturas Pendientes</h5>
          <p class="fs-4 fw-bold"><?= $facturasPendientes ?></p>
        </div>
      </div>
    </div>
    <div class="col-md-3 mb-3">
      <div class="card border-danger shadow-sm">
        <div class="card-body">
          <i class="fas fa-dollar-sign fa-2x text-danger mb-2"></i>
          <h5 class="card-title">Total Adeudado</h5>
          <p class="fs-4 fw-bold">$<?= number_format($totalAdeudado, 2) ?></p>
        </div>
      </div>
    </div>
  </div>

  <!-- Rol -->
  <div class="alert alert-info text-center">
    <strong>Rol actual:</strong> <?= htmlspecialchars($rol) ?>
  </div>

  <?php if ($rol === "admin"): ?>
    <div class="card border-primary mb-4">
      <div class="card-header bg-primary text-white">
        <i class="fas fa-user-shield"></i> Administraci贸n
      </div>
      <div class="card-body">
        <p class="card-text">馃懆鈥嶐煉?Acceso completo al sistema.</p>
        <ul>
          <li>Gestionar usuarios</li>
          <li>Revisar registros</li>
          <li>Ver estad铆sticas</li>
        </ul>
        <div class="d-grid gap-3 mt-4">
          <a href="registro.php" class="btn btn-outline-primary">
            <i class="fas fa-user-plus"></i> Registrar Nuevo Usuario
          </a>
          <a href="usuarios.php" class="btn btn-outline-primary">
            <i class="fas fa-users"></i> Ver Usuarios Registrados
          </a>
          <a href="clientes.php" class="btn btn-outline-primary">
            <i class="fas fa-money-check-alt"></i> Cuentas por Cobrar
          </a>
        </div>
      </div>
    </div>
  <?php elseif ($rol === "vendedor"): ?>
  <div class="card border-success mb-4">
    <div class="card-header bg-success text-white">
      <i class="fas fa-user-tag"></i> Panel del Vendedor
    </div>
    <div class="card-body text-center">
      <p class="card-text">馃Ь Acceso a cuentas por cobrar.</p>
      <a href="clientes.php" class="btn btn-outline-success">
        <i class="fas fa-money-check-alt"></i> Cuentas por Cobrar
      </a>
    </div>
  </div>

<?php elseif ($rol === "usuario"): ?>
  <div class="card border-info mb-4">
    <div class="card-header bg-info text-white">
      <i class="fas fa-user"></i> Panel de Usuario
    </div>
    <div class="card-body text-center">
      <p class="card-text">馃攳 Acceso limitado. Funcionalidad en desarrollo.</p>
      <button class="btn btn-outline-primary" onclick="mostrarEnConstruccion()">
        <i class="fas fa-credit-card"></i> Consultar Cuentas por Pagar
      </button>
    </div>
  </div>
<?php endif; ?>
x
</div>
</body>
</html>