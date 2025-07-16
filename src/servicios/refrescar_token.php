public function refrescarToken($userId, $clientId, $clientSecret) {
  // 1. Obtener datos actuales desde la base
  $stmt = $this->pdo->prepare("
    SELECT refresh_token, realm_id, modo
    FROM companies
    WHERE user_id = :user_id AND estado = 'activo'
  ");
  $stmt->execute([':user_id' => $userId]);
  $row = $stmt->fetch();

  if (!$row) {
    throw new Exception("❌ No se encontró token para user_id $userId");
  }

  $refreshToken = $row['refresh_token'];
  $modo         = $row['modo'];
  $realmId      = $row['realm_id'];

  // 2. Preparar solicitud HTTP a QuickBooks
  $url = 'https://oauth.platform.intuit.com/oauth2/v1/tokens/bearer';
  $headers = [
    'Accept: application/json',
    'Content-Type: application/x-www-form-urlencoded',
    'Authorization: Basic ' . base64_encode("$clientId:$clientSecret")
  ];
  $body = http_build_query([
    'grant_type'    => 'refresh_token',
    'refresh_token' => $refreshToken
  ]);

  $ch = curl_init($url);
  curl_setopt($ch, CURLOPT_POST, true);
  curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
  curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

  $response = curl_exec($ch);
  $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
  curl_close($ch);

  if ($httpCode !== 200) {
    throw new Exception("❌ Error al renovar token (HTTP $httpCode): $response");
  }

  // 3. Procesar respuesta
  $data = json_decode($response, true);

  $accessToken  = $data['access_token'];
  $refreshTokenNuevo = $data['refresh_token'];
  $accessTokenExpiresIn  = $data['expires_in'];       // segundos
  $refreshTokenExpiresIn = $data['x_refresh_token_expires_in']; // segundos

  // 4. Construir objeto temporal simulando tu token real
  $tokenMock = new class {
    public $accessToken;
    public $refreshToken;
    public $accessTokenExpiresAt;
    public $refreshTokenExpiresAt;
    public $realmId;

    public function getAccessToken()        { return $this->accessToken; }
    public function getRefreshToken()       { return $this->refreshToken; }
    public function getAccessTokenExpiresAt()  { return time() + $this->accessTokenExpiresAt; }
    public function getRefreshTokenExpiresAt() { return time() + $this->refreshTokenExpiresAt; }
    public function getRealmID()            { return $this->realmId; }
  };

  $tokenMock->accessToken             = $accessToken;
  $tokenMock->refreshToken            = $refreshTokenNuevo;
  $tokenMock->accessTokenExpiresAt    = $accessTokenExpiresIn;
  $tokenMock->refreshTokenExpiresAt   = $refreshTokenExpiresIn;
  $tokenMock->realmId                 = $realmId;

  // 5. Guardar nuevo token
  $this->guardar($tokenMock, $modo, $userId);
}