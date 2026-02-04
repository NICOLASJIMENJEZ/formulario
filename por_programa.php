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

    .contador-box{
        margin-top:20px; padding:12px; background:#fff;
        border-radius:10px; text-align:center; font-weight:bold;
        box-shadow:0 1px 4px rgba(0,0,0,0.1);
    }

    /* Estilos para tarjetas móviles mejoradas */
    .mobile-card {
        background: white;
        border-radius: 12px;
        padding: 20px;
        margin-bottom: 15px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.08);
        border-left: 5px solid #6c757d;
    }
    
    .mobile-card.arrived {
        border-left-color: #198754;
        background-color: #f4fff9;
    }
    
    .mobile-card-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 15px;
        padding-bottom: 10px;
        border-bottom: 2px solid #e9ecef;
    }
    
    .mobile-card-name {
        font-size: 18px;
        font-weight: bold;
        color: #2c3e50;
        margin-bottom: 5px;
    }
    
    .mobile-card-cedula {
        font-size: 14px;
        color: #6c757d;
    }
    
    .mobile-card-info {
        margin-bottom: 12px;
    }
    
    .mobile-card-label {
        font-size: 12px;
        font-weight: 600;
        color: #6c757d;
        text-transform: uppercase;
        margin-bottom: 3px;
    }
    
    .mobile-card-value {
        font-size: 15px;
        color: #2c3e50;
    }
    
    .mobile-card-button {
        width: 100%;
        padding: 14px;
        border: none;
        border-radius: 8px;
        font-weight: bold;
        font-size: 16px;
        cursor: pointer;
        transition: all 0.3s;
        margin-top: 10px;
    }
    
    .mobile-card-button.arrived {
        background: #198754;
        color: white;
    }
    
    .mobile-card-button.not-arrived {
        background: #6c757d;
        color: white;
    }

    #cardsContainer { display:none; }
    
    @media (max-width: 768px) {
        #tableContainer { display:none; }
        #cardsContainer { display:block; }
        
        .controls {
            flex-direction: column;
        }
        
        .controls select, .controls input, .controls button {
            width: 100%;
        }
    }
</style>
</head>

<body>
<div class="container py-3 wrap">

    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
        <h4 class="m-0">Registros por Programa</h4>
        <a href="formulario.php" class="btn btn-danger">Salir</a>
    </div>

    <div class="controls">
        <select id="hourFilter" class="form-select form-select-sm">
            <option value="">Todas las horas</option>
            <option value="09:00">09:00 AM</option>
            <option value="14:00">02:00 PM</option>
            <option value="16:30">04:30 PM</option>
        </select>

        <select id="programFilter" class="form-select form-select-sm">
            <option value="">Todos los programas</option>
        </select>

        <input id="q" class="form-control form-control-sm" placeholder="Buscar por nombre o cédula...">

        <button id="reload" class="btn btn-sm btn-outline-secondary">🔄 Recargar</button>
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
                        <th>Llegada</th>
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
let arrivalState = {}; // ESTADO VISUAL LOCAL

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
   USA: toggle_program_arrival (independiente de arrived_count)
========================== */
async function toggleArrivalVisual(id){
    try {
        const form = new FormData();
        form.append('id', id);

        const res = await fetch(api + '?action=toggle_program_arrival', {
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
            alert("Error: " + (j.message || 'Error desconocido'));
        }
    } catch(e){
        console.error("Error al marcar llegada:", e);
        alert("Error de conexión. Intente nuevamente.");
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
            arrivalState[item.id] = item.program_arrival || 0;
        }

        const tr = document.createElement('tr');
        const arrived = arrivalState[item.id] === 1;

        tr.innerHTML = `
            <td>${item.id}</td>
            <td></td>
            <td>${item.titular_nombre || ''} ${item.titular_apellidos || ''}</td>
            <td>${item.titular_cc || ''}</td>
            <td>${item.titular_celular || ''}</td>
            <td>${item.hora || ''}</td>
            <td>${item.programa || ''}</td>
            <td>
                <button class="btn btn-sm ${arrived ? 'btn-success' : 'btn-secondary'}"
                        onclick="toggleArrivalVisual(${item.id})">
                    ${arrived ? '✓ Llegó' : 'Marcar llegada'}
                </button>
            </td>
        `;

        tbody.appendChild(tr);
    });
}

function renderCards(records){
    const c = document.getElementById('cardsContainer');
    c.innerHTML = '';

    records.forEach(item => {
        if(arrivalState[item.id] === undefined){
            arrivalState[item.id] = item.program_arrival || 0;
        }

        const arrived = arrivalState[item.id] === 1;
        
        const card = document.createElement('div');
        card.className = `mobile-card ${arrived ? 'arrived' : ''}`;
        
        card.innerHTML = `
            <div class="mobile-card-header">
                <div>
                    <div class="mobile-card-name">${item.titular_nombre || ''} ${item.titular_apellidos || ''}</div>
                    <div class="mobile-card-cedula">CC: ${item.titular_cc || ''}</div>
                </div>
            </div>
            
            <div class="mobile-card-info">
                <div class="mobile-card-label">Programa</div>
                <div class="mobile-card-value">${item.programa || 'No especificado'}</div>
            </div>
            
            <div class="mobile-card-info">
                <div class="mobile-card-label">Hora</div>
                <div class="mobile-card-value">${item.hora || 'No especificada'}</div>
            </div>
            
            <div class="mobile-card-info">
                <div class="mobile-card-label">Celular</div>
                <div class="mobile-card-value">${item.titular_celular || 'No registrado'}</div>
            </div>
            
            <button class="mobile-card-button ${arrived ? 'arrived' : 'not-arrived'}"
                    onclick="toggleArrivalVisual(${item.id})">
                ${arrived ? '✓ Llegó al Evento' : 'Marcar Llegada'}
            </button>
        `;
        
        c.appendChild(card);
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

// Cargar datos iniciales
load();

// Auto-refrescar cada 30 segundos
setInterval(load, 30000);
</script>
</body>
</html>
