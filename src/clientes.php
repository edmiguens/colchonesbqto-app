<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
$tokenData = json_decode(file_get_contents('token.json'), true);

if (!$tokenData || !isset($tokenData['access_token'], $tokenData['realmId'])) {
  die('❌ token.json incompleto o ausente.');
}

$accessToken = $tokenData['access_token'];
$realmId     = $tokenData['realmId'];
$query       = urlencode("SELECT Id, DisplayName, Balance, PrimaryEmailAddr, PrimaryPhone FROM Customer");
$url         = "https://quickbooks.api.intuit.com/v3/company/{$realmId}/query?query={$query}";
//$url         = "https://sandbox-quickbooks.api.intuit.com/v3/company/{$realmId}/query?query={$query}";

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

if ($httpStatus !== 200) {
  include 'renovar_token.php';
  exit;
}

$data     = json_decode($response, true);
$clientes = $data['QueryResponse']['Customer'] ?? [];
echo "<pre>" . print_r($clientes, true) . "</pre>";
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Clientes QuickBooks</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</head>
<body class="bg-light">
<nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-4">
  <div class="container-fluid">
    <a class="navbar-brand" href="dashboard.php">🏠 Dashboard</a>
    <div class="collapse navbar-collapse">
      <ul class="navbar-nav me-auto">
      </ul>
    </div>
  </div>
</nav>

<div class="container py-4">
  <h1 class="mb-4">👥 Lista de Clientes (QuickBooks API)</h1>
  <a href="exportar_clientes.php" class="btn btn-success mb-3">📤 Exportar a Excel</a>
  <?php if (empty($clientes)): ?>
    <script>
      Swal.fire({ icon: 'info', title: 'Sin clientes', text: 'No se encontraron clientes en QuickBooks.' });
    </script>
  <?php else: ?>
    <table class="table table-bordered table-striped">
      <thead class="table-dark text-center">
        <tr>
          <th>#</th>
		  <th>Código</th>
          <th>Nombre</th>
          <th>Email</th>
          <th>Teléfono</th>
          <th>Saldo</th>
          <th>Acciones</th>
        </tr>
      </thead>
      <tbody>
        <?php $totalBalance = 0;
        foreach ($clientes as $i => $c):
          $balance = isset($c['Balance']) ? (float)$c['Balance'] : 0;
          $totalBalance += $balance;
        ?>
        <tr>
		  <td><?= htmlspecialchars($c['Id']) ?></td>
          <td><?= htmlspecialchars($c['DisplayName'] ?? 'N/D') ?></td>
          <td><?= htmlspecialchars($c['PrimaryEmailAddr']['Address'] ?? '—') ?></td>
          <td><?= htmlspecialchars($c['PrimaryPhone']['FreeFormNumber'] ?? '—') ?></td>
          <td class="text-end">$<?= number_format($balance, 2) ?></td>
          <td class="text-center">
            <?php if ($balance > 0): ?>
              <button class="btn btn-sm btn-warning ver-facturas"
                      data-id="<?= $c['Id'] ?>"
                      data-nombre="<?= htmlspecialchars($c['DisplayName']) ?>">
                📄 Ver facturas
              </button>
            <?php else: ?>
              <span class="text-muted">—</span>
            <?php endif; ?>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
      <tfoot>
        <tr>
          <td colspan="4" class="text-end fw-bold">Total Adeudado:</td>
          <td colspan="2" class="text-end fw-bold">$<?= number_format($totalBalance, 2) ?></td>
        </tr>
      </tfoot>
    </table>
  <?php endif; ?>
  <div class="text-center mt-4">
    <a href="dashboard.php" class="btn btn-secondary">🔙 Volver al Dashboard</a>
  </div>
</div>

<!-- 📄 Modal de facturas -->
<div class="modal fade" id="modalFacturas" tabindex="-1" aria-labelledby="modalFacturasLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="modalFacturasLabel">📑 Facturas del cliente</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>
      <div class="modal-body">
        <div id="contenedorFacturas" class="table-responsive"></div>
      </div>
      <div class="modal-footer">
        <button class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
      </div>
    </div>
  </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function () {
  document.querySelectorAll(".ver-facturas").forEach(btn => {
    btn.addEventListener("click", function () {
      const id = this.dataset.id;
      const nombre = this.dataset.nombre;
      const modalLabel = document.getElementById("modalFacturasLabel");
      const contenedor = document.getElementById("contenedorFacturas");
      modalLabel.textContent = `📑 Facturas de ${nombre}`;
      contenedor.innerHTML = "<p class='text-center'>🔄 Cargando facturas...</p>";

      fetch(`facturas_api.php?id=${id}`)
        .then(res => res.json())
        .then(data => {
          if (data.error) {
            Swal.fire({ icon: "error", title: "Error", text: data.error });
            return;
          }

          const facturas = data.facturas;
          if (!facturas || facturas.length === 0) {
            contenedor.innerHTML = "<p class='text-center'>Este cliente no tiene facturas registradas.</p>";
            return;
          }

          let total = 0;
          let html = `<table class="table table-bordered table-sm">
            <thead class="table-dark text-center">
              <tr><th>N°</th><th>Fecha</th><th>Total</th><th>Saldo</th></tr>
            </thead><tbody>`;

          facturas.forEach(f => {
            const monto = parseFloat(f.TotalAmt || 0);
            const saldo = parseFloat(f.Balance || 0);
            total += saldo;
            html += `<tr class="text-center">
              <td>${f.DocNumber || '—'}</td>
              <td>${f.TxnDate || '—'}</td>
              <td>$${monto.toFixed(2)}</td>
              <td>$${saldo.toFixed(2)}</td>
            </tr>`;
          });

          html += `</tbody><tfoot><tr>
            <td colspan="3" class="text-end fw-bold">Total Pendiente:</td>
            <td class="text-end fw-bold">$${total.toFixed(2)}</td>
          </tr></tfoot></table>`;

          contenedor.innerHTML = html;
          new bootstrap.Modal(document.getElementById("modalFacturas")).show();
        })
        .catch(err => {
          Swal.fire({ icon: "error", title: "Error de conexión", text: err });
        });
    });
  });
});
</script>
</body>
</html>