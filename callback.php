// 👇 AGREGAR ESTO PRIMERO
echo "<h1>✅ callback.php alcanzado</h1>";
print_r($_GET);


<?php
// callback.php

session_start();
require 'vendor/autoload.php';

use QuickBooksOnline\API\DataService\DataService;

// Cargar configuración
$config       = include __DIR__ . '/src/Config/config.php';
$credenciales = $config[$config['modo']];

// Validar parámetros recibidos y estado
if (!isset($_GET['code'], $_GET['state']) || $_GET['state'] !== $_SESSION['oauth2_state']) {
    exit('❌ Error: Parámetros faltantes o estado inválido.');
}

$authorizationCode = $_GET['code'];
$realmId           = $_GET['realmId'] ?? null;

if (!$realmId) {
    exit('❌ Error: No se recibió el realmId. Intuit no ha vinculado ninguna compañía.');
}

// Configurar DataService
$dataService = DataService::Configure([
    'auth_mode'    => 'oauth2',
    'ClientID'     => $credenciales['ClientID'],
    'ClientSecret' => $credenciales['ClientSecret'],
    'RedirectURI'  => $credenciales['RedirectURI'],
    'scope'        => 'com.intuit.quickbooks.accounting',
    'baseUrl'      => $credenciales['baseUrl'],
]);

$dataService->setRealmId($realmId);

// Intercambiar el código por tokens
try {
    $accessToken = $dataService->getOAuth2LoginHelper()->exchangeAuthorizationCodeForToken($authorizationCode);
    $dataService->updateOAuth2Token($accessToken);

    // Mostrar tokens obtenidos
    echo "<h2>✅ Token obtenido correctamente</h2><pre>";
    print_r($accessToken);
    echo "</pre>";

    echo "<h2>🏢 Realm ID:</h2><p>{$realmId}</p>";
} catch (Exception $e) {
    echo "<h2>❌ Error al intercambiar el token:</h2><pre>" . $e->getMessage() . "</pre>";
}