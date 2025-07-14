<?php
session_start();

// 🔐 Datos de tu aplicación (usa los de PRODUCCIÓN)
$client_id = 'ABCdF0BQFmcaxBa9KI9wtNRq9GbIMYbB2cWNA1UAvEa8t6hfmz';
//$client_secret = 't1IRhPgphog6kZqAtH7TA3aXGAjwh8ZIpZHfQaZb';
$redirect_uri = 'https://colchonesbqto-app.onrender.com/callback.php';

// 🔄 Generar un 'state' único para prevenir CSRF
$state = bin2hex(random_bytes(8));
$_SESSION['oauth_state'] = $state;

// 📋 Scopes que necesitas para la API de QuickBooks
$scope = implode(' ', [
    'com.intuit.quickbooks.accounting',
    'openid',
    'profile',
    'email'
]);

// 🚀 Construir la URL de autorización
$auth_url = "https://appcenter.intuit.com/connect/oauth2?" .
    http_build_query([
        'client_id'     => $client_id,
        'redirect_uri'  => $redirect_uri,
        'response_type' => 'code',
        'scope'         => $scope,
        'state'         => $state
    ]);

// 🔍 Mostrar datos para depuración
echo "<h3>🚀 Datos para iniciar autorización:</h3>";
echo "Client ID: <strong>$client_id</strong><br>";
echo "Redirect URI: <strong>$redirect_uri</strong><br>";
echo "Scopes: <strong>$scope</strong><br>";
echo "State generado: <strong>$state</strong><br>";
echo "<hr>";
echo "<h3>🔎 Entorno detectado: ";
echo (strpos($client_id, 'sandbox') !== false || strpos($client_id, 'SB') === 0)
    ? "⚗️ Sandbox</h3>" : "🏢 Producción</h3>";

// ⏩ Redirigir al usuario
echo "<p><strong>URL final:</strong> $auth_url</p>";
header("Location: $auth_url");
exit;
?>