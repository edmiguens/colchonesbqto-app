<?php
session_start();
if (!isset($_SESSION["usuario"])) {
    header("Location: login.php");
    exit();
}

$usuario = $_SESSION["usuario"];
$rol = $_SESSION["rol"];
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Dashboard</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
function mostrarEnProceso() {
  Swal.fire({
    title: '?? En construcci?n',
    text: 'Esta secci?n estar? disponible pr?ximamente.',
    icon: 'info',
    confirmButtonText: 'Entendido',
    confirmButtonColor: '#3085d6'
  });
}
</script>

</head>
<body class="bg-light">
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
  <div class="container-fluid">
    <span class="navbar-brand">Bienvenido, <?= htmlspecialchars($usuario) ?> ??</span>
    <div class="d-flex">
      <a href="logout.php" class="btn btn-outline-light">Cerrar sesión</a>
    </div>
  </div>
</nav>

<div class="container mt-4">
  <div class="alert alert-info">
    Tu Rol Actual es: <strong><?= htmlspecialchars($rol) ?></strong>
  </div>

  <?php if ($rol === "admin"): ?>
    <div class="card border-primary mb-3">
      <div class="card-header bg-primary text-white">Administraci?n</div>
	  <div class="card-body">
        <p class="card-text">?? Acceso completo al sistema.</p>
        <ul>
          <li>Gestionar usuarios</li>
          <li>Revisar registros</li>
          <li>Ver estad?sticas</li>
        </ul>
		<div class="d-flex justify-content-center gap-3 mt-4">
		  <a href="Registro.php" class="btn btn-outline-primary mt-3">🧔 Registrar Nuevo Usuario</a>
		  <a href="usuarios.php" class="btn btn-outline-primary mt-3">👯‍♀️ Ver Usuarios Registrados</a>
		  <a href="clientes.php" class="btn btn-outline-primary mt-3">💲 Cuentas por Cobrar</a>
		</div>
      </div>
    </div>
  <?php else: ?>
    <div class="card border-success mb-3">
      <div class="card-header bg-success text-white">Panel de Usuario</div>
      <div class="card-body">
        <p class="card-text">?? Bienvenido a tu zona personal.</p>
        <ul>
          <li>Visualizar tu perfil</li>
          <li>Actualizar informaci?n</li>
          <li>Explorar contenidos</li>
        </ul>
		<div class="d-flex justify-content-center gap-3 mt-4">
 		<a href="#" class="btn btn-outline-primary mt-3" onclick="mostrarEnProceso()">??Consultar Cuentas por Pagar</a>
		</div>
      </div>
    </div>
  <?php endif; ?>
</div>
</body>
</html>