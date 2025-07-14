<?php
session_start();
ini_set('display_errors', 1);
error_reporting(E_ALL);

// ?? Verifica que el usuario esté logueado
if (!isset($_SESSION["usuario"])) {
    header("Location: /ColchonesBqto/login.php");
    exit();
}

// ?? Archivo del token
$tokenFile = __DIR__ . '/../token.json';
$tokenData = file_exists($tokenFile) ? json_decode(file_get_contents($tokenFile), true) : null;

// ?? Renovar token si está cerca de expirar
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

// ?? Extraer credenciales
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

// ? Si hubo error en la consulta
if ($response === false || $httpStatus !== 200) {
    echo "<script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
    <script>
    Swal.fire({
        title: 'Error al consultar clientes',
        text: 'Código HTTP: {$httpStatus}',
        icon: 'error',
        confirmButtonText: 'Volver'
    });
    </script>";
    exit();
}

$data        = json_decode($response, true);
$clientes    = $data['QueryResponse']['Customer'] ?? [];
$totalBalance = 0;
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Dashboard | Métricas de Clientes</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body class="bg-light">

<nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-4">
  <div class="container-fluid">
    <a class="navbar-brand" href="#">?? Dashboard</a>
    <span class="navbar-text text-white">Usuario: <?= htmlspecialchars($_SESSION["usuario"]) ?></span>
  </div>
</nav>

<div class="container py-4">
  <h2 class="mb-4">?? Clientes registrados en QuickBooks</h2>
  <a href="../src/exportar/exportar_clientes.php" class="btn btn-success mb-3">?? Exportar a Excel</a>

  <?php if (empty($clientes)): ?>
    <script>
      Swal.fire({ icon: 'info', title: 'Sin clientes', text: 'No se encontraron registros en QuickBooks.' });
    </script>
  <?php else: ?>
    <table class="table table-bordered table-hover">
      <thead class="table-dark text-center">
        <tr>
          <th>ID</th>
          <th>Nombre</th>
          <th>Email</th>
          <th>Teléfono</th>
          <th>Saldo ($)</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($clientes as $c): 
          $balance = isset($c['Balance']) ? (float)$c['Balance'] : 0;
          $totalBalance += $balance;
        ?>
        <tr>
          <td><?= htmlspecialchars($c['Id']) ?></td>
          <td><?= htmlspecialchars($c['DisplayName'] ?? 'N/D') ?></td>
          <td><?= htmlspecialchars($c['PrimaryEmailAddr']['Address'] ?? '—') ?></td>
          <td><?= htmlspecialchars($c['PrimaryPhone']['FreeFormNumber'] ?? '—') ?></td>
          <td class="text-end"><?= number_format($balance, 2) ?></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
      <tfoot>
        <tr>
          <td colspan="4" class="text-end fw-bold">Total Adeudado:</td>
          <td class="text-end fw-bold"><?= number_format($totalBalance, 2) ?></td>
        </tr>
      </tfoot>
    </table>
  <?php endif; ?>

  <div class="text-center mt-4">
    <a href="clientes.php" class="btn btn-secondary">?? Ver Clientes Detallados</a>
  </div>
</div>
</body>
</html>