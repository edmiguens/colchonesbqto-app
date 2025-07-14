<?php
require_once __DIR__ . '/../vendor/autoload.php';
use QuickBooksOnline\API\DataService\DataService;

$dataService = DataService::Configure([
    'auth_mode'     => 'oauth2',
    'ClientID'      => 'ABCdF0BQFmcaxBa9KI9wtNRq9GbIMYbB2cWNA1UAvEa8t6hfmz',
    'ClientSecret'  => 't1IRhPgphog6kZqAtH7TA3aXGAjwh8ZIpZHfQaZb',
	'RedirectURI'   => 'https://colchonesbqto-app.onrender.com/callback.php',
	'scope' => 'com.intuit.quickbooks.accounting',
    'baseUrl'       => 'Production']);

$OAuth2LoginHelper = $dataService->getOAuth2LoginHelper();
$url = $OAuth2LoginHelper->getAuthorizationCodeURL();

// Redirigir al usuario a QuickBooks para autorizar manualmente
//echo "URL OAuth: " . $url; exit;
header("Location: {$url}");
exit;