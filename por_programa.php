<?php
require_once __DIR__ . '/db.php';
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
    body { font-size: 16px; background-color: #f8f9fa; }

    .panel {
        background: #fff; padding: 10px; border-radius: 10px;
        box-shadow: 0 2px 6px rgba(0,0,0,0.1);
    }

    .controls {
        display: flex; flex-wrap: wrap; gap: 8px;
        align-items: center; margin-bottom: 15px;
    }

    th:nth-child(1), td:nth-child(1),
    th:nth-child(2), td:nth-child(2),
    th:nth-child(5), td:nth-child(5) { display: none; }

    .semaforo-gray { background:#6c757d; }
    .semaforo-green { background:#198754; }

    .bolita {
        width: 18px; height: 18px; border-radius: 50%;
        display: inline-block;
        cursor: pointer;
    }

    .contador-box{
        margin-top:20px; padding:12px; background:#fff;
        border-radius:10px; text-align:center; font-weight:bold;
        box-shadow:0 1px 4px rgba(0,0,0,0.1);
    }

    #cardsContainer { display:none; }
    @media (max-width: 768px) {
        #tableContainer { display:none; }
        #cardsContainer { display:block; }
    }
</style>
</head>

<body>
<div class="container py-3 wrap">

    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
        <h4 class="m-0">Registros por Programa</h4>
        <a href="index.php" class="btn btn-danger">Salir</a>
    </div>

    <div class="controls">
        <select id="hourFilter" class="form-select form-select-sm">
            <option value="">Todas las horas</option>
            <option value="9:30">9:30</option>
            <option value="2:00">2:00</option>
            <option value="16:30">16:30</option>
        </select>

        <select id="programFilter" class="form-select form-select-sm">
            <option value="">Todos los programas</option>
        </select>

        <input id="q" class="form-control form-control-sm" placeholder="Buscar por nombre o cédula...">

        <button id="reload" class="btn btn-sm btn-outline-secondary">Recargar</button>
    </div>

    <div id="alert"></div>

    <div class="panel shadow-sm">

        <!-- TABLA -->
        <div id="tableContainer" class="table-responsive">
            <table id="resultsTable" class="table table-striped table-hover align-middle text-center">
                <thead class="table-dark">
                    <tr>
                        <th>ID</th>
                        <th>Acción</th>
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

        <!-- CARDS -->
        <div id="cardsContainer"></div>

    </div>

    <div id="contadorLlegadas" class="contador-box">
        Han llegado: 0 personas
    </div>

</div>

<script>
const api = 'api1.php';
let arrivalState = {}; // ESTADO VISUAL

async function fetchPrograms(){
    const res = await fetch(api + '?action=programs');
    const j = await res.json();
    return j.success ? (j.programs || []) : [];
}

async function fetchRecords(params = {}){
    const qs = new URLSearchParams(Object.assign({action:'list'}, params)).toString();
    const res = await fetch(api + '?' + qs);
    const j = await res.json();
    return j.success ? (j.records || []) : [];
}

/* ==========================
   TOGGLE VISUAL + API
========================== */
async function toggleArrivalVisual(id){
    try {
        const form = new FormData();
        form.append('id', id);

        const res = await fetch(api + '?action=toggle_arrival', {
            method: 'POST',
            body: form
        });

        const j = await res.json();
        if(j.success){
            arrivalState[id] = j.new_value;

            renderTable(lastRecords);
            renderCards(lastRecords);
            updateCounter();
        } else {
            alert("Error al cambiar semáforo: " + j.message);
        }
    } catch(e){
        console.error("Error semáforo:", e);
    }
}

/* ==========================
   CONTADOR VISUAL
========================== */
function updateCounter(){
    const llegados = Object.values(arrivalState).filter(v => v === 1).length;
    document.getElementById('contadorLlegadas').textContent =
        "Han llegado: " + llegados + " personas";
}

function renderTable(records){
    const tbody = document.querySelector('#resultsTable tbody');
    tbody.innerHTML = '';

    records.forEach(item => {
        if(arrivalState[item.id] === undefined){
            arrivalState[item.id] = item.arrived_count || 0;
        }

        const tr = document.createElement('tr');

        tr.innerHTML = `
            <td>${item.id}</td>

            <td>
                <span 
                    class="bolita ${arrivalState[item.id] == 1 ? 'semaforo-green' : 'semaforo-gray'}"
                    onclick="toggleArrivalVisual(${item.id})">
                </span>
            </td>

            <td>${item.titular_nombre || ''} ${item.titular_apellidos || ''}</td>
            <td>${item.titular_cc || ''}</td>
            <td>${item.titular_celular || ''}</td>
            <td>${item.hora || ''}</td>
            <td>${item.programa || ''}</td>
        `;

        tbody.appendChild(tr);
    });
}

function renderCards(records){
    const c = document.getElementById('cardsContainer');
    c.innerHTML = '';

    records.forEach(item => {
        if(arrivalState[item.id] === undefined){
            arrivalState[item.id] = item.arrived_count || 0;
        }

        c.innerHTML += `
            <div class="card p-2 mb-2">
                <h5>${item.titular_nombre||''} ${item.titular_apellidos||''}</h5>
                <p><strong>Cédula:</strong> ${item.titular_cc}</p>
                <p><strong>Programa:</strong> ${item.programa}</p>
                <p><strong>Hora:</strong> ${item.hora}</p>

                <button class="btn btn-sm ${arrivalState[item.id]==1?'btn-success':'btn-secondary'}"
                        onclick="toggleArrivalVisual(${item.id})">
                    ${arrivalState[item.id]==1?'Llegó':'Marcar llegada'}
                </button>
            </div>
        `;
    });
}

let lastRecords = [];

async function load(){
    const programSel = document.getElementById('programFilter');
    const list = await fetchPrograms();

    programSel.innerHTML =
        '<option value="">Todos los programas</option>' +
        list.map(p => `<option value="${p}">${p}</option>`).join('');

    applyFiltersServer();
}

async function applyFiltersServer(){
    const q = document.getElementById('q').value || '';
    const program = document.getElementById('programFilter').value || '';
    const hour = document.getElementById('hourFilter').value || '';

    const params = {};
    if(q) params.q = q;
    if(program) params.programa = program;
    if(hour) params.hora = hour;

    lastRecords = await fetchRecords(params);

    renderTable(lastRecords);
    renderCards(lastRecords);
    updateCounter();
}

document.getElementById('reload').addEventListener('click', load);
document.getElementById('programFilter').addEventListener('change', applyFiltersServer);
document.getElementById('hourFilter').addEventListener('change', applyFiltersServer);

document.getElementById('q').addEventListener('input', () => {
    clearTimeout(window.__debounce);
    window.__debounce = setTimeout(applyFiltersServer, 300);
});

load();
</script>
</body>
</html>

