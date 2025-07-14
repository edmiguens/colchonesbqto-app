<?php
date_default_timezone_set('America/Caracas');

$tokenFile = __DIR__ . '/token.json';
$renovarScript = __DIR__ . '/renovar_token.php';

// 🔍 Verifica que token.json existe
if (!file_exists($tokenFile)) {
    die('❌ No se encuentra token.json');
}

$tokenData = json_decode(file_get_contents($tokenFile), true);
$access_token = $tokenData['access_token'] ?? null;
$expires_in   = $tokenData['expires_in'] ?? null;
$generated_at = $tokenData['generated_at'] ?? null;

if (!$access_token || !$expires_in || !$generated_at) {
    die('❌ token.json está incompleto');
}

// 🔎 Tiempo restante antes de expiración
$ahora = time();
$expira_en = $generated_at + $expires_in;
$restante = $expira_en - $ahora;
$minutos_restantes = round($restante / 60);

// 🧠 Decidir si renovar
$debe_renovarse = $restante <= 300; // menos de 5 minutos

?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Validación de Token</title>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>

<script>
<?php if ($debe_renovarse): ?>
    Swal.fire({
      icon: 'warning',
      title: 'Token por expirar',
      text: 'Quedan <?= $minutos_restantes ?> minutos. Renovando automáticamente...',
      showConfirmButton: false,
      timer: 4000
    });
<?php
    // Ejecutar renovación
    $output = shell_exec("php $renovarScript");
    echo "console.log(" . json_encode($output) . ");";

    // Mensaje final
    echo "setTimeout(() => {
        Swal.fire({
            icon: 'success',
            title: 'Token actualizado',
            text: 'El access_token fue renovado exitosamente.',
            confirmButtonText: 'Genial'
        });
    }, 4200);";
?>
<?php else: ?>
    Swal.fire({
      icon: 'info',
      title: 'Token activo',
      text: 'Quedan aproximadamente <?= $minutos_restantes ?> minutos.',
      confirmButtonText: 'Ok'
    });
<?php endif; ?>
</script>

</body>
</html>