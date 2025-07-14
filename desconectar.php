<?php
$access_token = 'TU_ACCESS_TOKEN_AQUÍ'; // Debería venir desde tu base de datos o sesión
$revoke_url = 'https://oauth.platform.intuit.com/oauth2/v1/tokens/revoke';

$ch = curl_init($revoke_url);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
    'token' => $access_token
]));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/x-www-form-urlencoded',
    'Accept: application/json'
]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

$response = curl_exec($ch);
curl_close($ch);

// ✅ Mostrar resultado
$data = json_decode($response, true);
if (empty($data)) {
    // Revocación exitosa
    echo "<script>
        Swal.fire({
            icon: 'success',
            title: 'Conexión revocada',
            text: 'La integración con QuickBooks fue desconectada correctamente.',
            confirmButtonText: 'Aceptar'
        }).then(() => {
            window.location.href = 'dashboard.php';
        });
    </script>";
} else {
    echo "<pre>";
    print_r($data);
    echo "</pre>";
}
?>