<?php
require_once __DIR__ . '/auth.php';
do_logout();
header('Location: /formulario/registros/login.php');
exit;
