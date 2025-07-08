<?php
require_once 'vendor/autoload.php';
use QuickBooksOnline\API\DataService\DataService;

echo "<pre>";
print_r($_GET);
echo "</pre>";

$dataService = DataService::Configure([
    'auth_mode' => 'oauth2',
    'ClientID' => 'AB4dLiT5xDU15Ih8F6HoFE12wuq6MfGRNJI4DLbH1ERJb4bbLB',
    'ClientSecret' => 'E711ETcTF4XLgrvxjBys6sD5BDer0YoijhMRceI5',
    'RedirectURI' => 'http://localhost:8080/colchonesbqto/callback.php',
    'scope' => 'com.intuit.quickbooks.accounting',
    'baseUrl' => 'Production'
	//'baseUrl' => 'Development'
]);

$OAuth2LoginHelper = $dataService->getOAuth2LoginHelper();

if (isset($_GET['code']) && isset($_GET['realmId'])) {
    try {
        $accessToken = $OAuth2LoginHelper->exchangeAuthorizationCodeForToken(
            $_GET['code'],
            $_GET['realmId']
        );

        // Construir arreglo con los datos manualmente
        $tokenData = [
            'access_token' => $accessToken->getAccessToken(),
            'refresh_token' => $accessToken->getRefreshToken(),
            'expires_in' => $accessToken->getAccessTokenValidationPeriodInSeconds(),
            'x_refresh_token_expires_in' => $accessToken->getRefreshTokenValidationPeriodInSeconds(),
            'token_type' => 'bearer',
            'realmId' => $_GET['realmId']
        ];

        // Guardar token de forma segura
        $result = file_put_contents('token.json', json_encode($tokenData, JSON_PRETTY_PRINT));

        if ($result === false) {
            $message = "❌ Error al guardar el token. Verifica permisos de escritura.";
        } else {
            $message = "✅ ¡Token generado y guardado exitosamente!";
        }
    } catch (Exception $e) {
        $message = "❌ Error al generar el token: " . $e->getMessage();
    }
} else {
    $message = "⚠️ No se recibió el código de autorización o el realm ID.";
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Resultado de la Autenticación</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body class="bg-light">
    <script>
        Swal.fire({
            title: 'Resultado',
            text: <?= json_encode($message) ?>,
            icon: <?= strpos($message, '✅') !== false ? "'success'" : (strpos($message, '⚠️') !== false ? "'warning'" : "'error'") ?>,
            confirmButtonText: 'OK'
        });
    </script>
</body>
</html>