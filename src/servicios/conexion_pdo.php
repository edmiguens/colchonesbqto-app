<?php
$host      = "localhost";
$usuario   = "root";
$clave     = "jeshua"; // ← tu contraseña de MariaDB
$basedatos = "colchonesbqto"; // ← nombre verificado de la base
$puerto    = 3307; // ← puerto personalizado en WAMP

try {
  $pdo = new PDO("mysql:host=$host;port=$puerto;dbname=$basedatos", $usuario, $clave, [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
  ]);
  // Mensaje temporal para verificar
  // echo "<h4>✅ Conexión PDO establecida con '$basedatos'</h4>";
} catch (PDOException $e) {
  die("❌ Error de conexión PDO: " . $e->getMessage());
}