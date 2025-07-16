<?php
class QuickBooksService {
  private $tokenManager;
  private $userId;
  private $clientId;
  private $clientSecret;

  public function __construct($tokenManager, $userId, $clientId, $clientSecret) {
    $this->tokenManager  = $tokenManager;
    $this->userId        = $userId;
    $this->clientId      = $clientId;
    $this->clientSecret  = $clientSecret;
  }
  
  public function consultarClientes() {
  $datos = $this->tokenManager->cargar($this->userId);

  if (!$datos || !isset($datos['access_token'], $datos['realm_id'])) {
    return [
      'error' => true,
      'mensaje' => 'No se pudo cargar el token o el realm ID para el usuario actual.'
    ];
  }

  $accessToken = $datos['access_token'];
  $realmId     = $datos['realm_id'];

  $url = "https://sandbox-quickbooks.api.intuit.com/v3/company/$realmId/query";
  $query = "SELECT * FROM Customer";

  $headers = [
    "Authorization: Bearer $accessToken",
    "Accept: application/json",
    "Content-Type: application/text"
  ];

  $ch = curl_init($url);
  curl_setopt($ch, CURLOPT_POST, true);
  curl_setopt($ch, CURLOPT_POSTFIELDS, $query);
  curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

  $response = curl_exec($ch);
  curl_close($ch);

  return json_decode($response, true);
}
  
  
  
  //  public function consultarClientes() {
  //  $datos = $this->tokenManager->cargar($this->userId);
  //  $accessToken = $datos['access_token'];
  //  $realmId     = $datos['realm_id'];

    // Endpoint para consulta de clientes (sandbox)
  //  $url = "https://sandbox-quickbooks.api.intuit.com/v3/company/$realmId/query";
  //  $query = "SELECT * FROM Customer";

//    $headers = [
//      "Authorization: Bearer $accessToken",
//      "Accept: application/json",
//      "Content-Type: application/text"
//    ];

//    $ch = curl_init($url);
//    curl_setopt($ch, CURLOPT_POST, true);
//    curl_setopt($ch, CURLOPT_POSTFIELDS, $query);
//    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
//    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

//    $response = curl_exec($ch);
//    curl_close($ch);

    //return json_decode($response, true);
  }
}