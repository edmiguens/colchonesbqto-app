<?php
session_start();
ini_set('display_errors', 1);
error_reporting(E_ALL);

if (!isset($_SESSION['user_id'])) {
    // Redirección absoluta para evitar "Not Found"
    $rutaLogin = $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'] . '/ColchonesBqto/login.php';
    header("Location: $rutaLogin");
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
$userId       = $_SESSION['user_id'];
$tokenManager = new TokenManagerDB($pdo);

// 🔄 Renovar token si está vencido
$tokenManager->verificarYRenovar($userId, $clientId, $clientSecret);
$datos = $tokenManager->cargar($userId);
$accessToken = $datos['access_token'];
$realmId     = $datos['realm_id'];

//$query = urlencode("SELECT Id, GivenName, FamilyName, DisplayName, Balance, PrimaryEmailAddr, PrimaryPhone FROM Customer");
$query = urlencode("SELECT Id, GivenName, FamilyName, DisplayName, Balance, PrimaryEmailAddr, PrimaryPhone, CustomField FROM Customer");
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

$rolUsuario   = strtolower($_SESSION['rol'] ?? '');
$nombreSesion = strtoupper(trim($_SESSION['nombre'] ?? ''));

if (isset($respuesta['QueryResponse']['Customer'])) {
    foreach ($respuesta['QueryResponse']['Customer'] as $cliente) {
        $deuda = floatval($cliente['Balance'] ?? 0);
        if ($deuda > 0) {
            $etiquetas = strtoupper($cliente['DisplayName'] ?? '');

            // 🎯 Aplicar filtro según el rol
            if ($rolUsuario === 'vendedor') {
                if (strpos($etiquetas, "VENDEDOR:{$nombreSesion}") === false) {
                    continue; // No está asignado al vendedor
                }
            } elseif ($rolUsuario === 'cliente') {
                if (strtoupper($cliente['GivenName'] ?? '') !== $nombreSesion) {
                    continue; // No coincide con el cliente logueado
                }
            }

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
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container my-5">
    <div class="row mb-3 align-items-center">
        <div class="col-md-6">
            <input type="text" id="filtro" class="form-control" placeholder="Buscar cliente por nombre o email">
        </div>
        <div class="col-md-6 text-end">
            <a href="dashboard.php" class="btn btn-secondary">⬅️ Volver al Dashboard</a>
        </div>
    </div>

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
                                                <a href="#" class="ver-facturas"
                                                   data-id="<?= $cliente['id'] ?>"
                                                   data-nombre="<?= $cliente['nombre'] ?>"
                                                   data-apellido="<?= $cliente['apellido'] ?>">
                                                   $<?= number_format($cliente['deuda'], 2) ?>
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <p class="text-center text-muted">No hay clientes con deudas registradas para tu rol.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- 💬 Modal para facturas -->
<div class="modal fade" id="modalFacturas" tabindex="-1" aria-labelledby="facturasLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title" id="facturasLabel">Facturas pendientes</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>
      <div class="modal-body">
        <div id="contenidoFacturas">Cargando facturas...</div>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.getElementById('filtro').addEventListener('input', function () {
    const valor = this.value.toLowerCase();
    const filas = document.querySelectorAll('#tabla-clientes tbody tr');
    filas.forEach(fila => {
        fila.style.display = fila.textContent.toLowerCase().includes(valor) ? '' : 'none';
    });
});

document.querySelectorAll('.ver-facturas').forEach(enlace => {
    enlace.addEventListener('click', function (e) {
        e.preventDefault();

        const customerId = this.dataset.id;
        const nombre     = this.dataset.nombre;
        const apellido   = this.dataset.apellido;

        document.getElementById('facturasLabel').textContent = `Facturas pendientes de ${nombre} ${apellido}`;
        document.getElementById('contenidoFacturas').textContent = "Cargando...";

        fetch(`facturas_cliente.php?id=${customerId}`)
            .then(res => res.text())
            .then(html => {
                document.getElementById('contenidoFacturas').innerHTML = html;
                new bootstrap.Modal(document.getElementById('modalFacturas')).show();
            })
            .catch(err => {
                document.getElementById('contenidoFacturas').innerHTML = `<p class='text-danger'>Error al cargar las facturas.</p>`;
                console.error("Error al cargar facturas:", err);
            });
    });
});
</script>

<div class="container my-4 text-center">
  <a href="dashboard.php" class="btn btn-secondary">⬅️ Volver al Dashboard</a>
</div>

</body>
</html>