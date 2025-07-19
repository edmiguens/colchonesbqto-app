<?php

class TokenManagerDB {
  private $pdo;

  public function __construct(PDO $pdo) {
    $this->pdo = $pdo;
  }

  /**
   * Guarda los datos del token OAuth2 en la base de datos.
   */
  public function guardar($token, $modo, $userId) {
    // Obtener fechas y normalizar si vienen con '/'
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

  /**
   * Recupera el token activo para un usuario.
   */
  public function cargar($userId) {
    $stmt = $this->pdo->prepare("
      SELECT access_token, refresh_token, realm_id
      FROM companies
      WHERE user_id = :user_id AND estado = 'activo'
    ");
    $stmt->execute([':user_id' => $userId]);
    return $stmt->fetch();
  }

  /**
   * Verifica si el token ha caducado según access_expiration.
   */
  public function tokenExpirado($userId) {
    $stmt = $this->pdo->prepare("
      SELECT access_expiration
      FROM companies
      WHERE user_id = :user_id
    ");
    $stmt->execute([':user_id' => $userId]);
    $row = $stmt->fetch();
    if (!$row) return true;
    return strtotime($row['access_expiration']) < time();
  }
}