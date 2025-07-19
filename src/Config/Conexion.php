<?php
$host      = "localhost";
$usuario   = "root";
$clave     = "jeshua"; // Cambia si tu MySQL tiene contraseña
$basedatos = "colchonesbqto"; // Asegúrate de que esta base exista
$puerto    = 3307; // Si usas WAMP con puerto personalizado

$conn = new mysqli($host, $usuario, $clave, $basedatos, $puerto);

// ⛔ Verificar conexión
if ($conn->connect_error) {
    die("❌ Error de conexión a la base de datos: " . $conn->connect_error);
}

// ✅ Opcional: configurar charset si trabajas con textos
$conn->set_charset("utf8");
?>