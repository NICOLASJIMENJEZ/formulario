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

$params = [];
if ($filtro !== "") {
    $sql .= " WHERE programa = :programa ";
    $params["programa"] = $filtro;
}

$sql .= " ORDER BY nombre_completo ASC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$titulares = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Listado de Titulares</title>
<meta name="viewport" content="width=device-width, initial-scale=1">

<!-- Bootstrap -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

<style>
:root{
    --blue:#0b3c6f;
    --red:#c4161c;
    --bg:#eef4ff;
    --card:#ffffff;
    --text:#1f2937;
}

body{
    background:linear-gradient(135deg,var(--bg),#ffffff);
    font-family:system-ui,Segoe UI,Roboto,Arial;
    color:var(--text);
}

.card{
    border:none;
    border-radius:18px;
    box-shadow:0 12px 30px rgba(0,0,0,.15);
}

h2{
    color:var(--blue);
    font-weight:700;
}

.table thead{
    background:var(--blue);
    color:#fff;
}

.table tbody tr{
    transition:.2s;
}

.table tbody tr:hover{
    background:rgba(11,60,111,.05);
}

.btn-danger{
    background:var(--red);
    border:none;
}

.fade-up{
    animation:fadeUp .5s ease both;
}

@keyframes fadeUp{
    from{opacity:0; transform:translateY(15px)}
    to{opacity:1; transform:translateY(0)}
}

/* RESPONSIVE */
@media(max-width:768px){
    h2{font-size:1.3rem}
    .table{font-size:.9rem}
}

/* PDF */
@media print{
    body{
        background:white;
    }
    .no-print{
        display:none !important;
    }
    .card{
        box-shadow:none;
        border-radius:0;
    }
}
</style>
</head>

<body>

<div class="container py-4 fade-up">

<div class="card">
<div class="card-body">

<!-- HEADER -->
<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
    <h2>
        <i class="bi bi-people-fill"></i> Listado de Titulares
    </h2>

    <div class="d-flex gap-2 no-print">

        <!-- FILTRO -->
        <form method="GET">
            <select name="programa" class="form-select" onchange="this.form.submit()">
                <option value="">Todos los programas</option>
                <?php foreach ($programas as $p): ?>
                    <option value="<?= htmlspecialchars($p["programa"]) ?>"
                        <?= $p["programa"] === $filtro ? "selected" : "" ?>>
                        <?= htmlspecialchars($p["programa"]) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </form>

        <!-- PDF -->
        <button onclick="exportPDF()" class="btn btn-danger">
            <i class="bi bi-file-earmark-pdf-fill"></i> PDF
        </button>

        <!-- VOLVER -->
        <a href="graduados_contador.php" class="btn btn-dark">
            <i class="bi bi-arrow-left"></i>
        </a>
    </div>
</div>

<!-- TABLA -->
<div class="table-responsive">
<table class="table table-hover align-middle">
<thead>
<tr>
    <th>Nombre completo del graduado</th>
</tr>
</thead>
<tbody>
<?php if (count($titulares) === 0): ?>
<tr>
    <td class="text-center text-muted">No hay registros</td>
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
</div>

</div>
</div>

</div>

<script>
function exportPDF(){
    window.print();
}
</script>

</body>
</html>

