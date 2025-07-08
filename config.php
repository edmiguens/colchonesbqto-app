<?php
require_once __DIR__ . '/vendor/autoload.php';
use QuickBooksOnline\API\DataService\DataService;

$dataService = DataService::Configure([
    'auth_mode' => 'oauth2',
    'ClientID' => 'AB4dLiT5xDU15Ih8F6HoFE12wuq6MfGRNJI4DLbH1ERJb4bbLB',
    'ClientSecret' => 'E711ETcTF4XLgrvxjBys6sD5BDer0YoijhMRceI5',
    'RedirectURI' => 'http://localhost:8080/colchonesbqto/callback.php',
    'scope' => 'com.intuit.quickbooks.accounting',
    'baseUrl' => 'Development'
]);

$OAuth2LoginHelper = $dataService->getOAuth2LoginHelper();
$authUrl = $OAuth2LoginHelper->getAuthorizationCodeURL();
header("Location: " . $authUrl);
exit;