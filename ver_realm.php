<?php
$tokenData = json_decode(file_get_contents('token.json'), true);
if (!$tokenData || !isset($tokenData['realmId'])) {
    echo "❌ No se encontró el realmId en token.json";
} else {
    echo "✅ Tu realmId es: " . $tokenData['realmId'];
}
?>