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

<!-- BOOTSTRAP -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<!-- ICONOS -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

<style>
body {
    background: linear-gradient(135deg, #e9f5ee, #f8f9fa);
}
.card {
    border: none;
    border-radius: 16px;
}
.card-title {
    font-weight: 600;
}
.kpi {
    font-size: 3rem;
    font-weight: bold;
    color: #198754;
}
.table thead {
    background: #198754;
    color: white;
}
.badge-soft {
    background: rgba(25,135,84,.15);
    color: #198754;
}
</style>
</head>

<body>

<div class="container py-4">

<div class="row g-4">

<!-- PANEL IZQUIERDO -->
<div class="col-lg-4">
    <div class="card shadow-sm h-100">
        <div class="card-body text-center">
            <h5 class="card-title mb-3">
                <i class="bi bi-mortarboard-fill"></i> Total de Graduados
            </h5>

            <div id="total" class="kpi">0</div>

            <p class="text-muted">
                Última actualización:<br>
                <strong id="last">-</strong>
            </p>

            <div class="d-grid gap-2 mt-3">
                <button class="btn btn-success" onclick="loadData()">
                    <i class="bi bi-arrow-clockwise"></i> Actualizar
                </button>

                <button id="autoBtn" class="btn btn-outline-secondary">
                    Auto: ON (5s)
                </button>

                <a href="titulares.php" class="btn btn-dark mt-2">
                    <i class="bi bi-list-ul"></i> Ver Titulares
                </a>
            </div>
        </div>
    </div>
</div>

<!-- PANEL DERECHO -->
<div class="col-lg-8">
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <h5 class="card-title">
                <i class="bi bi-bar-chart-fill"></i> Graduados por Programa
            </h5>
            <div class="table-responsive">
                <table class="table table-hover mt-3" id="tablaProgramas">
                    <thead>
                        <tr><th>Programa</th><th>Total</th></tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <h5 class="card-title">
                <i class="bi bi-clock-history"></i> Graduados e Invitados por Hora
            </h5>
            <div class="table-responsive">
                <table class="table table-hover mt-3" id="tablaHoras">
                    <thead>
                        <tr>
                            <th>Hora</th>
                            <th>Graduados</th>
                            <th>Invitados</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">
            <h5 class="card-title">
                <i class="bi bi-people-fill"></i> Invitados por Programa
            </h5>
            <div class="table-responsive">
                <table class="table table-hover mt-3" id="tablaInvitadosPrograma">
                    <thead>
                        <tr><th>Programa</th><th>Invitados</th></tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>
</div>

</div>
</div>

<script>
let auto = true;
let interval;

function loadData() {
    fetch("?json=1")
        .then(r => r.json())
        .then(data => {
            if (!data.success) return alert(data.error);

            document.getElementById("total").innerText = data.total;
            document.getElementById("last").innerText =
                new Date().toLocaleTimeString();

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

document.getElementById("autoBtn").onclick = () => {
    auto = !auto;
    document.getElementById("autoBtn").innerText =
        auto ? "Auto: ON (5s)" : "Auto: OFF";
    auto ? startAuto() : clearInterval(interval);
};

function startAuto() {
    interval = setInterval(loadData, 5000);
}

loadData();
startAuto();
</script>

</body>
</html>

