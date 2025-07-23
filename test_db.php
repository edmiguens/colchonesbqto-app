<?php
$host = '127.0.0.1';        // o 'localhost'
$port = 3307;              // ajusta aquí al puerto real (3306 o 3307)
$user = 'root';
$pass = 'jeshua';          // tu contraseña real
$db   = 'colchonesbqto';

echo "Intentando conectar a {$host}:{$port}…<br>";
$fp = @fsockopen($host, $port, $errno, $errstr, 2);
if (! $fp) {
    die("No responde el puerto: [$errno] $errstr");
}
fclose($fp);
echo "Puerto abierto. Ahora pruebo PDO…<br>";

try {
    $dsn = "mysql:host={$host};port={$port};dbname={$db};charset=utf8mb4";
    $pdo = new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
    echo "¡Conexión PDO exitosa!";
} catch (PDOException $e) {
    die("PDOException: " . $e->getMessage());
}