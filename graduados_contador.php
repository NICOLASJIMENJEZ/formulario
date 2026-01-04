<?php
// dashboard_unificado.php

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
        $pdo = null;
        $db_error = $e->getMessage();
    }
}

// ---------- JSON ----------
if (isset($_GET["json"])) {
    header("Content-Type: application/json");

    if (!$pdo) {
        echo json_encode([
            "success" => false,
            "error" => $db_error ?? "Sin conexión"
        ]);
        exit;
    }

    try {
        $total = $pdo->query("SELECT COUNT(*) FROM registros")->fetchColumn();

        $por_programa = $pdo->query("
            SELECT programa, COUNT(*) AS total
            FROM registros
            WHERE programa IS NOT NULL AND programa <> ''
            GROUP BY programa
            ORDER BY programa ASC
        ")->fetchAll();

        $por_hora = $pdo->query("
            SELECT
                to_char(hora,'HH24:MI') AS hora,
                COUNT(*) AS graduados,
                SUM(
                    (CASE WHEN invitado1_nombre <> '' THEN 1 ELSE 0 END) +
                    (CASE WHEN invitado2_nombre <> '' THEN 1 ELSE 0 END) +
                    (CASE WHEN invitado3_nombre <> '' THEN 1 ELSE 0 END)
                ) AS invitados
            FROM registros
            WHERE hora IS NOT NULL
            GROUP BY to_char(hora,'HH24:MI')
            ORDER BY hora
        ")->fetchAll();

        $invitados_por_programa = $pdo->query("
            SELECT
                programa,
                SUM(
                    (CASE WHEN invitado1_nombre <> '' THEN 1 ELSE 0 END) +
                    (CASE WHEN invitado2_nombre <> '' THEN 1 ELSE 0 END) +
                    (CASE WHEN invitado3_nombre <> '' THEN 1 ELSE 0 END)
                ) AS invitados
            FROM registros
            WHERE programa IS NOT NULL AND programa <> ''
            GROUP BY programa
            ORDER BY programa ASC
        ")->fetchAll();

        echo json_encode([
            "success" => true,
            "total" => (int)$total,
            "by_program" => $por_programa,
            "by_hour" => $por_hora,
            "by_program_guests" => $invitados_por_programa
        ]);
        exit;

    } catch (Exception $e) {
        echo json_encode([
            "success" => false,
            "error" => $e->getMessage()
        ]);
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Dashboard Graduados</title>
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
body.dark{
    --bg:#0f172a;
    --card:#020617;
    --text:#e5e7eb;
}

body{
    background:linear-gradient(135deg,var(--bg),#ffffff);
    color:var(--text);
    transition:.3s;
}

.card{
    border:none;
    border-radius:18px;
    background:var(--card);
    box-shadow:0 12px 35px rgba(0,0,0,.15);
    transition:.3s;
}

.card:hover{
    transform:translateY(-6px);
}

.kpi{
    font-size:3.5rem;
    font-weight:800;
    color:var(--red);
}

.table thead{
    background:var(--blue);
    color:white;
}

.badge-soft{
    background:rgba(196,22,28,.15);
    color:var(--red);
    font-weight:600;
}

.fade-up{
    animation:fadeUp .6s ease both;
}

@keyframes fadeUp{
    from{opacity:0; transform:translateY(20px)}
    to{opacity:1; transform:translateY(0)}
}

/* Toggle */
.toggle-dark{
    position:fixed;
    top:20px;
    right:20px;
    z-index:10;
}
</style>
</head>

<body>

<button class="btn btn-outline-dark toggle-dark" onclick="toggleDark()">
    <i class="bi bi-moon-stars-fill"></i>
</button>

<div class="container py-4">

<div class="row g-4 fade-up">

<!-- KPI -->
<div class="col-lg-4">
    <div class="card h-100">
        <div class="card-body text-center">
            <h5 class="mb-3">
                <i class="bi bi-mortarboard-fill"></i> Total de Graduados
            </h5>

            <div id="total" class="kpi">0</div>

            <p class="text-muted">
                Última actualización<br>
                <strong id="last">-</strong>
            </p>

            <div class="d-grid gap-2">
                <button class="btn btn-danger" onclick="loadData()">
                    <i class="bi bi-arrow-clockwise"></i> Actualizar
                </button>
                <button id="autoBtn" class="btn btn-outline-secondary">
                    Auto: ON (5s)
                </button>
                <a href="titulares.php" class="btn btn-dark">
                    <i class="bi bi-list-ul"></i> Ver Titulares
                </a>
            </div>
        </div>
    </div>
</div>

<!-- TABLAS -->
<div class="col-lg-8">

<div class="card mb-4">
<div class="card-body">
<h5><i class="bi bi-bar-chart-fill"></i> Graduados por Programa</h5>
<table class="table table-hover mt-3" id="tablaProgramas">
<thead><tr><th>Programa</th><th>Total</th></tr></thead>
<tbody></tbody>
</table>
</div>
</div>

<div class="card mb-4">
<div class="card-body">
<h5><i class="bi bi-clock-history"></i> Graduados e Invitados por Hora</h5>
<table class="table table-hover mt-3" id="tablaHoras">
<thead><tr><th>Hora</th><th>Graduados</th><th>Invitados</th></tr></thead>
<tbody></tbody>
</table>
</div>
</div>

<div class="card">
<div class="card-body">
<h5><i class="bi bi-people-fill"></i> Invitados por Programa</h5>
<table class="table table-hover mt-3" id="tablaInvitadosPrograma">
<thead><tr><th>Programa</th><th>Invitados</th></tr></thead>
<tbody></tbody>
</table>
</div>
</div>

</div>
</div>
</div>

<script>
let auto=true, interval;
let currentTotal=0;

/* Contador animado */
function animateCounter(el, start, end){
    let duration=600;
    let startTime=null;
    function step(t){
        if(!startTime) startTime=t;
        let progress=Math.min((t-startTime)/duration,1);
        el.innerText=Math.floor(progress*(end-start)+start);
        if(progress<1) requestAnimationFrame(step);
    }
    requestAnimationFrame(step);
}

function loadData(){
    fetch("?json=1")
    .then(r=>r.json())
    .then(data=>{
        if(!data.success) return alert(data.error);

        animateCounter(document.getElementById("total"), currentTotal, data.total);
        currentTotal=data.total;

        document.getElementById("last").innerText=new Date().toLocaleTimeString();

        document.querySelector("#tablaProgramas tbody").innerHTML =
            data.by_program.map(r =>
                `<tr><td>${r.programa}</td><td><span class="badge badge-soft">${r.total}</span></td></tr>`
            ).join("");

        document.querySelector("#tablaHoras tbody").innerHTML =
            data.by_hour.map(r =>
                `<tr><td>${r.hora}</td><td>${r.graduados}</td><td>${r.invitados}</td></tr>`
            ).join("");

        document.querySelector("#tablaInvitadosPrograma tbody").innerHTML =
            data.by_program_guests.map(r =>
                `<tr><td>${r.programa}</td><td>${r.invitados}</td></tr>`
            ).join("");
    });
}

document.getElementById("autoBtn").onclick=()=>{
    auto=!auto;
    document.getElementById("autoBtn").innerText=auto?"Auto: ON (5s)":"Auto: OFF";
    auto?startAuto():clearInterval(interval);
};

function startAuto(){ interval=setInterval(loadData,5000); }

function toggleDark(){
    document.body.classList.toggle("dark");
}

loadData();
startAuto();
</script>

</body>
</html>


