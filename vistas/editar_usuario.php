<?php
session_start();
if (!isset($_SESSION["user_id"]) || $_SESSION["rol"] !== "admin") {
	$rutaLogin = $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'] . '/ColchonesBqto/login.php';
     header("Location: $rutaLogin");
     exit();
   
}

$conexion = new mysqli("127.0.0.1", "root", "jeshua", "colchonesbqto", 3307);

// Obtener datos del usuario a editar
if (isset($_GET["id"])) {
    $id = intval($_GET["id"]);
    $resultado = $conexion->query("SELECT * FROM usuarios WHERE id = $id");
    if ($resultado->num_rows === 0) {
        echo "Usuario no encontrado.";
        exit();
    }
    $usuario = $resultado->fetch_assoc();
}

// Procesar formulario
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id = intval($_POST["id"]);
    $nombre = $_POST["nombre"];
    $email = $_POST["email"];
    $rol = $_POST["rol"];
    $passwordOpcional = $_POST["nueva_password"];

    if (!empty($passwordOpcional)) {
        $nueva_password = password_hash($passwordOpcional, PASSWORD_DEFAULT);
        $stmt = $conexion->prepare("UPDATE usuarios SET nombre = ?, email = ?, rol = ?, password = ? WHERE id = ?");
        $stmt->bind_param("ssssi", $nombre, $email, $rol, $nueva_password, $id);
    } else {
        $stmt = $conexion->prepare("UPDATE usuarios SET nombre = ?, email = ?, rol = ? WHERE id = ?");
        $stmt->bind_param("sssi", $nombre, $email, $rol, $id);
    }

    if ($stmt->execute()) {
        header("Location: usuarios.php");
        exit();
    } else {
        $mensaje = "❌ Error al actualizar usuario: " . $stmt->error;
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Editar Usuario</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-5">
  <h3 class="mb-4">✏️ Editar Usuario</h3>

  <?php if (isset($mensaje)) echo "<div class='alert alert-danger'>$mensaje</div>"; ?>

  <form method="POST">
    <input type="hidden" name="id" value="<?= $usuario["id"] ?>">
    
    <div class="mb-3">
      <label class="form-label">Nombre:</label>
      <input type="text" name="nombre" value="<?= htmlspecialchars($usuario["nombre"]) ?>" class="form-control" required>
    </div>
    
    <div class="mb-3">
      <label class="form-label">Email:</label>
      <input type="email" name="email" value="<?= htmlspecialchars($usuario["email"]) ?>" class="form-control" required>
    </div>
    
    <div class="mb-3">
      <label class="form-label">Rol:</label>
      <select name="rol" class="form-select" required>
        <option value="cliente" <?= $usuario["rol"] === "cliente" ? "selected" : "" ?>>Cliente</option>
        <option value="vendedor" <?= $usuario["rol"] === "vendedor" ? "selected" : "" ?>>Vendedor</option>
        <option value="admin" <?= $usuario["rol"] === "admin" ? "selected" : "" ?>>Administrador</option>
      </select>
    </div>
    
    <div class="mb-3">
      <label class="form-label">Nueva contraseña (opcional):</label>
      <input type="password" name="nueva_password" class="form-control" minlength="6">
      <div class="form-text">Déjalo vacío si no deseas cambiarla.</div>
    </div>
    
    <button type="submit" class="btn btn-primary">💾 Guardar cambios</button>
    <a href="usuarios.php" class="btn btn-secondary">Cancelar</a>
  </form>
</div>
</body>
</html>