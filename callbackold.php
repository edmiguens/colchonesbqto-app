<?php
require_once __DIR__ . '/vendor/autoload.php'; // 👈 asegúrate que este archivo esté en la raíz
use QuickBooksOnline\API\DataService\DataService;

date_default_timezone_set('America/Caracas'); // 🌎 para que los timestamps sean precisos

// 📦 Cargar configuración
$config       = include __DIR__ . '/src/Config/config.php';
$modo         = $config['modo'];
$credenciales = $config[$modo];

// 🔍 Validar que Intuit te envió el código
if (!isset($_GET['code']) || !isset($_GET['state'])) {
    exit('❌ Error: No se recibió el código de autorización');
}

$authorizationCode = $_GET['code'];
$state             = $_GET['state']; // útil si deseas comparar contra tu estado original

// 🔐 Configurar conexión OAuth
$dataService = DataService::Configure([
    'auth_mode'     => 'oauth2',
    'ClientID'      => $credenciales['ClientID'],
    'ClientSecret'  => $credenciales['ClientSecret'],
    'RedirectURI'   => $credenciales['RedirectURI'],
    'scope'         => 'com.intuit.quickbooks.accounting',
    'baseUrl'       => $credenciales['baseUrl']
]);

$OAuth2LoginHelper = $dataService->getOAuth2LoginHelper();
echo "<pre style='background:#f9f9f9; padding:1rem; border:1px solid #ccc; max-width:700px; margin:2rem auto;'>";
echo "🔎 Diagnóstico previo al intercambio del token\n";
echo "Modo: " . htmlspecialchars($modo) . "\n";
echo "ClientID: " . htmlspecialchars($credenciales['ClientID']) . "\n";
echo "RedirectURI: " . htmlspecialchars($credenciales['RedirectURI']) . "\n";
echo "baseUrl: " . htmlspecialchars($credenciales['baseUrl']) . "\n";
echo "Authorization Code (code): " . htmlspecialchars($authorizationCode) . "\n";
echo "State: " . htmlspecialchars($state) . "\n";
echo "RealmID (GET): " . htmlspecialchars($_GET['realmId'] ?? 'No recibido') . "\n";
echo "</pre>";




try {
    // 🔁 Intercambiar el código por tokens
    $accessTokenObj = $OAuth2LoginHelper->exchangeAuthorizationCodeForToken($authorizationCode);

    // 🧠 Extraer información importante
    $tokenData = [
        'access_token'                => $accessTokenObj->getAccessToken(),
        'refresh_token'               => $accessTokenObj->getRefreshToken(),
        'expires_in'                  => $accessTokenObj->getAccessTokenValidationPeriodInSeconds(),
        'x_refresh_token_expires_in' => $accessTokenObj->getRefreshTokenValidationPeriodInSeconds(),
        'token_type'                  => 'bearer',
        'realmId'                     => $accessTokenObj->getRealmID(),
        'generated_at'                => time()
    ];

    // 📁 Guardar los tokens en JSON
    file_put_contents(__DIR__ . '/token.json', json_encode($tokenData, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

    // 🎉 Mostrar confirmación visual
    echo "<script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>";
    echo "<script>
      Swal.fire({
        icon: 'success',
        title: '✅ Token obtenido',
        text: 'La conexión con QuickBooks fue exitosa',
        confirmButtonText: 'Continuar'
      }).then(() => {
        window.location.href = 'empresa.php'; // cambia si tienes otro archivo de prueba
      });
    </script>";

} catch (Exception $e) {
    // ⚠️ Mostrar el error visualmente
    echo "<script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>";
    echo "<script>
      Swal.fire({
        icon: 'error',
        title: '❌ Falló la autorización',
        text: " . json_encode($e->getMessage()) . ",
        confirmButtonText: 'Revisar'
      });
    </script>";
}