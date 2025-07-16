<?php
session_start();
require 'vendor/autoload.php';
use QuickBooksOnline\API\DataService\DataService;

// Cargar configuración
$config       = include __DIR__ . '/src/Config/config.php';
$credenciales = $config[$config['modo']]; // por ejemplo, 'produccion'

// Generar estado de seguridad CSRF
$state = bin2hex(random_bytes(8));
$_SESSION['oauth2_state'] = $state;

// Configurar el DataService
$dataService = DataService::Configure([
    'auth_mode'    => 'oauth2',
    'ClientID'     => $credenciales['ClientID'],
    'ClientSecret' => $credenciales['ClientSecret'],
    'RedirectURI'  => $credenciales['RedirectURI'],
    'scope'        => 'com.intuit.quickbooks.accounting',
    'baseUrl'      => $credenciales['baseUrl'],
]);

// Obtener helper y generar URL de autorización
$helper   = $dataService->getOAuth2LoginHelper();
$authUrl  = $helper->getAuthorizationCodeURL($credenciales['RedirectURI'], $state);

// Mostrar link para depuración
echo "<h2>🚧 DEBUG AUTH URL</h2>";
echo "<p><a href=\"$authUrl\" target=\"_blank\">Haz clic aquí para iniciar OAuth2</a></p>";
echo "<pre>$authUrl</pre>";