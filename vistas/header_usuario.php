<?php
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

$nombre = $_SESSION['nombre'] ?? 'Invitado';
$rol    = $_SESSION['rol']    ?? 'sin rol';
$modo   = $_SESSION['modo']   ?? 'desconocido';

$color = $modo === 'sandbox' ? 'warning' : 'success';
?>
<div class="container-fluid bg-light py-2 px-4 border-bottom d-flex justify-content-between align-items-center">
 
</div>