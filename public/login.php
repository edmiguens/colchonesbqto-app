<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../src/Config/conexion.php';
require_once __DIR__ . '/../src/Config/config.php';
$config = require __DIR__ . '/../src/Config/config.php';
//$config = require __DIR__ . '/src/Config/config.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
  $cedula = strtoupper(trim($_POST["cedula"] ?? ''));
  $contrasena = trim($_POST["contrasena"] ?? '');

  if (!preg_match('/^[VEJ]\d{7,10}$/', $cedula)) {
    $error = "Cédula/RIF inválido. Debe comenzar con V, E o J y contener de 7 a 10 dígitos.";
  } elseif ($contrasena === '') {
    $error = "Debes ingresar la contraseña.";
  } else {
    $stmt = $conn->prepare("SELECT id, nombre, password, rol FROM usuarios WHERE cedula = ?");
    $stmt->bind_param("s", $cedula);
    $stmt->execute();
    $resultado = $stmt->get_result();

    if ($resultado->num_rows === 1) {
      $fila = $resultado->fetch_assoc();
      if (password_verify($contrasena, $fila["password"])) {
        $_SESSION["user_id"] = $fila["id"];
		$_SESSION["nombre"]  = $fila["nombre"];
        $_SESSION["rol"]     = $fila["rol"];
        $userId              = $fila["id"];

        // 🔐 Manejo del token solo después del login exitoso
		require_once __DIR__ . '/../src/Config/config.php';

        $dbConfig     = $config['db'];
        $modo         = $config['modo'];
        $clientId     = $config[$modo]['ClientID'];
        $clientSecret = $config[$modo]['ClientSecret'];

        $pdo = new PDO(
          "mysql:host={$dbConfig['host']};port={$dbConfig['puerto']};dbname={$dbConfig['basedatos']};charset=utf8",
          $dbConfig['usuario'],
          $dbConfig['clave'],
          [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
        );
          require_once __DIR__ . '/../src/servicios/token_manager.php';
        $tokenManager = new TokenManagerDB($pdo);

        // 🌐 Redirigir si el token aún no existe
        if (!$tokenManager->existe($userId)) {
		  require_once __DIR__ . '/../src/servicios/iniciar_conexion_quickbooks.php';
		  //header("Location: src/servicios/iniciar_conexion_quickbooks.php");
          exit();
        }

        header("Location: vistas/dashboard.php");
        exit;
      } else {
        $error = "Contraseña incorrecta.";
      }
    } else {
      $error = "Cédula no registrada.";
    }
  }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Inicio de Sesión</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body class="bg-light d-flex justify-content-center align-items-center vh-100">
  <form method="POST" class="bg-white p-5 rounded shadow" style="width: 100%; max-width: 400px;">
    <h2 class="mb-4 text-center">🔐 Iniciar Sesión</h2>

    <div class="mb-3">
      <label for="cedula" class="form-label">🆔 Cédula o RIF</label>
      <input type="text" name="cedula" id="cedula" class="form-control" maxlength="11" required placeholder="Ej: V12345678" autocomplete="off">
    </div>

    <div class="mb-3">
      <label for="contrasena" class="form-label">🔒 Contraseña</label>
      <input type="password" name="contrasena" id="contrasena" class="form-control" required>
    </div>

    <button type="submit" class="btn btn-primary w-100">Ingresar</button>
  </form>

  <?php if (isset($error)): ?>
    <script>
      Swal.fire({
        icon: 'error',
        title: 'Acceso denegado',
        text: '<?= addslashes($error) ?>'
      });
    </script>
  <?php endif; ?>
</body>
</html>