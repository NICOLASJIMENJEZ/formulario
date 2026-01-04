<?php
// titulares.php

// ---------- CONEXIÓN ----------
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

// ---------- PROGRAMAS PARA EL FILTRO ----------
$programas = $pdo->query("
    SELECT DISTINCT programa
    FROM registros
    WHERE programa IS NOT NULL AND programa <> ''
    ORDER BY programa
")->fetchAll();

// ---------- FILTRO SELECCIONADO ----------
$filtro = $_GET["programa"] ?? "";

// ---------- QUERY BASE ----------
$sql = "
    SELECT
        CONCAT(titular_nombre, ' ', titular_apellidos) AS nombre_completo
    FROM registros
";

// Aplicar filtro si existe
$params = [];
if ($filtro !== "") {
    $sql .= " WHERE programa = :programa ";
    $params["programa"] = $filtro;
}

// Orden alfabético
$sql .= " ORDER BY nombre_completo ASC";

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
    max-width:700px;
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
    text-align:left;
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

    <!-- FILTRO POR PROGRAMA -->
    <form method="GET">
        <select name="programa" onchange="this.form.submit()">
            <option value="">Todos los programas</option>
            <?php foreach ($programas as $p): ?>
                <option value="<?= htmlspecialchars($p["programa"]) ?>"
                    <?= $p["programa"] === $filtro ? "selected" : "" ?>>
                    <?= htmlspecialchars($p["programa"]) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </form>
</div>

<table>
<thead>
<tr>
    <th>Nombre completo del graduado</th>
</tr>
</thead>
<tbody>
<?php if (count($titulares) === 0): ?>
<tr>
    <td>No hay registros</td>
</tr>
<?php else: ?>
<?php foreach ($titulares as $t): ?>
<tr>
    <td><?= htmlspecialchars($t["nombre_completo"]) ?></td>
</tr>
<?php endforeach; ?>
<?php endif; ?>
</tbody>
</table>

<div style="margin-top:15px;">
    <a href="graduados_contador.php">
        <button>⬅ Volver al Dashboard</button>
    </a>
</div>

</div>

</body>
</html>

