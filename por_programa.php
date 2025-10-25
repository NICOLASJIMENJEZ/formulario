<?php
// Página: Filtrar registros por programa (responsive: tabla en escritorio, tarjetas en móvil)
require_once __DIR__ . '/auth.php';
require_login();
?>
<!doctype html>
<html lang="es">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Registros por Programa</title>
<link href="/formulario/assets/css/styles.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
    body {
        font-size: 16px;
        background-color: #f8f9fa;
    }

    .panel {
        background: #fff;
        padding: 10px;
        border-radius: 10px;
        box-shadow: 0 2px 6px rgba(0,0,0,0.1);
    }

    .controls {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        align-items: center;
        margin-bottom: 15px;
    }

    .controls .form-select, 
    .controls .form-control {
        min-width: 180px;
    }

    /* 💡 Ocultar columnas innecesarias (id, celular, semáforo) */
    th:nth-child(1),
    td:nth-child(1),
    th:nth-child(2),
    td:nth-child(2),
    th:nth-child(5),
    td:nth-child(5) {
        display: none;
    }

    /* 💡 Tabla adaptativa */
    .table-responsive {
        overflow-x: auto;
    }

    /* 💡 Tarjetas (modo móvil) */
    #cardsContainer {
        display: none;
    }

    @media (max-width: 768px) {
        #tableContainer {
            display: none;
        }

        #cardsContainer {
            display: block;
        }

        .card {
            border: 1px solid #dee2e6;
            border-radius: 10px;
            box-shadow: 0 1px 4px rgba(0,0,0,0.1);
            margin-bottom: 12px;
        }

        .card-header {
            background: #212529;
            color: #fff;
            font-weight: bold;
        }
    }

    .wrap {
        position: relative;
        overflow: hidden;
    }

    /* 💡 Fondo con logo transparente */
    .escudo-center {
        position: absolute;
        left: 50%;
        top: 50%;
        transform: translate(-50%, -50%);
        width: 420px;
        height: 420px;
        background-repeat: no-repeat;
        background-position: center;
        background-size: contain;
        opacity: 0.12;
        pointer-events: none;
        z-index: 0;
        background-image: url('/formulario/registros/escudo.png');
    }

    .escudo-corner {
        position: fixed;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        width: 380px;
        height: 380px;
        background-image: url('imagenes/logo.png');
        background-repeat: no-repeat;
        background-position: center;
        background-size: contain;
        opacity: 0.15;
        z-index: 0;
        pointer-events: none;
    }

    @media(max-width:720px) { 
        .escudo-center { width: 260px; height: 260px; }
        .escudo-corner { width: 110px; height: 110px; top: 6px; right: 10px; }
    }

    /* 💡 Botón de salida */
    .boton-salida {
        position: fixed;
        top: 20px;
        right: 20px;
        background-color: #dc3545;
        color: white;
        padding: 10px 18px;
        border-radius: 25px;
        text-decoration: none;
        font-weight: bold;
        box-shadow: 0 2px 6px rgba(0,0,0,0.2);
        transition: background 0.3s ease;
        z-index: 10;
    }

    .boton-salida:hover {
        background-color: #c82333;
    }
</style>
</head>

<body>
<div class="container py-3 wrap">
    <div class="escudo-center" aria-hidden="true"></div>
    <div class="escudo-corner" aria-hidden="true"></div>

    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
        <h4 class="m-0">Registros por Programa</h4>
        <a href="index.php" class="boton-salida">Salir</a>
    </div>

    <div class="controls">
        <select id="programFilter" class="form-select form-select-sm">
            <option value="">Todos los programas</option>
        </select>
        <input id="q" class="form-control form-control-sm" placeholder="Buscar por nombre o cédula...">
        <button id="reload" class="btn btn-sm btn-outline-secondary">Recargar</button>
    </div>

    <div id="alert"></div>

    <div class="panel shadow-sm">
        <!-- 💡 Tabla visible solo en escritorio -->
        <div id="tableContainer" class="table-responsive">
            <table id="resultsTable" class="table table-striped table-hover align-middle text-center">
                <thead class="table-dark">
                    <tr>
                        <th>ID</th>
                        <th>Semáforo</th>
                        <th>Titular</th>
                        <th>Cédula</th>
                        <th>Celular</th>
                        <th>Hora</th>
                        <th>Programa</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>

        <!-- 💡 Cards (modo móvil) -->
        <div id="cardsContainer"></div>
    </div>
</div>

<script>
const api = 'api.php';  
const alertEl = document.getElementById('alert');

function showAlert(msg, type='success'){
    alertEl.innerHTML = `<div class="alert alert-${type} alert-dismissible fade show" role="alert">${msg}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>`;
}

async function fetchPrograms(){
    const res = await fetch(api + '?action=programs');
    const j = await res.json();
    if(!j.success) return [];
    return j.programs || [];
}

async function fetchRecords(params = {}){
    const qs = new URLSearchParams(Object.assign({action:'list'}, params)).toString();
    const res = await fetch(api + '?' + qs);
    const j = await res.json();
    if(!j.success){ showAlert(j.message || 'Error al listar', 'danger'); return []; }
    return j.records || [];
}

function semaforoFor(item){
    let count = null;
    if(item.hasOwnProperty('arrived_count') && item.arrived_count !== null && item.arrived_count !== ''){
        const n = parseInt(item.arrived_count); if(!isNaN(n)) count = Math.max(0, Math.min(3, n));
    }
    if(count === null){
        count = [1,2,3].reduce((acc,i)=> acc + ((item[`invitado${i}_nombre`]||'') ? 1 : 0), 0);
    }
    return Math.min(3, Math.max(0, count));
}

function renderTable(records){
    const tbody = document.querySelector('#resultsTable tbody'); 
    tbody.innerHTML = '';
    records.forEach(item => {
        const tr = document.createElement('tr');
        const s = semaforoFor(item);
        tr.classList.add('semaforo-' + s);
        tr.innerHTML = `
            <td>${item.id}</td>
            <td><span style="display:inline-block;width:18px;height:18px;border-radius:50%;background:${s===0?'#6c757d':s===1?'#dc3545':s===2?'#fd7e14':'#198754'}" title="${s} llegadas"></span></td>
            <td>${(item.titular_nombre||'') + ' ' + (item.titular_apellidos||'')}</td>
            <td>${item.titular_cc||''}</td>
            <td>${item.titular_celular||''}</td>
            <td>${item.hora||''}</td>
            <td>${item.programa||''}</td>
        `;
        tbody.appendChild(tr);
    });
}

function renderCards(records){
    const c = document.getElementById('cardsContainer'); 
    c.innerHTML = '';
    records.forEach(item => {
        const s = semaforoFor(item);
        const card = document.createElement('div'); 
        card.className = 'card';
        const header = document.createElement('div'); 
        header.className = 'card-header'; 
        header.textContent = (item.titular_nombre||'') + ' ' + (item.titular_apellidos||'');
        const body = document.createElement('div'); 
        body.className = 'card-body';
        body.innerHTML = `
            <p><strong>Cédula:</strong> ${item.titular_cc||''}</p>
            <p><strong>Hora:</strong> ${item.hora||''}</p>
            <p><strong>Programa:</strong> ${item.programa||''}</p>
        `;
        card.appendChild(header); 
        card.appendChild(body); 
        c.appendChild(card);
    });
}

async function load(){
    const sel = document.getElementById('programFilter');
    const list = await fetchPrograms();
    sel.innerHTML = '<option value="">Todos los programas</option>' + list.map(p => {
        if(p === '__EMPTY__') return `<option value="__EMPTY__">(sin programa)</option>`;
        return `<option value="${p}">${p}</option>`;
    }).join('');
    applyFiltersServer();
}

async function applyFiltersServer(){
    const q = document.getElementById('q').value || '';
    const program = document.getElementById('programFilter').value || '';
    const params = {};
    if(program) params.programa = program;
    if(q) params.q = q;
    const records = await fetchRecords(params);
    renderTable(records);
    renderCards(records);
}

document.getElementById('reload').addEventListener('click', load);
document.getElementById('programFilter').addEventListener('change', applyFiltersServer);
document.getElementById('q').addEventListener('input', function(){
    clearTimeout(window.__pp_debounce);
    window.__pp_debounce = setTimeout(applyFiltersServer, 300);
});

load();
</script>
</body>
</html>

