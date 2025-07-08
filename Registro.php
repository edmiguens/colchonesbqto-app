<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
require_once 'conexion.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
  $nombre     = trim($_POST['nombre'] ?? '');
  $email      = trim($_POST['email'] ?? '');
  $tipoCedula = trim($_POST['cedula_tipo'] ?? '');
  $numCedula  = trim($_POST['cedula_numero'] ?? '');
  $telefono   = trim($_POST['telefono'] ?? '');
  $direccion  = trim($_POST['direccion'] ?? '');
  $rol        = trim($_POST['rol'] ?? '');
  $contrasena = trim($_POST['contrasena'] ?? '');
  $confirmar  = trim($_POST['confirmar'] ?? '');
  $cedula     = $tipoCedula . $numCedula;

  if ($nombre === '' || $email === '' || $cedula === '' || $contrasena === '' || $confirmar === '' || $rol === '') {
    echo "<!DOCTYPE html><html><head><meta charset='UTF-8'>
    <script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script></head><body>
    <script>
      Swal.fire({icon:'error',title:'Campos obligatorios',text:'Completa todos los campos requeridos.'})
      .then(() => { window.location.href = 'registro.php'; });
    </script></body></html>";
    exit;
  }

  if (!preg_match('/^[VEJ]\d{7,10}$/', $cedula)) {
    echo "<!DOCTYPE html><html><head><meta charset='UTF-8'>
    <script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script></head><body>
    <script>
      Swal.fire({icon:'error',title:'Cédula/RIF inválido',text:'Debe comenzar con V, E o J seguido de 7 a 10 dígitos.'})
      .then(() => { window.location.href = 'registro.php'; });
    </script></body></html>";
    exit;
  }

  if ($contrasena !== $confirmar) {
    echo "<!DOCTYPE html><html><head><meta charset='UTF-8'>
    <script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script></head><body>
    <script>
      Swal.fire({icon:'error',title:'Contraseñas no coinciden',text:'Asegúrate de que ambas coincidan.'})
      .then(() => { window.location.href = 'registro.php'; });
    </script></body></html>";
    exit;
  }

  $verifica = $conn->prepare("SELECT id FROM usuarios WHERE cedula = ?");
  $verifica->bind_param("s", $cedula);
  $verifica->execute();
  $verifica->store_result();

  if ($verifica->num_rows > 0) {
    echo "<!DOCTYPE html><html><head><meta charset='UTF-8'>
    <script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script></head><body>
    <script>
      Swal.fire({icon:'warning',title:'Usuario existente',text:'Ya existe un usuario con esa cédula o RIF.'})
      .then(() => { window.location.href = 'registro.php'; });
    </script></body></html>";
    exit;
  }

  $hash = password_hash($contrasena, PASSWORD_DEFAULT);
  $query = "INSERT INTO usuarios (nombre, email, cedula, telefono, direccion, password, rol) VALUES (?, ?, ?, ?, ?, ?, ?)";
  $stmt  = $conn->prepare($query);
  $stmt->bind_param("sssssss", $nombre, $email, $cedula, $telefono, $direccion, $hash, $rol);

  if ($stmt->execute()) {
    echo "<!DOCTYPE html><html><head><meta charset='UTF-8'>
    <script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script></head><body>
    <script>
      Swal.fire({icon:'success',title:'Registro exitoso',text:'Redirigiendo...'})
      .then(() => { window.location.href = 'dashboard.php'; });
    </script></body></html>";
    exit;
  } else {
    echo "<!DOCTYPE html><html><head><meta charset='UTF-8'>
    <script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script></head><body>
    <script>
      Swal.fire({icon:'error',title:'Error al registrar',text:'Inténtalo nuevamente más tarde.'})
      .then(() => { window.location.href = 'registro.php'; });
    </script></body></html>";
    exit;
  }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Registro de Usuario</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <script>
    function validarFormulario() {
      const form       = document.forms[0];
      const nombre     = form.nombre.value.trim();
      const email      = form.email.value.trim();
      const tipoCedula = form.cedula_tipo.value.trim();
      const numCedula  = form.cedula_numero.value.trim();
      const cedula     = tipoCedula + numCedula;
      const rol        = form.rol.value.trim();
      const contrasena = form.contrasena.value.trim();
      const confirmar  = form.confirmar.value.trim();

      if (!nombre || !email || !tipoCedula || !numCedula || !rol || !contrasena || !confirmar) {
        Swal.fire({ icon: 'warning', title: 'Campos vacíos', text: 'Completa todos los campos obligatorios.' });
        return false;
      }

      if (!/^[VEJ]\d{7,10}$/.test(cedula)) {
        Swal.fire({ icon: 'warning', title: 'Cédula/RIF inválido', text: 'Debe comenzar con V, E o J seguido de 7 a 10 dígitos.' });
        return false;
      }

      if (contrasena !== confirmar) {
        Swal.fire({ icon: 'warning', title: 'Contraseñas no coinciden', text: 'Asegúrate de que coincidan.' });
        return false;
      }

      return true;
    }
  </script>
</head>
<body class="container py-5">

  <div class="text-center mb-4">
    <a href="dashboard.php" class="btn btn-outline-primary" style="border-radius: 8px; padding: 10px 20px; font-weight: bold; text-transform: uppercase;">
      <i class="fas fa-arrow-left"></i> Volver al Dashboard
    </a>
  </div>

  <form method="post" onsubmit="return validarFormulario();">
    <div class="mb-3">
      <label for="nombre" class="form-label">👤 Nombre completo</label>
      <input type="text" name="nombre" id="nombre" class="form-control" required>
    </div>

    <div class="mb-3">
      <label for="email" class="form-label">📧 Correo electrónico</label>
      <input type="email" name="email" id="email" class="form-control" required>
    </div>

    <div class="mb-3 row">
      <label for="cedula_numero" class="form-label col-12">🆔 Cédula o RIF</label>
      <div class="col-3">
        <select name="cedula_tipo" id="cedula_tipo" class="form-select" required>
          <option value="V">V</option>
          <option value="E">E</option>
          <option value="J">J</option>
        </select>
      </div>
      <div class="col-9">
        <input type="text" name="cedula_numero" id="cedula_numero" class="form-control" required>
      </div>
    </div>

    <div class="mb-3">
      <label for="telefono" class="form-label">📞 Teléfono</label>
      <input type="text" name="telefono" id="telefono" class="form-control">
    </div>

    <div class="mb-3">
      <label for="direccion" class="form-label">🏠 Dirección</label>
      <input type="text" name="direccion" id="direccion" class="form-control">
    </div>

    <div class="mb-3">
      <label for="rol" class="form-label">🧑‍💼 Rol</label>
      <select name="rol" id="rol" class="form-select" required>
        <option value="">-- Selecciona el rol --</option>
        <option value="usuario">Usuario</option>
        <option value="vendedor">Vendedor</option>
      </select>
    </div>

    <div class="mb-3">
      <label for="contrasena" class="form-label">🔒 Contraseña</label>
      <input type="password" name="contrasena" id="contrasena" class="form-control" required>
    </div>

    <div class="mb-3">
      <label for="confirmar" class="form-label">🔁 Confirmar contraseña</label>
      <input type="password" name="confirmar" id="confirmar" class="form-control" placeholder="Repite la contraseña" required>
    </div>

    <div class="text-center mt-4">
      <button type="submit" class="btn btn-success" style="border-radius: 8px; padding: 10px 20px; font-weight: bold; text-transform: uppercase;">
        📝 Registrarse
      </button>
    </div>
  </form>
</body>
</html>