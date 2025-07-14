<?php
// 🔐 Credenciales de tu app
$client_id     = 'ABCdF0BQFmcaxBa9KI9wtNRq9GbIMYbB2cWNA1UAvEa8t6hfmz'; // ← cámbialo si quieres probar con otro
$client_secret = 't1IRhPgphog6kZqAtH7TA3aXGAjwh8ZIpZHfQaZb';
$redirect_uri  = 'https://colchonesbqto-app.onrender.com/callback.php';

// 🧭 Detección automática del entorno
$entorno = (strpos($client_id, 'sandbox') !== false || strpos($client_id, 'SB') === 0)
    ? 'sandbox'
    : 'producción';
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Entorno detectado</title>
  <style>
    body {
      font-family: Arial, sans-serif;
      padding: 2rem;
      background-color: #f0f8ff;
    }
    .card {
      background: #fff;
      border: 2px solid <?= $entorno === 'producción' ? '#2e8b57' : '#ffa500' ?>;
      padding: 1.5rem;
      border-radius: 8px;
      box-shadow: 0 0 10px rgba(0,0,0,0.1);
      max-width: 500px;
      margin: auto;
      text-align: center;
    }
    .card h2 {
      color: <?= $entorno === 'producción' ? '#2e8b57' : '#ffa500' ?>;
    }
    .badge {
      font-size: 1rem;
      padding: 0.5rem 1rem;
      background-color: <?= $entorno === 'producción' ? '#2e8b57' : '#ffa500' ?>;
      color: white;
      border-radius: 5px;
      display: inline-block;
      margin-top: 1rem;
    }
  </style>
</head>
<body>
  <div class="card">
    <h2>🌐 Entorno detectado</h2>
    <p>Según el <code>client_id</code> configurado, estás trabajando en:</p>
    <div class="badge"><?= strtoupper($entorno) ?></div>
    <p style="margin-top: 1rem;">Puedes usar esta detección para ajustar credenciales, rutas o lógica.</p>
  </div>
</body>
</html>