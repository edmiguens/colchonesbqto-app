<?php
session_start();
if (!isset($_SESSION["usuario"]) || $_SESSION["rol"] !== "admin") {
    header("Location: login.php");
    exit();
}

if (isset($_GET["id"])) {
    $id = intval($_GET["id"]);

    // Evitar que el admin se elimine a sí mismo
    if ($_SESSION["usuario_id"] == $id) {
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