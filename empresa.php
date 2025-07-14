<?php
// 🧠 Leer token.json
$tokenFile = 'token.json';
if (!file_exists($tokenFile)) {
    die('❌ No se encuentra el archivo token.json');
}

$tokenData = json_decode(file_get_contents($tokenFile), true);
$access_token = $tokenData['access_token'] ?? null;
$realmId      = $tokenData['realmId'] ?? null;

if (!$access_token || !$realmId) {
    die('❌ Faltan access_token o realmId en token.json');
}

// 🛰️ Endpoint de QuickBooks
$url = "https://quickbooks.api.intuit.com/v3/company/$realmId/companyinfo/$realmId";

// 🔐 Encabezados
$headers = [
    "Authorization: Bearer $access_token",
    "Accept: application/json"
];

// 🚀 Hacer la solicitud
$ch = curl_init($url);
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($ch);
curl_close($ch);

// 📦 Procesar la respuesta
$data = json_decode($response, true);
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Información de la Empresa</title>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <style>
    body { font-family: Arial, sans-serif; padding: 2rem; background: #f9f9f9; }
    .card { background: #fff; padding: 2rem; border-radius: 8px; max-width: 600px; margin: auto; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
    h2 { color: #333; margin-bottom: 1rem; }
    .item { margin-bottom: 0.5rem; }
  </style>
</head>
<body>

<div class="card">
  <h2>🏢 Información de la Empresa</h2>
  <?php
  if (isset($data['CompanyInfo'])) {
      $info = $data['CompanyInfo'];
      echo "<div class='item'><strong>Nombre:</strong> {$info['CompanyName']}</div>";
      echo "<div class='item'><strong>Dirección:</strong> " . ($info['CompanyAddr']['Line1'] ?? '') . "</div>";
      echo "<div class='item'><strong>País:</strong> {$info['Country']}</div>";
      echo "<div class='item'><strong>Phone:</strong> " . ($info['PrimaryPhone']['FreeFormNumber'] ?? 'No disponible') . "</div>";

      echo "<script>
        Swal.fire({
          icon: 'success',
          title: 'Conexión exitosa',
          text: 'Los datos de la empresa se obtuvieron correctamente.',
          confirmButtonText: 'Perfecto'
        });
      </script>";
  } else {
      echo "<strong>❌ Error:</strong> No se pudo obtener la información de la empresa.";
      echo "<pre>" . print_r($data, true) . "</pre>";
      echo "<script>
        Swal.fire({
          icon: 'error',
          title: 'Conexión fallida',
          text: 'No se pudo obtener la información. Verifica el token.',
          confirmButtonText: 'Revisar'
        });
      </script>";
  }
  ?>
</div>

</body>
</html>