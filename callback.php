<?php
declare(strict_types=1);

// 1. Sesión segura y logging de errores
session_set_cookie_params([
    'lifetime' => 0,
    'path'     => '/',
    'domain'   => $_SERVER['HTTP_HOST'],
    'secure'   => false,   // true en prod
    'httponly' => true,
    'samesite' => 'Lax',
]);
session_name('app_session');
session_start();

ini_set('display_errors', '0');
ini_set('log_errors',     '1');
ini_set('error_log',      __DIR__ . '/logs/app_errors.log');
error_reporting(E_ALL & ~E_DEPRECATED & ~E_STRICT);

// 2. Carga de configuración
$configPath = __DIR__ . '/src/Config/config.php';
if (! is_file($configPath)) {
    error_log("Config no encontrada: {$configPath}");
    http_response_code(500);
    exit('Error de configuración');
}
$config = require $configPath;
$modo   = $config['modo'] ?? 'desarrollo';
$creds  = $config[$modo] ?? [];

// 3. Conexión PDO a la base de datos
$db   = $config['db'] ?? [];
$dsn  = sprintf(
    'mysql:host=%s;port=%d;dbname=%s;charset=utf8mb4',
    $db['host']      ?? '127.0.0.1',
    (int)($db['puerto'] ?? 3306),
    $db['basedatos'] ?? ''
);
try {
    $pdo = new PDO(
        $dsn,
        $db['usuario'] ?? '',
        $db['clave']   ?? '',
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
} catch (PDOException $e) {
    error_log("Error conexión BD: " . $e->getMessage());
    http_response_code(500);
    exit('Error de base de datos');
}

// 4. Validar parámetros OAuth2
if (empty($_GET['code']) || empty($_GET['state']) || empty($_GET['realmId'])) {
    error_log('Callback inválido: faltan code, state o realmId');
    http_response_code(400);
    exit('Parámetros faltantes');
}

// 5. Mitigación CSRF: validar state
if (! hash_equals($_SESSION['oauth_state'] ?? '', $_GET['state'])) {
    error_log('Callback inválido: state no coincide');
    http_response_code(400);
    exit('State inválido');
}
unset($_SESSION['oauth_state']);

// 6. Intercambio de código por tokens
require __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/src/servicios/token_manager.php';

use QuickBooksOnline\API\DataService\DataService;
use QuickBooksOnline\API\Core\HttpClients\IntuitResponse;

try {
    $dataService = DataService::Configure([
        'auth_mode'    => 'oauth2',
        'ClientID'     => $creds['ClientID'],
        'ClientSecret' => $creds['ClientSecret'],
        'RedirectURI'  => $creds['RedirectURI'],
        'scope'        => 'com.intuit.quickbooks.accounting',
        'baseUrl'      => $creds['baseUrl'],
    ]);

    $oauthHelper      = $dataService->getOAuth2LoginHelper();
    $accessTokenObj   = $oauthHelper
        ->exchangeAuthorizationCodeForToken($_GET['code'], $_GET['realmId']);

    if (! $accessTokenObj || ! $accessTokenObj->getAccessToken()) {
        throw new Exception('Token vacío o incompleto.');
    }

    // Actualizar token en el SDK para futuras llamadas
    $dataService->updateOAuth2Token($accessTokenObj);

} catch (Exception $e) {
    error_log('Error intercambio OAuth2: ' . $e->getMessage());
    http_response_code(500);
    exit('Error al obtener tokens');
}

// 7. Guardar tokens en BD
if (empty($_SESSION['user_id'])) {
    error_log('Callback: user_id no encontrado en sesión');
    http_response_code(401);
    exit('Usuario no autenticado');
}

$userId = (int) $_SESSION['user_id'];
$tm     = new TokenManagerDB($pdo);

try {
    $tm->guardar(
        $userId,
        $accessTokenObj->getAccessToken(),
        $accessTokenObj->getRefreshToken(),
        (int)$accessTokenObj->getExpiresIn(),
        (int)$accessTokenObj->getRefreshTokenExpiresIn(),
        $_GET['realmId']
    );
} catch (Exception $e) {
    error_log('Error guardando tokens: ' . $e->getMessage());
    http_response_code(500);
    exit('No se pudieron guardar los tokens');
}

// 8. Redirigir al dashboard
header('Location: /ColchonesBqto/vistas/dashboard.php');
exit;