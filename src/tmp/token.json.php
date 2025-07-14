<?php
require_once 'vendor/autoload.php';
use QuickBooksOnline\API\Core\ServiceContext;
use QuickBooksOnline\API\DataService\DataService;

date_default_timezone_set('America/Caracas');

// 📍 Ruta segura para entornos como Render
$tokenFile = sys_get_temp_dir() . '/token.json';

// 🧱 Si el archivo no existe, lo creamos vacío
if (!file_exists($tokenFile)) {
    $handle = fopen($tokenFile, 'w');
    if ($handle) {
        fclose($handle);
    } else {
        die("❌ No se pudo crear el archivo token.json en /tmp");
    }
}

// 🔍 Leer datos actuales del token
$tokenData = json_decode(file_get_contents($tokenFile), true);

// ❗ Validar que tenga refresh_token
if (!isset($tokenData['refresh_token'])) {
    die("❌ token.json no contiene refresh_token. Debes iniciar sesión nuevamente.");
}

// 🔐 Configuración para QuickBooks API
$dataService = DataService::Configure([
    'auth_mode'     => 'oauth2',
    'ClientID'      => 'AB4dLiT5xDU15Ih8F6HoFE12wuq6MfGRNJI4DLbH1ERJb4bbLB',
    'ClientSecret'  => 'E711ETcTF4XLgrvxjBys6sD5BDer0YoijhMRceI5',
    'RedirectURI'   => 'http://localhost:8080/colchonesbqto/callback.php',
    'scope'         => 'com.intuit.quickbooks.accounting',
    'baseUrl'       => 'Production'
]);

$oauth2LoginHelper = $dataService->getOAuth2LoginHelper();

try {
    // 🔁 Obtener el nuevo token usando el refresh_token
    $refreshedAccessToken = $oauth2LoginHelper->refreshAccessTokenWithRefreshToken($tokenData['refresh_token']);

    $nuevoToken = [
        'access_token'                => $refreshedAccessToken->getAccessToken(),
        'refresh_token'               => $refreshedAccessToken->getRefreshToken(),
        'expires_in'                  => $refreshedAccessToken->getAccessTokenValidationPeriodInSeconds(),
        'x_refresh_token_expires_in' => $refreshedAccessToken->getRefreshTokenValidationPeriodInSeconds(),
        'token_type'                  => 'bearer',
        'realmId'                     => $tokenData['realmId']
    ];

    // ✍️ Guardar el token renovado
    if (is_writable($tokenFile)) {
        file_put_contents($tokenFile, json_encode($nuevoToken, JSON_PRETTY_PRINT));
        echo "✅ Token renovado exitosamente";
    } else {
        echo "❌ No se puede escribir en $tokenFile. Verifica permisos.";
    }

} catch (Exception $e) {
    echo "❌ Error al renovar token: " . $e->getMessage();
}
?>