<?php
require "db.php";

$q = $pdo->query("
    SELECT 
        SUM(invitado1) + SUM(invitado2) + SUM(invitado3) AS total
    FROM registros
");

echo json_encode($q->fetch());
