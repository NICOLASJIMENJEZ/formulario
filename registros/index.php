<?php
// Entry point for the folder /formulario/registros/
// Redirects to chooser UI so visiting /formulario/registros/ opens the chooser.
$target = '/formulario/registros/chooser.php';
header('Location: ' . $target);
if (headers_sent()) {
    echo '<!doctype html><html><head><meta charset="utf-8"><title>Registros</title></head><body>';
    echo '<p>Si no eres redirigido automáticamente, <a href="' . htmlspecialchars($target) . '">haz clic aquí</a>.</p>';
    echo '</body></html>';
}
exit;
