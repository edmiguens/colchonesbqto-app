<?php
session_start();
ini_set('display_errors', 1);
error_reporting(E_ALL);

// 🔒 Verificar sesión
if (!isset($_SESSION['usuario_id'])) {
    header("Location: /ColchonesBqto/login.php");
    exit();
}

$config = require __DIR__ . '/../src/config/config.php';
$modo   = $config['modo'];
$dbConfig = $config['db'];
$clientId     = $config[$modo]['ClientID'];
$clientSecret = $config[$modo]['ClientSecret'];

// 🛠️ Conexión DB
$pdo = new PDO(
    "mysql:host={$dbConfig['host']};port={$dbConfig['puerto']};dbname={$dbConfig['basedatos']};charset=utf8",
    $dbConfig['usuario'],
    $dbConfig['clave'],
    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
);

// 📂 Servicios
require_once __DIR__ . '/../src/servicios/token_manager.php';
require_once __DIR__ . '/../src/servicios/QuickBooksService.php';

$userId       = $_SESSION['usuario_id'];
$tokenManager = new TokenManagerDB($pdo);
$qb           = new QuickBooksService($tokenManager, $userId, $clientId, $clientSecret);

// 📦 Datos de clientes
//$respuesta = $qb->consultarClientes();
$datos = $tokenManager->cargar($userId);
$accessToken = $datos['access_token'];
$realmId     = $datos['realm_id'];
$query       = urlencode("SELECT Id, GivenName, FamilyName, Balance, PrimaryEmailAddr, PrimaryPhone FROM Customer");
$url         = "https://sandbox-quickbooks.api.intuit.com/v3/company/{$realmId}/query?query={$query}";

$headers = [
    "Authorization: Bearer {$accessToken}",
    "Accept: application/json",
    "Content-Type: application/text"
];

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

$response   = curl_exec($ch);
$httpStatus = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

$respuesta = json_decode($response, true);
echo "<pre>";
print_r($respuesta);
echo "</pre>";
$clientesDeuda = [];

if (isset($respuesta['QueryResponse']['Customer'])) {
    foreach ($respuesta['QueryResponse']['Customer'] as $cliente) {
        $deuda = $cliente['Balance'] ?? 0;
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
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Clientes con Deuda</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css" rel="stylesheet">
</head>
<body class="bg-light">
  <div class="container py-5">
    <h2 class="mb-4 text-center">🧾 Clientes con Deuda Registrados</h2>
    <div class="table-responsive shadow-sm rounded">
      <table id="tablaDeudas" class="table table-bordered table-hover">
        <thead class="table-dark text-center">
          <tr>
            <th>ID</th>
            <th>Nombre</th>
            <th>Apellido</th>
            <th>Teléfono</th>
            <th>Email</th>
            <th>Deuda ($)</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($clientesDeuda as $cliente): ?>
          <tr>
            <td><?= htmlspecialchars($cliente['id']) ?></td>
            <td><?= htmlspecialchars($cliente['nombre']) ?></td>
            <td><?= htmlspecialchars($cliente['apellido']) ?></td>
            <td><?= htmlspecialchars($cliente['telefono']) ?></td>
            <td><?= htmlspecialchars($cliente['email']) ?></td>
            <td class="text-end"><?= number_format($cliente['deuda'], 2) ?></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>

    <div class="mt-4 text-center">
      <a href="dashboard.php" class="btn btn-outline-secondary">⬅️ Volver al Dashboard</a>
      <a href="../src/exportar/exportar_deudas.php" class="btn btn-success">📤 Exportar Deudas</a>
    </div>
  </div>

  <!-- Scripts -->
  <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
  <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
  <script>
    new DataTable('#tablaDeudas');
  </script>
</body>
</html>