<?php
// callback_debug.php

echo "<h2>🚧 DEBUG CALLBACK</h2>";
echo "<pre style='background:#f9f9f9;padding:1rem;border:1px solid #ccc;'>";
echo "REQUEST_URI:\n", htmlspecialchars($_SERVER['REQUEST_URI']), "\n\n";
echo "GET PARAMETERS:\n";
print_r($_GET);
echo "</pre>";
exit;