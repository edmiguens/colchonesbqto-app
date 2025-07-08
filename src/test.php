<?php
echo file_exists("clientes.php") ? "✅ clientes.php está disponible" : "❌ No se encuentra";
echo "<br>";
echo file_exists("token.json") ? "✅ token.json disponible" : "❌ token.json ausente";
?>