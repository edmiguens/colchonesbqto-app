<?php
// debug_oauth.php
session_start();
require_once __DIR__ . '/vendor/autoload.php';
use QuickBooksOnline\API\DataService\DataService;

// 1) Carga tu config
$config       = include __DIR__ . '/src/Config/config.php';
$modo         = $config['modo'];
$credenciales = $config[$modo];

// 2) Valores que recibió (si ya vino la redirección)
$receivedCode    = $_GET['code']    ?? '[no enviado]';
$receivedState   = $_GET['state']   ?? '[no enviado]';
$receivedRealmId = $_GET['realmId'] ?? '[no enviado]';

// 3) Imprime la configuración actual
echo "=== CONFIG GENERAL ===\n";
echo "modo:           $modo\n";
echo "ClientID:       {$credenciales['ClientID']}\n";
echo "RedirectURI:    https://colchonesbqto-app.onrender.com/debug_oauth.php\n";  // ojo
echo "scope:          com.intuit.quickbooks.accounting openid profile email\n";
echo "baseUrl:        {$credenciales['baseUrl']}\n\n";

// 4) Si ya tenemos GET, muéstralo y salimos
if (isset($_GET['code'])) {
    echo "=== GET RECIBIDO EN REDIRECT ===\n";
    echo "code:    $receivedCode\n";
    echo "state:   $receivedState\n";
    echo "realmId: $receivedRealmId\n";
    exit;
}

// 5) Si no, construye el enlace de autorización apuntando a debug_oauth.php
$oauthHelper = DataService::Configure([
    'auth_mode'     => 'oauth2',
    'ClientID'      => $credenciales['ClientID'],
    'ClientSecret'  => $credenciales['ClientSecret'],
    'RedirectURI'   => 'https://colchonesbqto-app.onrender.com/debug_oauth.php',
    'scope'         => 'com.intuit.quickbooks.accounting openid profile email',
    'baseUrl'       => $credenciales['baseUrl']
])->getOAuth2LoginHelper();

$state = bin2hex(random_bytes(8));
$_SESSION['oauth2_state'] = $state;
$authUrl = $oauthHelper->getAuthorizationCodeURL(
    'https://colchonesbqto-app.onrender.com/debug_oauth.php',
    $state
);

// 6) Muestra el enlace
echo "=== ENLACE DE AUTHORIZACIÓN ===\n";
echo $authUrl . "\n\n";
echo "Haz clic en ese enlace para autorizar, y volverás aquí con code, state y realmId.\n";