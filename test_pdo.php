<?php
ini_set('display_errors','1');
ini_set('display_startup_errors','1');
error_reporting(E_ALL);

$host = '127.0.0.1';   // forzamos TCP
$ports = [3307, 3306]; // probamos ambos
$db   = 'colchonesbqto';
$user = 'root';
$pass = 'jeshua';

foreach ($ports as $port) {
    echo "Probando puerto {$port}:<br>";
    try {
        $pdo = new PDO(
            "mysql:host={$host};port={$port};dbname={$db};charset=utf8mb4",
            $user,
            $pass,
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
        );
        echo "<span style='color:green;'>✔ Conexión OK en {$port}</span><br><br>";
    } catch (PDOException $e) {
        echo "<span style='color:red;'>✘ Error en {$port}: {$e->getMessage()}</span><br><br>";
    }
}