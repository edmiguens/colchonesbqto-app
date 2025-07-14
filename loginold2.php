<?php
session_start();
require_once __DIR__ . '/src/Config/conexion.php';

$mensaje = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $cedula = strtoupper(trim($_POST["cedula"])); // Sanear y estandarizar
	$clave = isset($_POST["clave"]) ? trim($_POST["clave"]) : "";
    
    // Consulta ajustada
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
			//echo "Entrando al dashboard..."; exit();
			header("Location: vistas/dashboard.php");
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
    <title>Ingreso por Cédula</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-4">
            <h3 class="text-center mb-4">Inicio de Sesión</h3>

            <?php if ($mensaje): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($mensaje) ?></div>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="mb-3">
                    <label for="cedula" class="form-label">Cédula</label>
                    <input type="text" name="cedula" class="form-control" required placeholder="Ej: V12345678">
                </div>
                <div class="mb-3">
                    <label for="clave" class="form-label">Contraseña</label>
                    <input type="password" name="clave" class="form-control" required>
                </div>
                <button type="submit" class="btn btn-primary w-100">Entrar</button>
            </form>
        </div>
    </div>
</div>
</body>
</html>