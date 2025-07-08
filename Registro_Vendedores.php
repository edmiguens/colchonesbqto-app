<?php
if ($_SERVER["REQUEST_METHOD"] === "POST") {
  include("conexion.php"); // Asegúrate que este archivo establezca correctamente la conexión a MariaDB

  // Sanitizar y normalizar cédula con prefijo V
  $cedula = strtoupper(trim($_POST["cedula"]));
  if (!preg_match("/^V\d+$/", $cedula)) {
    $cedula = "V" . preg_replace("/[^0-9]/", "", $cedula);
  }

  $nombre = trim($_POST["nombre"]);
  $telefono = trim($_POST["telefono"]);
  $codigo_vendedor = strtoupper(trim($_POST["codigo_vendedor"]));
  $clave = $_POST["clave"];
  $clave_hash = password_hash($clave, PASSWORD_DEFAULT);

  // Verificar duplicados por cédula o código
  $sql_verifica = "SELECT id FROM vendedores WHERE cedula = ? OR codigo_vendedor = ?";
  $stmt_verifica = $conn->prepare($sql_verifica);
  $stmt_verifica->bind_param("ss", $cedula, $codigo_vendedor);
  $stmt_verifica->execute();
  $stmt_verifica->store_result();

  if ($stmt_verifica->num_rows > 0) {
    echo "<script>alert('Ya existe un vendedor con esa cédula o código.');</script>";
  } else {
    // Guardar vendedor
    $sql = "INSERT INTO vendedores (cedula, nombre, telefono, codigo_vendedor, clave_hash)
            VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssss", $cedula, $nombre, $telefono, $codigo_vendedor, $clave_hash);
    
    if ($stmt->execute()) {
      echo "<script>alert('Registro exitoso. Puedes iniciar sesión ahora.'); window.location='login.php';</script>";
    } else {
      echo "<script>alert('Error al registrar: " . addslashes($stmt->error) . "');</script>";
    }
  }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Registro de Vendedor</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
</head>
<body class="bg-light">
<div class="container mt-5">
  <h2 class="mb-4 text-center">📝 Crear cuenta de vendedor</h2>
  <form method="POST" action="" class="bg-white p-4 rounded shadow-sm">
    <div class="mb-3">
      <label for="cedula" class="form-label">Cédula</label>
      <input type="text" name="cedula" class="form-control" placeholder="Ej: 12345678" required>
    </div>
    <div class="mb-3">
      <label for="nombre" class="form-label">Nombre completo</label>
      <input type="text" name="nombre" class="form-control" required>
    </div>
    <div class="mb-3">
      <label for="telefono" class="form-label">Teléfono</label>
      <input type="text" name="telefono" class="form-control">
    </div>
    <div class="mb-3">
      <label for="codigo_vendedor" class="form-label">Código único de vendedor</label>
      <input type="text" name="codigo_vendedor" class="form-control" required>
    </div>
    <div class="mb-3">
      <label for="clave" class="form-label">Contraseña</label>
      <input type="password" name="clave" class="form-control" required>
    </div>
    <button type="submit" class="btn btn-primary w-100">Registrarse</button>
  </form>
</div>
</body>
</html>