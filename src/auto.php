<?php
$client_id     = 'ABCdF0BQFmcaxBa9KI9wtNRq9GbIMYbB2cWNA1UAvEa8t6hfmz';
$redirect_uri  = 'https://colchonesbqto-app.onrender.com/callback.php';

// ✨ Scope limpio
$scope = 'com.intuit.quickbooks.accounting com.intuit.quickbooks.payment offline_access';

$params = [
  'client_id'     => $client_id,
  'redirect_uri'  => $redirect_uri,
  'scope'         => $scope,
  'response_type' => 'code',
  'state'         => bin2hex(random_bytes(8))
];

$url = 'https://appcenter.intuit.com/connect/oauth2?' . http_build_query($params);

// ✨ Verifica la URL generada
//echo "🔗 URL generada: $url"; exit;