<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Verificar Token</title>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body style="padding: 2rem; background: #f8f9fa;">
<?php
date_default_timezone_set('America/Caracas');

$tokenFile = sys_get_temp_dir() . '/token.json';
//$tokenFile = 'token.json';

if (!file_exists($tokenFile)) {
    echo "<script>
        Swal.fire({
            icon: 'error',
            title: 'Token no encontrado',
            text: 'El archivo token.json no existe o no fue generado aún.',
            footer: 'Por favor autoriza la app desde /autorizar.php'
        });
    </script>";
    exit;
}

$tokenData = json_decode(file_get_contents($tokenFile), true);

// Verificar campos clave
$accessToken  = $tokenData['access_token'] ?? null;
$expiresIn    = $tokenData['expires_in'] ?? 0;
$fechaGenerado = filemtime($tokenFile);
$fechaExpira   = $fechaGenerado + $expiresIn;
$ahora         = time();
$minRestantes  = round(($fechaExpira - $ahora) / 60);

if (!$accessToken) {
    echo "<script>
        Swal.fire({
            icon: 'error',
            title: 'Token inválido',
            text: 'No se encontró access_token válido en token.json.'
        });
    </script>";
    exit;
}

if ($ahora < $fechaExpira) {
    echo "<script>
        Swal.fire({
            icon: 'success',
            title: 'Token activo',
            html: 'Tu access_token expira en aproximadamente <b>$minRestantes minutos</b>.<br><br>
                   Fecha de expiración:<br><b>" . date('d/m/Y H:i:s', $fechaExpira) . "</b>'
        });
    </script>";
} else {
    echo "<script>
        Swal.fire({
            icon: 'warning',
            title: 'Token expirado',
            html: 'El access_token ha caducado.<br><br>
                   Fecha de expiración:<br><b>" . date('d/m/Y H:i:s', $fechaExpira) . "</b><br><br>
                   Ejecuta el flujo de renovación o reautoriza la app.'
        });
    </script>";
}
?>
</body>
</html>