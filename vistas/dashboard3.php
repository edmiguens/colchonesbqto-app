<?php
session_start();
ini_set('display_errors', 1);
error_reporting(E_ALL);

// 🔒 Verificar sesión
if (!isset($_SESSION['usuario_id'])) {
    header("Location: /ColchonesBqto/login.php");
    exit();
}

// 📦 Configuración global
$config = require __DIR__ . '/../src/config/config.php';

$modo          = $config['modo'];
$clientId      = $config[$modo]['ClientID'];
$clientSecret  = $config[$modo]['ClientSecret'];

$dbConfig      = $config['db'];

// 🛠️ Conexión a base de datos
try {
    $pdo = new PDO(
        "mysql:host={$dbConfig['host']};port={$dbConfig['puerto']};dbname={$dbConfig['basedatos']};charset=utf8",
        $dbConfig['usuario'],
        $dbConfig['clave'],
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]
    );
} catch (PDOException $e) {
    die("❌ Error de conexión: " . $e->getMessage());
}

// 📂 Servicios
require_once __DIR__ . '/../src/servicios/token_manager.php';
require_once __DIR__ . '/../src/servicios/QuickBooksService.php';

$userId     = $_SESSION['usuario_id'];
$tokenManager = new TokenManagerDB($pdo);
$qb          = new QuickBooksService($tokenManager, $userId, $clientId, $clientSecret);

// 🔁 Consultar clientes desde QuickBooks
$respuesta = $qb->consultarClientes();

$clientes = [];
if (isset($respuesta['QueryResponse']['Customer'])) {
    foreach ($respuesta['QueryResponse']['Customer'] as $item) {
        $clientes[] = [
            'nombre' => $item['DisplayName'] ?? 'Sin nombre',
            'deuda'  => $item['Balance'] ?? 0
        ];
    }
}

function calcularDeudaTotal($clientes) {
    return array_sum(array_column($clientes, 'deuda'));
}

usort($clientes, fn($a, $b) => $b['deuda'] <=> $a['deuda']);
$topClientes = array_slice($clientes, 0, 5);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Dashboard</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="bg-light">
<div class="container py-5">
    <h1 class="text-center mb-4">Dashboard</h1>

    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card text-bg-success">
                <div class="card-body">
                    <h5 class="card-title">Clientes Totales</h5>
                    <p class="card-text fs-3"><?= count($clientes) ?></p>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card text-bg-danger">
                <div class="card-body">
                    <h5 class="card-title">Deuda Acumulada</h5>
                    <p class="card-text fs-3">$<?= number_format(calcularDeudaTotal($clientes), 2) ?></p>
                </div>
            </div>
        </div>
    </div>

    <h4 class="text-center mb-3">Top 5 Clientes con Mayor Deuda</h4>
    <canvas id="graficaDeuda" height="100"></canvas>
    <script>
        const ctx = document.getElementById('graficaDeuda').getContext('2d');
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: <?= json_encode(array_column($topClientes, 'nombre')) ?>,
                datasets: [{
                    label: 'Deuda en $',
                    data: <?= json_encode(array_column($topClientes, 'deuda')) ?>,
                    backgroundColor: 'rgba(255, 99, 132, 0.6)',
                    borderColor: 'rgba(255, 99, 132, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Deuda ($)'
                        }
                    }
                }
            }
        });
    </script>

    <div class="mt-5 d-flex justify-content-center gap-3">
        <a href="usuarios.php" class="btn btn-outline-secondary">Administrar Usuarios</a>
        <a href="deudas.php" class="btn btn-outline-dark">📊 Ver Deudas por Cliente</a>
    </div>

    <div class="card mt-4 p-3 shadow-sm">
        <h5 class="mb-3">Conectar con QuickBooks Online</h5>
        <a href="../autorizar.php" class="btn btn-success me-2">Autorizar acceso</a>
        <a href="desconectar.php" class="btn btn-danger">Desconectar QuickBooks</a>
    </div>
</div>
</body>
</html>