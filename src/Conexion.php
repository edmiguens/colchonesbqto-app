<?php
date_default_timezone_set('America/Caracas');

// Detectar si estamos en Render (producción) o local
$esProduccion = getenv('RENDER') !== false;

// Parámetros de conexión según el entorno
if ($esProduccion) {
    $host     = getenv('DB_HOST') ?: 'mariadb.render.internal';
    $usuario  = getenv('DB_USER') ?: 'usuario_render';
    $clave    = getenv('DB_PASS') ?: 'clave_render';
    $basedatos= getenv('DB_NAME') ?: 'colchonesbqto';
    $puerto   = getenv('DB_PORT') ?: 3306;
} else {
    $host     = 'localhost';
    $usuario  = 'root';
    $clave    = 'jeshua';
    $basedatos= 'colchonesbqto';
    $puerto   = 3307; // puerto usado por MariaDB local en WAMP
}

// Conexión con mysqli
$conn = new mysqli($host, $usuario, $clave, $basedatos, $puerto);

// Validar conexión
if ($conn->connect_error) {
    echo "<script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
    <script>
      Swal.fire({
        icon: 'error',
        title: 'Error de conexión',
        html: 'No se pudo conectar a la base de datos:<br><b>{$conn->connect_error}</b>'
      });
    </script>";
    exit;
}
?>