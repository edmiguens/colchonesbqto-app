<?php
require_once 'vendor/autoload.php';
use QuickBooksOnline\API\Core\ServiceContext;
use QuickBooksOnline\API\DataService\DataService;

$tokenFile = 'token.json';

if (!file_exists($tokenFile)) {
    die("❌ No se encontró token.json. Autentícate primero desde config.php");
}

$tokenData = json_decode(file_get_contents($tokenFile), true);

// Validar campos necesarios
if (!isset($tokenData['refresh_token'])) {
    die("❌ token.json no contiene refresh_token. Debes iniciar sesión nuevamente.");
}

$dataService = DataService::Configure([
    'auth_mode'       => 'oauth2',
    'ClientID'        => 'AB4dLiT5xDU15Ih8F6HoFE12wuq6MfGRNJI4DLbH1ERJb4bbLB',
    'ClientSecret'    => 'E711ETcTF4XLgrvxjBys6sD5BDer0YoijhMRceI5',
    'RedirectURI'     => 'http://localhost:8080/colchonesbqto/callback.php',
    'scope'           => 'com.intuit.quickbooks.accounting',
	'baseUrl'         => 'Production',
    //'baseUrl'         => 'Development',
]);

$oauth2LoginHelper = $dataService->getOAuth2LoginHelper();

try {
    $refreshedAccessToken = $oauth2LoginHelper->refreshAccessTokenWithRefreshToken($tokenData['refresh_token']);

    $nuevoToken = [
        'access_token' => $refreshedAccessToken->getAccessToken(),
        'refresh_token' => $refreshedAccessToken->getRefreshToken(),
        'expires_in' => $refreshedAccessToken->getAccessTokenValidationPeriodInSeconds(),
        'x_refresh_token_expires_in' => $refreshedAccessToken->getRefreshTokenValidationPeriodInSeconds(),
        'token_type' => 'bearer',
        'realmId' => $tokenData['realmId']
    ];

    file_put_contents($tokenFile, json_encode($nuevoToken, JSON_PRETTY_PRINT));
    //echo "✅ Token renovado exitosamente";

} catch (Exception $e) {
    echo "❌ Error al renovar token: " . $e->getMessage();
}