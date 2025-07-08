<?php
$tokenFile = __DIR__ . '/storage/token.json';

if (is_writable($tokenFile)) {
    echo "✅ El archivo token.json es escribible.";
} else {
    echo "❌ No se puede escribir en token.json. Verifica permisos en Render.";
}
?>