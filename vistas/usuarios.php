<?php
session_start();
if (!isset($_SESSION["user_id"]) || $_SESSION["rol"] !== "admin") {
    header("Location: /ColchonesBqto/login.php");
    exit();
}
require_once __DIR__ . '/../src/Config/conexion.php';

$resultado = $conn->query("SELECT id, nombre, email, cedula, rol, fecha_registro FROM usuarios ORDER BY nombre");
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

  <div class="mb-3 d-flex justify-content-between align-items-center">
    <a href="dashboard.php" class="btn btn-secondary">⬅️ Volver al Dashboard</a>
    <a href="Registra_usuarios.php" class="btn btn-primary">➕ Nuevo Usuario</a>
  </div>

  <!-- 🔍 Filtros -->
  <div class="mb-4 d-flex justify-content-between align-items-center">
    <div class="input-group w-50 me-2">
      <span class="input-group-text">Buscar</span>
      <input type="text" id="busqueda" class="form-control" placeholder="Filtrar por nombre...">
    </div>
    <div>
      <select id="filtroRol" class="form-select">
        <option value="">Todos los roles</option>
        <option value="admin">Admin</option>
        <option value="vendedor">Vendedor</option>
        <option value="cliente">Cliente</option>
      </select>
    </div>
  </div>

  <!-- 📋 Tabla de usuarios -->
  <table class="table table-bordered table-hover align-middle">
    <thead class="table-dark text-center">
      <tr>
        <th>ID</th>
        <th>Nombre</th>
        <th>Email</th>
        <th>Cédula/RIF</th>
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
          <td class="text-center"><?= htmlspecialchars($fila["cedula"]) ?></td>
          <td class="text-center">
            <?php
              $rol = strtolower($fila["rol"]);
              if ($rol === "admin") {
                echo '<span class="badge bg-primary">🛡️ Admin</span>';
              } elseif ($rol === "vendedor") {
                echo '<span class="badge bg-success">💼 Vendedor</span>';
              } elseif ($rol === "cliente") {
                echo '<span class="badge bg-secondary">🧑 Cliente</span>';
              } else {
                echo htmlspecialchars($fila["rol"]);
              }
            ?>
          </td>
          <td class="text-center"><?= $fila["fecha_registro"] ?></td>
          <td class="text-center">
  <div class="d-inline-flex gap-2">
    <a href="editar_usuario.php?id=<?= $fila["id"] ?>" class="btn btn-sm btn-outline-primary">✏️ Editar</a>
    <a href="#" class="btn btn-sm btn-outline-danger" onclick="confirmarEliminacion(<?= $fila['id'] ?>, '<?= addslashes($fila['nombre']) ?>')">🗑️ Eliminar</a>
  </div>
</td>
        </tr>
      <?php endwhile; ?>
    </tbody>
  </table>
</div>

<!-- 🧠 Scripts de búsqueda y filtro -->
<script>
const busquedaInput = document.getElementById("busqueda");
const filtroRol = document.getElementById("filtroRol");

busquedaInput.addEventListener("input", filtrarTabla);
filtroRol.addEventListener("change", filtrarTabla);

function filtrarTabla() {
  const texto = busquedaInput.value.toLowerCase();
  const rol = filtroRol.value.toLowerCase();
  const filas = document.querySelectorAll("table tbody tr");

  filas.forEach(fila => {
    const nombre = fila.cells[1].textContent.toLowerCase();
    const rolActual = fila.cells[4].textContent.toLowerCase();

    const coincideNombre = nombre.includes(texto);
    const coincideRol = rol === "" || rolActual.includes(rol);

    fila.style.display = (coincideNombre && coincideRol) ? "" : "none";
  });
}

function confirmarEliminacion(id, nombre) {
  Swal.fire({
    title: '¿Estás seguro?',
    html: `Esta acción eliminará al usuario <strong>${nombre}</strong> permanentemente.`,
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#d33',
    cancelButtonColor: '#6c757d',
    confirmButtonText: 'Sí, eliminar',
    cancelButtonText: 'Cancelar'
  }).then((result) => {
    if (result.isConfirmed) {
		const ruta = `${window.location.origin}/ColchonesBqto/vistas/eliminar_usuario.php?id=${id}`;
        window.location.href = ruta;
    }
  });
}

</script>
</body>
</html>