<?php
// Admin con campos apilados visibles y letra de 16px
?>
<!doctype html>
<html lang="es">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Admin - Control de Acceso</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
    :root {
        --primary-dark: #2c3e50;
        --accent: #3498db;
    }
    body { 
        font-family: 'Segoe UI', Roboto, sans-serif; 
        background-color: #f4f7f6;
        font-size: 16px; 
    }
    
    .table-responsive { 
        max-height: 80vh; 
        overflow: auto; 
        box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        border-radius: 8px;
        background: #fff;
    }

    thead th { 
        background-color: var(--primary-dark) !important; 
        color: white !important; 
        position: sticky; 
        top: 0; 
        z-index: 10;
        font-size: 14px;
        padding: 15px 8px !important;
        text-align: center;
        white-space: nowrap;
    }

    /* AJUSTE CLAVE: Contenedor con más espacio vertical */
    .stack-container {
        display: flex;
        flex-direction: column;
        gap: 5px; /* Espacio entre los dos cuadros */
        min-width: 180px;
        padding: 8px 0; /* Margen interno para que no se corte el texto */
    }

    /* AJUSTE CLAVE: Altura del input para que se vea el texto completo */
    .form-control-sm {
        font-size: 16px !important; 
        height: 38px !important; /* Altura suficiente para letra de 16px */
        border: 1px solid transparent;
        background: #f1f3f4;
        transition: all 0.2s;
        padding: 5px 10px;
    }

    .form-control-sm:focus {
        background: #fff;
        border-color: var(--accent);
        box-shadow: 0 0 5px rgba(52,152,219,0.3);
    }

    .empty-cell { background: #fff3cd !important; }

    /* Semáforos */
    tr.semaforo-0 { border-left: 6px solid #6c757d; }
    tr.semaforo-1 { border-left: 6px solid #dc3545; background-color: #fff8f8; }
    tr.semaforo-2 { border-left: 6px solid #fd7e14; background-color: #fffbf5; }
    tr.semaforo-3 { border-left: 6px solid #198754; background-color: #f6fff9; }

    .btn-group-arrive .btn { padding: 4px 10px; font-weight: bold; font-size: 14px; }
    .btn-save { background: #3498db; color: white; border: none; padding: 8px 12px; }
    .btn-delete { background: #e74c3c; color: white; border: none; padding: 8px 12px; }

    .boton-salida {
        background: #e74c3c; color: white; padding: 8px 20px;
        border-radius: 25px; text-decoration: none; font-weight: bold;
    }
</style>
</head>
<body>

<div class="container-fluid px-4 py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="mb-0 text-dark fw-bold">Gestión de Acceso</h3>
            <span class="text-muted">Panel de Control de Invitados</span>
        </div>
        
        <div class="d-flex gap-3 align-items-center">
            <input id="searchCedula" class="form-control form-control-lg" placeholder="🔍 Buscar por nombre o CC..." style="width:350px; border-radius: 30px; font-size: 16px;">
            <button id="reload" class="btn btn-primary rounded-pill px-4">🔄 Actualizar</button>
            <a href="formulario.php" class="boton-salida">Salir</a>
        </div>
    </div>

    <div class="table-responsive shadow-sm">
        <table class="table table-hover align-middle" id="recordsTable">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Titular (Nombre / Apellido)</th>
                    <th>Cédula</th>
                    <th>Discapacidad</th>
                    <th>Invitado 1 / CC</th>
                    <th>Invitado 2 / CC</th>
                    <th>Invitado 3 / CC</th>
                    <th class="text-center">Acciones</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>

    <div class="text-center mt-4">
        <div class="d-inline-block p-3 bg-white rounded-pill shadow-sm border">
            <h4 class="mb-0 px-3">
                ✅ Total en Sitio: <span id="contadorInvitados" class="badge bg-success">0</span>
            </h4>
        </div>
    </div>
</div>

<script>
const api = 'api.php';

// Función para normalizar texto: quita tildes y puntos
function normalizeText(text) {
    if (!text) return '';
    return text.toString()
        .toLowerCase()
        .normalize("NFD").replace(/[\u0300-\u036f]/g, "") // Quita tildes
        .replace(/[.\-\s]/g, ''); // Quita puntos, guiones y espacios
}

// Esta función crea los dos cuadros (Nombre y Apellido/CC) uno sobre otro
function createStackedInput(name1, val1, name2, val2, p1, p2) {
    const td = document.createElement('td');
    const container = document.createElement('div');
    container.className = 'stack-container';

    const in1 = document.createElement('input');
    in1.className = `form-control form-control-sm ${!val1 ? 'empty-cell' : ''}`;
    in1.name = name1;
    in1.value = val1 ?? '';
    in1.placeholder = p1;

    const in2 = document.createElement('input');
    in2.className = `form-control form-control-sm ${!val2 ? 'empty-cell' : ''}`;
    in2.name = name2;
    in2.value = val2 ?? '';
    in2.placeholder = p2;

    [in1, in2].forEach(el => {
        el.addEventListener('input', () => el.classList.toggle('empty-cell', !el.value));
    });

    container.append(in1, in2);
    td.appendChild(container);
    return td;
}

function createSimpleInput(name, value) {
    const td = document.createElement('td');
    const input = document.createElement('input');
    input.className = `form-control form-control-sm ${!value ? 'empty-cell' : ''}`;
    input.name = name;
    input.value = value ?? '';
    input.addEventListener('input', () => input.classList.toggle('empty-cell', !input.value));
    td.appendChild(input);
    return td;
}

function createActions(id, current) {
    const td = document.createElement('td');
    td.className = 'text-center';
    
    const group = document.createElement('div');
    group.className = 'btn-group btn-group-arrive mb-2';
    const colors = ['#6c757d','#dc3545','#fd7e14','#198754'];
    
    for(let i=0; i<4; i++){
        const btn = document.createElement('button');
        btn.className = 'btn btn-outline-secondary';
        btn.style.backgroundColor = (current == i) ? colors[i] : 'transparent';
        btn.style.color = (current == i) ? '#fff' : '#000';
        btn.textContent = i;
        btn.onclick = () => setArrived(id, i);
        group.appendChild(btn);
    }
    
    const actions = document.createElement('div');
    actions.className = 'd-flex gap-2 justify-content-center';
    actions.innerHTML = `
        <button class="btn btn-save" onclick="saveRow(${id}, this)" title="Guardar">💾</button>
        <button class="btn btn-delete" onclick="deleteRow(${id})" title="Eliminar">🗑️</button>
    `;
    td.append(group, actions);
    return td;
}

async function load() {
    const tbody = document.querySelector('#recordsTable tbody');
    tbody.innerHTML = '<tr><td colspan="8" class="text-center py-5">Cargando registros...</td></tr>';
    
    try {
        const res = await fetch(api + '?action=list');
        const data = await res.json();
        tbody.innerHTML = '';

        data.records.forEach(item => {
            const tr = document.createElement('tr');
            tr.className = `semaforo-${item.arrived_count ?? 0}`;
            
            // ID
            const tdId = document.createElement('td');
            tdId.textContent = item.id;
            tdId.className = 'text-center fw-bold text-muted';
            tr.appendChild(tdId);

            // Titular apilado (Nombre / Apellido)
            tr.appendChild(createStackedInput('titular_nombre', item.titular_nombre, 'titular_apellidos', item.titular_apellidos, 'Nombre', 'Apellido'));
            
            // CC y Discapacidad
            tr.appendChild(createSimpleInput('titular_cc', item.titular_cc));
            tr.appendChild(createSimpleInput('discapacidad', item.discapacidad));

            // Invitados apilados (Nombre / CC)
            tr.appendChild(createStackedInput('invitado1_nombre', item.invitado1_nombre, 'invitado1_cc', item.invitado1_cc, 'Invitado 1', 'Cédula'));
            tr.appendChild(createStackedInput('invitado2_nombre', item.invitado2_nombre, 'invitado2_cc', item.invitado2_cc, 'Invitado 2', 'Cédula'));
            tr.appendChild(createStackedInput('invitado3_nombre', item.invitado3_nombre, 'invitado3_cc', item.invitado3_cc, 'Invitado 3', 'Cédula'));

            tr.appendChild(createActions(item.id, item.arrived_count));
            tbody.appendChild(tr);
        });
    } catch (e) {
        tbody.innerHTML = '<tr><td colspan="8" class="text-center text-danger">Error al conectar con la base de datos.</td></tr>';
    }
}

async function saveRow(id, btn) {
    const tr = btn.closest('tr');
    const inputs = tr.querySelectorAll('input');
    const params = new URLSearchParams({action: 'update', id: id});
    inputs.forEach(i => params.append(i.name, i.value));
    const res = await fetch(api, {method: 'POST', body: params});
    const result = await res.json();
    if(result.success) {
        const oldIcon = btn.innerHTML;
        btn.innerHTML = '✅';
        setTimeout(() => btn.innerHTML = oldIcon, 2000);
    }
}

async function setArrived(id, val) {
    const params = new URLSearchParams({action: 'update', id: id, arrived_count: val});
    await fetch(api, {method: 'POST', body: params});
    load();
    updateCounter();
}

async function deleteRow(id) {
    if(!confirm('¿Seguro que deseas eliminar este registro?')) return;
    await fetch(api + `?action=delete&id=${id}`);
    load();
}

async function updateCounter() {
    try {
        const r = await fetch("contador_invitados.php");
        const d = await r.json();
        document.getElementById("contadorInvitados").textContent = d.total ?? 0;
    } catch(e) {}
}

// BÚSQUEDA MEJORADA: Normaliza tanto la búsqueda como el contenido
document.getElementById('searchCedula').addEventListener('input', e => {
    const q = normalizeText(e.target.value);
    
    document.querySelectorAll('#recordsTable tbody tr').forEach(tr => {
        // Texto visible en la fila
        const visibleText = normalizeText(tr.innerText);
        
        // Valores de los inputs
        const inputValues = Array.from(tr.querySelectorAll('input'))
            .map(i => normalizeText(i.value))
            .join('');
        
        // Combinar todo el contenido
        const allContent = visibleText + inputValues;
        
        // Mostrar/ocultar según coincidencia
        tr.style.display = allContent.includes(q) ? '' : 'none';
    });
});

document.getElementById('reload').onclick = load;
updateCounter();
load();
</script>
</body>
</html>
