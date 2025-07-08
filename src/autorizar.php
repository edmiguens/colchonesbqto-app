<?php
require_once 'vendor/autoload.php';
use QuickBooksOnline\API\DataService\DataService;

$dataService = DataService::Configure([
    'auth_mode'     => 'oauth2',
    'ClientID'      => 'AB4dLiT5xDU15Ih8F6HoFE12wuq6MfGRNJI4DLbH1ERJb4bbLB',
    'ClientSecret'  => 'E711ETcTF4XLgrvxjBys6sD5BDer0YoijhMRceI5',
    'RedirectURI'   => 'http://localhost:8080/colchonesbqto/callback.php',
    'scope'         => 'com.intuit.quickbooks.accounting',
    'baseUrl'       => 'Production'
]);

$OAuth2LoginHelper = $dataService->getOAuth2LoginHelper();
$url = $OAuth2LoginHelper->getAuthorizationCodeURL();

// Redirigir al usuario a QuickBooks para autorizar manualmente
header("Location: {$url}");
exit;