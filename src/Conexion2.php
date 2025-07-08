<?php
$host     = "localhost";
$usuario  = "root";
$clave    = "jeshua"; // Cambia si tu MySQL tiene contraseña
$basedatos = "colchonesbqto"; // Asegúrate de que esta base exista

$conn = new mysqli($host, $usuario, $clave, $basedatos,3307);

// Verificar conexión
if ($conn->connect_error) {
  die("Error de conexión: " . $conn->connect_error);
}
?>
