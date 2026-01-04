<?php
// titulares.php

if (file_exists(__DIR__ . "/db.php")) {
    require_once __DIR__ . "/db.php";
} else {
    try {
        $pdo = new PDO(
            "pgsql:host=" . getenv("DB_HOST") . ";port=" . getenv("DB_PORT") . ";dbname=" . getenv("DB_NAME"),
            getenv("DB_USER"),
            getenv("DB_PASS"),
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
            ]
        );
    } catch (Exception $e) {
        die("Error de conexión");
    }
}

// Programas para el filtro
$programas = $pdo->query("
    SELECT DISTINCT programa
    FROM registros
    WHERE programa IS NOT NULL AND programa <> ''
    ORDER BY programa
")->fetchAll();

// Filtro seleccionado
$filtro = $_GET["programa"] ?? "";

// Query base
$sql = "
    SELECT
        CONCAT(titular_nombre, ' ', titular_apellidos) AS nombre,
        programa
    FROM registros
";

// Aplicar filtro
$params = [];
if ($filtro !== "") {
    $sql .= " WHERE programa = :programa ";
    $params["programa"] = $filtro;
}

// Orden alfabético
$sql .= " ORDER BY nombre ASC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$titulares = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Titulares</title>
<meta name="viewport" content="width=device-width, initial-scale=1">

<style>
body {
    font-family: Arial;
    background:#f4f4f4;
    padding:20px;
}
.card {
    max-width:800px;
    margin:auto;
    background:#fff;
    padding:20px;
    border-radius:10px;
    box-shadow:0 0 10px #0002;
}
table {
    width:100%;
    border-collapse:collapse;
    margin-top:15px;
}
th, td {
    padding:10px;
    border-bottom:1px solid #ddd;
}
th {
    background:#eee;
}
select, button {
    padding:8px;
    border-radius:6px;
    border:1px solid #ccc;
}
.header {
    display:flex;
    justify-content:space-between;
    align-items:center;
    flex-wrap:wrap;
    gap:10px;
}
</style>
</head>

<body>

<div class="card">

<div class="header">
    <h2>Listado de Titulares</h2>

    <form method="GET">
        <select name="programa" onchange="this.form.submit()">
            <option value="">Todos los programas</option>
            <?php foreach ($programas as $p): ?>
                <option value="<?= $p["programa"] ?>"
                    <?= $p["programa"] === $filtro ? "selected" : "" ?>>
                    <?= $p["programa"] ?>
                </option>
            <?php endforeach; ?>
        </select>
    </form>
</div>

<table>
<thead>
<tr>
    <th>Nombre Completo</th>
    <th>Programa</th>
</tr>
</thead>
<tbody>
<?php foreach ($titulares as $t): ?>
<tr>
    <td><?= htmlspecialchars($t["nombre"]) ?></td>
    <td><?= htmlspecialchars($t["programa"]) ?></td>
</tr>
<?php endforeach; ?>
</tbody>
</table>

<div style="margin-top:15px;">
    <a href="dashboard_unificado.php">
        <button>⬅ Volver al Dashboard</button>
    </a>
</div>

</div>

</body>
</html>
