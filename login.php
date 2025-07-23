<?php
declare(strict_types=1);

// ——————————————
// Sesión y debug (solo desarrollo)
// ——————————————
session_name('app_session');
session_start();
ini_set('display_errors',        '1');
ini_set('display_startup_errors','1');
error_reporting(E_ALL);

// ——————————————
// 1. Conexión MySQLi (define $conn, $host, $usuario, $clave, $basedatos, $puerto)
// ——————————————
require __DIR__ . '/src/Config/conexion.php';

// ——————————————
// 2. Crear PDO para TokenManagerDB (forzar TCP en localhost)
// ——————————————
$pdoHost = '127.0.0.1';        // siempre TCP
$pdoPort = $puerto;
$dsn     = sprintf(
    'mysql:host=%s;port=%d;dbname=%s;charset=utf8mb4',
    $pdoHost,
    $pdoPort,
    $basedatos
);

try {
    $pdo = new PDO($dsn, $usuario, $clave, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
} catch (PDOException $e) {
    exit("❌ Error de conexión PDO: " . $e->getMessage());
}

// ——————————————
// 3. Autoload y TokenManager
// ——————————————
require __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/src/servicios/token_manager.php';
use QuickBooksOnline\API\DataService\DataService;

// ——————————————
// 4. Carga de config QuickBooks
// ——————————————
$config = require __DIR__ . '/src/Config/config.php';
$modo   = $config['modo'] ?? 'desarrollo';
$creds  = $config[$modo]   ?? [];

// ——————————————
// 5. Validación de login interno (cédula + contraseña)
// ——————————————
$error = '';
if (
    $_SERVER['REQUEST_METHOD'] === 'POST'
    && isset($_POST['cedula'], $_POST['pass'])
) {
    $stmt = $conn->prepare(
        'SELECT id, password 
           FROM usuarios
          WHERE cedula = ?'
    );
    $stmt->bind_param('s', $_POST['cedula']);
    $stmt->execute();
    $stmt->bind_result($userId, $dbPassword);
    $stmt->fetch();
    $stmt->close();

    if ($userId && password_verify($_POST['pass'], $dbPassword)) {
        $_SESSION['user_id'] = $userId;
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit;
    }

    $error = 'Cédula o contraseña inválidos.';
}

// ——————————————
// 6. Si autenticado, renovar tokens o iniciar OAuth2
// ——————————————
if (! empty($_SESSION['user_id'])) {
    $tm = new TokenManagerDB($pdo);

    try {
        // tokens válidos → dashboard
        $tm->verificarYRenovar(
            (int)$_SESSION['user_id'],
            $creds['ClientID'],
            $creds['ClientSecret']
        );
        header('Location: /ColchonesBqto/vistas/dashboard.php');
        exit;

    } catch (Exception $e) {
        // arrancar OAuth2
        $state = bin2hex(random_bytes(16));
        $_SESSION['oauth_state'] = $state;

        $dataService = DataService::Configure([
            'auth_mode'    => 'oauth2',
            'ClientID'     => $creds['ClientID'],
            'ClientSecret' => $creds['ClientSecret'],
            'RedirectURI'  => $creds['RedirectURI'],
            'scope'        => 'com.intuit.quickbooks.accounting',
            'baseUrl'      => $creds['baseUrl'],
        ]);

        $authUrl = $dataService
            ->getOAuth2LoginHelper()
            ->getAuthorizationCodeURL(
                $creds['RedirectURI'],
                'com.intuit.quickbooks.accounting',
                $state
            );

        header("Location: {$authUrl}");
        exit;
    }
}

// ——————————————
// 7. Formulario de login interno + error
// ——————————————
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1"/>
  <title>ColchonesBQTO — Acceso Interno</title>
  <link
    href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css"
    rel="stylesheet"
  />
  <style>
    body {
      background: #f8f9fa;
      height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
    }
    .card { max-width: 380px; width: 100%; }
  </style>
</head>
<body>
  <div class="card shadow-sm">
    <div class="card-body">
      <h5 class="card-title text-center mb-3">
        Ingresa tu cédula y contraseña
      </h5>

      <?php if ($error): ?>
        <div class="alert alert-danger">
          <?= htmlspecialchars($error) ?>
        </div>
      <?php endif; ?>

      <form method="post" novalidate>
        <div class="mb-3">
          <label class="form-label">Cédula</label>
          <input
            type="text"
            name="cedula"
            class="form-control"
            required
            autofocus
          />
        </div>

        <div class="mb-3">
          <label class="form-label">Contraseña</label>
          <input
            type="password"
            name="pass"
            class="form-control"
            required
          />
        </div>

        <button class="btn btn-primary w-100">
          Ingresar y conectar QuickBooks
        </button>
      </form>
    </div>
  </div>
</body>
</html>