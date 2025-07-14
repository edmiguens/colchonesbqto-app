<?php
require_once 'vendor/autoload.php';
use QuickBooksOnline\API\DataService\DataService;

date_default_timezone_set('America/Caracas');

$tokenFile = __DIR__ . '/token.json';

// 🔍 Verifica que el archivo token.json exista y tenga el refresh_token
if (!file_exists($tokenFile)) {
    exit("❌ El archivo token.json no existe. Debes iniciar sesión primero.");
}

$tokenData = json_decode(file_get_contents($tokenFile), true);

if (!isset($tokenData['refresh_token']) || !isset($tokenData['realmId'])) {
    exit("❌ token.json está incompleto. No se puede renovar el token.");
}

// 🔐 Configurar QuickBooks API (✅ Producción)
$dataService = DataService::Configure([
    'auth_mode'     => 'oauth2',
    'ClientID'      => 'ABCdF0BQFmcaxBa9KI9wtNRq9GbIMYbB2cWNA1UAvEa8t6hfmz',        // 🔒 Usa el de producción
    'ClientSecret'  => 't1IRhPgphog6kZqAtH7TA3aXGAjwh8ZIpZHfQaZb',                   // 🔒 También el de producción
    'RedirectURI'   => 'https://colchonesbqto-app.onrender.com/callback.php',       // ✅ El que registraste en Intuit
    'scope'         => 'com.intuit.quickbooks.accounting',
    'baseUrl'       => 'Production'                                                  // 🎯 MUY importante: producción
]);

$oauth2LoginHelper = $dataService->getOAuth2LoginHelper();

try {
    // 🔁 Renovar el token
    $refreshed = $oauth2LoginHelper->refreshAccessTokenWithRefreshToken($tokenData['refresh_token']);

    $nuevoToken = [
        'access_token'                => $refreshed->getAccessToken(),
        'refresh_token'               => $refreshed->getRefreshToken(),
        'expires_in'                  => $refreshed->getAccessTokenValidationPeriodInSeconds(),
        'x_refresh_token_expires_in' => $refreshed->getRefreshTokenValidationPeriodInSeconds(),
        'token_type'                  => 'bearer',
        'realmId'                     => $tokenData['realmId'],
        'generated_at'                => time()
    ];

    file_put_contents($tokenFile, json_encode($nuevoToken, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    echo "✅ Token renovado exitosamente\n";
} catch (Exception $e) {
    echo "❌ Error al renovar el token: " . $e->getMessage();
}