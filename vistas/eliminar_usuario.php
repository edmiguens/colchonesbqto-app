<?php
session_start();
if (!isset($_SESSION["user_id"]) || $_SESSION["rol"] !== "admin") {
	$rutaLogin = $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'] . '/ColchonesBqto/login.php';
    header("Location: $rutaLogin");
    exit();
}

if (isset($_GET["id"])) {
    $id = intval($_GET["id"]);

    // Evitar que el admin se elimine a sí mismo
    if ($_SESSION["user_id"] == $id) {
        echo "❌ No puedes eliminar tu propia cuenta.";
        exit();
    }

    $conexion = new mysqli("127.0.0.1", "root", "jeshua", "colchonesbqto", 3307);
    $conexion->query("DELETE FROM usuarios WHERE id = $id");

	header("Location: usuarios.php?mensaje=eliminado");
    exit();
} else {
    echo "ID de usuario no especificado.";
}
?>