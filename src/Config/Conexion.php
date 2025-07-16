<?php
// 🌍 Detectar entorno automáticamente
$esProduccion = isset($_SERVER['RENDER']) || getenv('RENDER');

if ($esProduccion) {
    // 🔒 Entorno Render (producción)
    $host      = getenv("DB_HOST");
    $usuario   = getenv("DB_USER");
    $clave     = getenv("DB_PASS");
    $basedatos = getenv("DB_NAME");
    $puerto    = getenv("DB_PORT") ?: 3306;
} else {
    // 🧪 Entorno local (WAMP)
    $host      = "localhost";
    $usuario   = "root";
    $clave     = "jeshua";
    $basedatos = "colchonesbqto";
    $puerto    = 3307;
}

// 🛠️ Crear conexión MySQLi
$conn = new mysqli($host, $usuario, $clave, $basedatos, $puerto);

// ⛔ Verificar conexión
if ($conn->connect_error) {
    die("❌ Error de conexión a la base de datos: " . $conn->connect_error);
}

// ✅ Configurar charset
$conn->set_charset("utf8");