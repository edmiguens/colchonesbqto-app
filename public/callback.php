<?php
<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 🔍 Diagnóstico visual temporal
echo "<pre>";
echo "✅ callback.php ejecutándose\n";

echo "📦 Parámetros recibidos:\n";
echo "Code: " . ($_GET['code'] ?? '❌ No recibido') . "\n";
echo "State: " . ($_GET['state'] ?? '❌ No recibido') . "\n";
echo "RealmID: " . ($_GET['realmId'] ?? '❌ No recibido') . "\n";

echo "🧪 User ID de sesión: " . ($_SESSION['user_id'] ?? '❌ No definido') . "\n";
echo "</pre>";
exit;
session_start();

// ✅ Autoload del SDK de QuickBooks y tus clases
require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/src/servicios/token_manager.php';
require __DIR__ . '/src/servicios/conexion_pdo.php'; // ← conexión PDO con puerto 3307

use QuickBooksOnline\API\DataService\DataService;

// ✅ Configuración
$config = include __DIR__ . '/src/Config/config.php';
$credenciales = $config[$config['modo']];

if (isset($_GET['code'], $_GET['realmId'])) {
  try {
    // ⚙️ Instancia del DataService
    $dataService = DataService::Configure([
      'auth_mode'     => 'oauth2',
      'ClientID'      => $credenciales['ClientID'],
      'ClientSecret'  => $credenciales['ClientSecret'],
      'RedirectURI'   => $credenciales['RedirectURI'],
      'scope'         => 'com.intuit.quickbooks.accounting',
      'baseUrl'       => $credenciales['baseUrl']
    ]);

    // 🔄 Intercambio del código por el token
    $accessToken = $dataService
      ->getOAuth2LoginHelper()
      ->exchangeAuthorizationCodeForToken($_GET['code'], $_GET['realmId']);
    if (!$accessToken || !$accessToken->getAccessToken()) {
        throw new Exception("❌ El token recibido está vacío o incompleto.");
}
    $dataService->updateOAuth2Token($accessToken);

    // 📦 Guardar el token en base de datos
    $gestor = new TokenManagerDB($pdo);
	if (!isset($_SESSION['user_id'])) {
       die("❌ No se encontró el ID de usuario en la sesión.");
}
    //$userId = $_SESSION['usuario_id'];
    //$userId = $_SESSION['usuario_id'] ?? 1; // ← ajusta según tu sistema
	$userId = $_SESSION['user_id'];

    $gestor->guardar($accessToken, $config['modo'], $userId);

    // ✅ Mensaje de éxito temporal
    echo "<div id='token-msg' style='
      padding: 1em;
      margin-top: 2em;
      border: 2px solid #4CAF50;
      background-color: #e7f8e7;
      color: #2e7d32;
      font-family: Arial, sans-serif;
      max-width: 500px;
    '>
      ✅ Token generado y guardado correctamente.
    </div>
    <script>
      setTimeout(() => {
        document.getElementById('token-msg')?.remove();
        window.location.href = 'vistas/dashboard.php';
      }, 3000);
    </script>";

  } catch (Exception $e) {
    echo "<h3 style='color:red;'>❌ Error al procesar el token: " . $e->getMessage() . "</h3>";
  }

} else {
  echo "<h3 style='color: red;'>❌ Error: Parámetros faltantes en callback</h3>";
}