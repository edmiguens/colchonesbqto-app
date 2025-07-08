<?php
session_start();
require_once 'conexion.php'; // Usa la conexión centralizada

$mensaje = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $cedula = strtoupper(trim($_POST["cedula"]));
    if (!preg_match("/^V\d+$/", $cedula)) {
        $cedula = "V" . preg_replace("/[^0-9]/", "", $cedula);
    }

    $clave = $_POST["password"];

    $sql = "SELECT id, nombre, password, rol FROM usuarios WHERE cedula = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $cedula);
    $stmt->execute();
    $resultado = $stmt->get_result();

    if ($resultado->num_rows > 0) {
        $usuario = $resultado->fetch_assoc();
        if (password_verify($clave, $usuario["password"])) {
            $_SESSION["usuario"] = $usuario["nombre"];
            $_SESSION["rol"] = $usuario["rol"];
            $_SESSION["usuario_id"] = $usuario["id"];
            header("Location: dashboard.php");
            exit();
        } else {
            $mensaje = "⚠️ Contraseña incorrecta.";
        }
    } else {
		    $mensaje = "❌ Usuario no encontrado.";
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Colchones BQTO - Inicio de Sesión</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
  <style>
    body {
      background-color: #f2f2f2;
    }
    .login-card {
      max-width: 400px;
      margin: auto;
      margin-top: 60px;
      box-shadow: 0 4px 8px rgba(0,0,0,0.2);
      border-radius: 6px;
      overflow: hidden;
    }
    .colchon-img {
      width: 100%;
      height: auto;
      border-bottom: 3px solid #007bff;
    }
    .input-group-text {
      background-color: #007bff;
      color: white;
      border: none;
    }
  </style>
</head>
<body>

<div class="card login-card">
  <img src="img/colchon.jpg" alt="Colchón decorativo" class="colchon-img">
  <div class="card-body">
    <?php if ($mensaje): ?>
      <div class='alert alert-warning text-center'><?= $mensaje ?></div>
    <?php endif; ?>
    <form method="POST">
      <div class="mb-3">
        <label class="form-label">Cédula:</label>
        <div class="input-group">
          <span class="input-group-text"><i class="fas fa-user"></i></span>
          <input type="text" name="cedula" class="form-control" placeholder="Ej: 12345678" required>
        </div>
      </div>
      <div class="mb-3">
        <label class="form-label">Contraseña:</label>
        <div class="input-group">
          <span class="input-group-text"><i class="fas fa-lock"></i></span>
          <input type="password" name="password" class="form-control" minlength="6" required>
        </div>
      </div>
      <button type="submit" class="btn btn-primary w-100">Ingresar</button>
    </form>
  </div>
</div>

</body>
</html>