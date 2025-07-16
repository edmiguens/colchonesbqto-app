<?php

class TokenManagerDB {
  private $pdo;

  public function __construct(PDO $pdo) {
    $this->pdo = $pdo;
  }

  public function existe($userId) {
    $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM companies WHERE user_id = ? AND estado = 'activo'");
    $stmt->execute([$userId]);
    return $stmt->fetchColumn() > 0;
  }

  public function guardar($token, $modo, $userId) {
    $accessExpirationRaw  = $token->getAccessTokenExpiresAt();
    $refreshExpirationRaw = $token->getRefreshTokenExpiresAt();

    $accessExpiration = is_string($accessExpirationRaw)
      ? str_replace('/', '-', $accessExpirationRaw)
      : $accessExpirationRaw;

    $refreshExpiration = is_string($refreshExpirationRaw)
      ? str_replace('/', '-', $refreshExpirationRaw)
      : $refreshExpirationRaw;

    $sql = "
      INSERT INTO companies (
        user_id,
        realm_id,
        access_token,
        refresh_token,
        access_expiration,
        refresh_expiration,
        modo,
        estado,
        creado_en,
        actualizado_en
      ) VALUES (
        :user_id,
        :realm_id,
        :access_token,
        :refresh_token,
        :access_expiration,
        :refresh_expiration,
        :modo,
        'activo',
        NOW(),
        NOW()
      )
      ON DUPLICATE KEY UPDATE
        access_token = VALUES(access_token),
        refresh_token = VALUES(refresh_token),
        access_expiration = VALUES(access_expiration),
        refresh_expiration = VALUES(refresh_expiration),
        actualizado_en = NOW()
    ";

    $stmt = $this->pdo->prepare($sql);
    $stmt->execute([
      ':user_id'            => $userId,
      ':realm_id'           => $token->getRealmID(),
      ':access_token'       => $token->getAccessToken(),
      ':refresh_token'      => $token->getRefreshToken(),
      ':access_expiration'  => $accessExpiration,
      ':refresh_expiration' => $refreshExpiration,
      ':modo'               => $modo
    ]);
  }

  public function cargar($userId) {
    $stmt = $this->pdo->prepare("
      SELECT access_token, realm_id
      FROM companies
      WHERE user_id = ? AND estado = 'activo'
    ");
    $stmt->execute([$userId]);
    $datos = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$datos || empty($datos['access_token']) || empty($datos['realm_id'])) {
      return null;
    }

    return [
      'access_token' => $datos['access_token'],
      'realm_id'     => $datos['realm_id']
    ];
  }

  public function tokenExpirado($userId) {
    $stmt = $this->pdo->prepare("
      SELECT access_expiration
      FROM companies
      WHERE user_id = :user_id AND estado = 'activo'
    ");
    $stmt->execute([':user_id' => $userId]);
    $row = $stmt->fetch();

    if (!$row || empty($row['access_expiration'])) return true;
    return strtotime($row['access_expiration']) < time();
  }

  public function refrescarToken($userId, $clientId, $clientSecret) {
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
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    if ($response === false) {
      $errorMsg = curl_error($ch);
      curl_close($ch);
      throw new Exception("❌ Fallo en curl: $errorMsg");
    }

    curl_close($ch);

    if ($httpCode !== 200) {
      throw new Exception("❌ Error al renovar token (HTTP $httpCode): $response");
    }

    $data = json_decode($response, true);

    $tokenMock = new class {
      public $accessToken;
      public $refreshToken;
      public $accessTokenExpiresAt;
      public $refreshTokenExpiresAt;
      public $realmId;

      public function getAccessToken()        { return $this->accessToken; }
      public function getRefreshToken()       { return $this->refreshToken; }
      public function getAccessTokenExpiresAt()  { return date('Y-m-d H:i:s', time() + $this->accessTokenExpiresAt); }
      public function getRefreshTokenExpiresAt() { return date('Y-m-d H:i:s', time() + $this->refreshTokenExpiresAt); }
      public function getRealmID()            { return $this->realmId; }
    };

    $tokenMock->accessToken             = $data['access_token'];
    $tokenMock->refreshToken            = $data['refresh_token'];
    $tokenMock->accessTokenExpiresAt    = $data['expires_in'];
    $tokenMock->refreshTokenExpiresAt   = $data['x_refresh_token_expires_in'];
    $tokenMock->realmId                 = $realmId;

    $this->guardar($tokenMock, $modo, $userId);
  }

  public function verificarYRenovar($userId, $clientId, $clientSecret) {
    if ($this->tokenExpirado($userId)) {
      $this->refrescarToken($userId, $clientId, $clientSecret);
    }
  }
}