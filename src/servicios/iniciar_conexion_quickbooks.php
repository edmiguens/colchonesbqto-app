<?php
session_start();
require_once __DIR__ . '/../Config/config.php';
$config = include __DIR__ . '/../Config/config.php';

if (!isset($_SESSION['user_id'])) {
  die("❌ Sesión no iniciada.");
}

$userId       = $_SESSION['user_id'];
$modo         = $config['modo'];
$clientId     = $config[$modo]['ClientID'];
$redirectUri  = $config[$modo]['RedirectURI'];
$scope        = 'com.intuit.quickbooks.accounting openid profile email phone address';

$params = [
  'client_id'     => $clientId,
  'redirect_uri'  => $redirectUri,
  'response_type' => 'code',
  'scope'         => $scope,
  'state'         => $userId
];

//$host = ($modo === 'sandbox')
$host = 'https://appcenter.intuit.com/connect/oauth2'; // ✅ siempre válida

header("Location: " . $host . '?' . http_build_query($params));
exit;