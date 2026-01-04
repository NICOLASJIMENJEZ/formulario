<?php
// dashboard_unificado.php
// Dashboard: total graduados, por programa, por hora y por invitados

// ---------- CONEXIÓN A LA BASE DE DATOS ----------
if (file_exists(__DIR__ . "/db.php")) {
    require_once __DIR__ . "/db.php"; // Debe crear $pdo
} else {
    $db_host = getenv("DB_HOST");
    $db_port = getenv("DB_PORT");
    $db_name = getenv("DB_NAME");
    $db_user = getenv("DB_USER");
    $db_pass = getenv("DB_PASS");

    try {
        $pdo = new PDO(
            "pgsql:host=$db_host;port=$db_port;dbname=$db_name",
            $db_user,
            $db_pass,
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

// ---------- SI PIDE JSON ----------
if (isset($_GET["json"])) {
    header("Content-Type: application/json");

    if (!$pdo) {
        echo json_encode([
            "success" => false,
            "error" => $db_error ?? "Sin conexión a la base de datos"
        ]);
        exit;
    }

    try {
        // TOTAL DE GRADUADOS
        $total = $pdo->query("SELECT COUNT(*) FROM registros")->fetchColumn();

        // POR PROGRAMA
        $por_programa = $pdo->query("
            SELECT programa, COUNT(*) AS total
            FROM registros
            WHERE programa IS NOT NULL AND programa <> ''
            GROUP BY programa
            ORDER BY total DESC
        ")->fetchAll();

        // POR HORA (CORREGIDO)
        $por_hora = $pdo->query("
            SELECT to_char(hora,'HH24:MI') AS hora, COUNT(*) AS total
            FROM registros
            WHERE hora IS NOT NULL
            GROUP BY to_char(hora,'HH24:MI')
            ORDER BY hora ASC
        ")->fetchAll();

        // INVITADOS POR TITULAR
        $por_usuario = $pdo->query("
            SELECT
                CONCAT(titular_nombre, ' ', titular_apellidos) AS titular,
                programa,
                (
                    CASE WHEN invitado1_nombre IS NOT NULL AND invitado1_nombre <> '' THEN 1 ELSE 0 END +
                    CASE WHEN invitado2_nombre IS NOT NULL AND invitado2_nombre <> '' THEN 1 ELSE 0 END +
                    CASE WHEN invitado3_nombre IS NOT NULL AND invitado3_nombre <> '' THEN 1 ELSE 0 END
                ) AS invitados
            FROM registros
            ORDER BY titular
        ")->fetchAll();

        echo json_encode([
            "success" => true,
            "total" => intval($total),
            "by_program" => $por_programa,
            "by_hour" => $por_hora,
            "by_user" => $por_usuario
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
    <title>Dashboard Unificado — Graduados</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <style>
        body { font-family: Arial; background:#f4f4f4; padding:20px; }
        .wrap {
            max-width:1200px;
            margin:auto;
            display:grid;
            grid-template-columns:300px 1fr;
            gap:20px;
        }
        .card {
            background:#fff;
            padding:20px;
            border-radius:10px;
            box-shadow:0 0 10px #0002;
        }
        .big {
            font-size:48px;
            color:#0a8235;
            font-weight:bold;
            text-align:center;
        }
        table {
            width:100%;
            margin-top:10px;
            border-collapse:collapse;
        }
        th, td {
            padding:8px;
            border-bottom:1px solid #ddd;
            text-align:left;
        }
        th { background:#eee; }
        button {
            padding:8px 12px;
            border:none;
            background:#0a8235;
            color:white;
            border-radius:6px;
            cursor:pointer;
        }
        button.secondary { background:#444; }
        .controls { margin-top:15px; display:flex; gap:10px; }
        @media(max-width:900px){
            .wrap{grid-template-columns:1fr;}
        }
    </style>
</head>

<body>

<div class="wrap">

    <div class="card">
        <h2>Total de Graduados</h2>
        <div id="total" class="big">0</div>
        <p>Última actualización: <span id="last">-</span></p>

        <div class="controls">
            <button onclick="loadData()">Actualizar</button>
            <button id="autoBtn" class="secondary">Auto: ON (5s)</button>
        </div>
    </div>

    <div class="card">
        <h2>Graduados por Programa</h2>
        <table id="tablaProgramas">
            <thead>
                <tr><th>Programa</th><th>Total</th></tr>
            </thead>
            <tbody>
                <tr><td colspan="2">Cargando...</td></tr>
            </tbody>
        </table>

        <h2 style="margin-top:20px;">Graduados por Hora</h2>
        <table id="tablaHoras">
            <thead>
                <tr><th>Hora</th><th>Total</th></tr>
            </thead>
            <tbody>
                <tr><td colspan="2">Cargando...</td></tr>
            </tbody>
        </table>

        <h2 style="margin-top:20px;">Invitados por Titular</h2>
        <table id="tablaUsuarios">
            <thead>
                <tr>
                    <th>Titular</th>
                    <th>Programa</th>
                    <th>Invitados</th>
                </tr>
            </thead>
            <tbody>
                <tr><td colspan="3">Cargando...</td></tr>
            </tbody>
        </table>
    </div>

</div>

<script>
let auto = true;
let interval;

function loadData() {
    fetch("?json=1")
        .then(r => r.json())
        .then(data => {
            if (!data.success) {
                alert("Error: " + data.error);
                return;
            }

            document.getElementById("total").innerText = data.total;
            document.getElementById("last").innerText =
                new Date().toLocaleTimeString();

            // Programas
            document.querySelector("#tablaProgramas tbody").innerHTML =
                data.by_program.map(r =>
                    `<tr><td>${r.programa}</td><td>${r.total}</td></tr>`
                ).join("");

            // Horas
            document.querySelector("#tablaHoras tbody").innerHTML =
                data.by_hour.map(r =>
                    `<tr><td>${r.hora}</td><td>${r.total}</td></tr>`
                ).join("");

            // Invitados por titular
            document.querySelector("#tablaUsuarios tbody").innerHTML =
                data.by_user.map(r =>
                    `<tr>
                        <td>${r.titular}</td>
                        <td>${r.programa}</td>
                        <td>${r.invitados}</td>
                    </tr>`
                ).join("");
        });
}

document.getElementById("autoBtn").onclick = () => {
    auto = !auto;
    document.getElementById("autoBtn").innerText =
        auto ? "Auto: ON (5s)" : "Auto: OFF";

    if (auto) startAuto();
    else clearInterval(interval);
};

function startAuto() {
    interval = setInterval(loadData, 5000);
}

loadData();
startAuto();
</script>

</body>
</html>
