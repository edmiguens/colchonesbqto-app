<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Panel de Control</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
  <div class="container py-5">
    <h2 class="mb-4 text-center">Panel de Módulos 💼</h2>
    <div class="row row-cols-1 row-cols-md-2 g-4">
      <div class="col">
        <a href="clientes.php" class="btn btn-primary w-100 py-3">🧾 Cuentas por Cobrar</a>
      </div>
      <div class="col">
        <a href="verificar_token.php" class="btn btn-warning w-100 py-3">🔐 Verificar Token</a>
      </div>
      <div class="col">
        <a href="autorizar.php" class="btn btn-success w-100 py-3">✅ Autorizar QuickBooks</a>
      </div>
      <div class="col">
        <a href="callback.php" class="btn btn-info w-100 py-3">🔁 Callback</a>
      </div>
    </div>
  </div>
</body>
</html>