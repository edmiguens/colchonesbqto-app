<?php
session_start();
if (!isset($_SESSION["usuario"]) || $_SESSION["rol"] !== "admin") {
    header("Location: login.php");
    exit();
}

require_once 'conexion.php'; // Usamos la conexión modular
$resultado = $conn->query("SELECT id, nombre, email, cedula, rol, fecha_registro FROM usuarios Order By nombre");
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Usuarios Registrados</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body class="bg-light">
<div class="container mt-5">

  <?php if (isset($_GET["mensaje"]) && $_GET["mensaje"] === "eliminado"): ?>
    <div class="alert alert-success alert-dismissible fade show text-center" role="alert">
      ✅ Usuario Eliminado Correctamente.
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
    </div>
  <?php endif; ?>

  <h2 class="mb-4 text-center">👥 Usuarios Registrados</h2>

  <div class="mb-3 d-flex justify-content-between">
    <a href="dashboard.php" class="btn btn-secondary">⬅️ Volver al Dashboard</a>
    <a href="Registro.php" class="btn btn-primary">➕ Nuevo Usuario</a>
  </div>

  <table class="table table-bordered table-hover align-middle">
    <thead class="table-dark text-center">
      <tr>
        <th>ID</th>
        <th>Nombre</th>
        <th>Email</th>
        <th>Cédula/RIF</th> <!-- Columna añadida -->
        <th>Rol</th>
        <th>Registro</th>
        <th>Acciones</th>
      </tr>
    </thead>
    <tbody>
      <?php while ($fila = $resultado->fetch_assoc()): ?>
        <tr>
          <td class="text-center"><?= $fila["id"] ?></td>
          <td><?= htmlspecialchars($fila["nombre"]) ?></td>
          <td><?= htmlspecialchars($fila["email"]) ?></td>
          <td class="text-center"><?= htmlspecialchars($fila["cedula"]) ?></td> <!-- Celda añadida -->
          <td class="text-center"><?= $fila["rol"] ?></td>
          <td class="text-center"><?= $fila["fecha_registro"] ?></td>
          <td class="text-center">
            <a href="editar_usuario.php?id=<?= $fila["id"] ?>" class="btn btn-sm btn-info me-1">✏️ Editar</a>
            <a href="#" class="btn btn-sm btn-danger" onclick="confirmarEliminacion(<?= $fila['id'] ?>)">🗑️ Eliminar</a>
          </td>
        </tr>
      <?php endwhile; ?>
    </tbody>
  </table>
</div>

<script>
function confirmarEliminacion(id) {
  Swal.fire({
    title: '¿Estás seguro?',
    text: "Esta acción eliminará al usuario permanentemente.",
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#d33',
    cancelButtonColor: '#6c757d',
    confirmButtonText: 'Sí, eliminar',
    cancelButtonText: 'Cancelar'
  }).then((result) => {
    if (result.isConfirmed) {
      window.location.href = 'eliminar_usuario.php?id=' + id;
    }
  });
}
</script>
</body>
</html>