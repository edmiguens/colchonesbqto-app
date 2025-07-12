<?php
// callback.php (TODO sobreescribir)

date_default_timezone_set('America/Caracas');

// 1) Imprime la URL completa y los GET
echo "<h2>🚧 DEBUG CALLBACK</h2>";
echo "<pre style='background:#f9f9f9;padding:1rem;border:1px solid #ccc;'>";
echo "REQUEST_URI:\n";
echo htmlspecialchars($_SERVER['REQUEST_URI']) . "\n\n";

echo "GET PARAMETERS:\n";
print_r($_GET);
echo "\n</pre>";

// 2) Detenemos aquí la ejecución
exit;