<?php
// Página de administración de registros optimizada con campos apilados para Titular e Invitados
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
        font-size: 14px; 
    }
    
    .table-responsive { 
        max-height: 75vh; 
        overflow: auto; 
        box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        border-radius: 8px;
        background: #fff;
    }

    table { margin-bottom: 0 !important; }
    thead th { 
        background-color: var(--primary-dark) !important; 
        color: white !important; 
        position: sticky; 
        top: 0; 
        z-index: 10;
        font-weight: 500;
        text-transform: uppercase;
        font-size: 12px;
        padding: 12px 8px !important;
        text-align: center;
    }

    /* Contenedor para inputs apilados (Estilo Titular e Invitados) */
    .stack-container {
        display: flex;
        flex-direction: column;
        gap: 4px;
        min-width: 150px;
        padding: 4px 0;
    }

    .form-control-sm {
        font-size: 13px;
        border: 1px solid transparent;
        background: #f8f9fa;
        transition: all 0.2s;
        padding: 4px 8px;
        border-radius: 4px;
    }

    .form-control-sm:focus {
        background: #fff;
        border-color: var(--accent);
        box-shadow: 0 0 5px rgba(52,152,219,0.3);
    }

    .empty-cell { background: #fff3cd !important; }

    /* Semáforos de fila */
    tr.semaforo-0 { border-left: 5px solid #6c757d; }
    tr.semaforo-1 { border-left: 5px solid #dc3545; background-color: #fff5f5; }
    tr.semaforo-2 { border-left: 5px solid #fd7e14; background-color: #fff9f0; }
    tr.semaforo-3 { border-left: 5px solid #198754; background-color: #f4fff9; }

    .btn-group-arrive .btn {
        padding: 2px 8px;
        font-weight: bold;
        font-size: 11px;
    }
    .btn-save { background: #3498db; color: white; border: none; }
    .btn-delete { background: #e74c3c; color: white; border: none; }

    .boton-salida {
        background: #e74c3c; color: white; padding: 6px 15px;
        border-radius: 20px; text-decoration: none; font-weight: bold;
    }

    @media (max-width: 992px) {
        .table-view { display: none; }
    }
</style>
</head>
<body>

<div class="container-fluid px-4 py-3">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h4 class="mb-0 text-dark fw-bold">Gestión de Acceso</h4>
            <span class="badge bg-secondary">Panel Administrativo</span>
        </div>
        
        <div class="d-flex gap-3 align-items-center">
            <input id="searchCedula" class="form-control" placeholder="🔍 Buscar..." style="width:250px; border-radius: 20px;">
            <button id="reload" class="btn btn-outline-primary btn-sm rounded-pill">🔄 Actualizar</button>
            <a href="formulario.php" class="boton-salida">Salir</a>
        </div>
    </div>

    <div id="alert"></div>

    <div class="table-responsive shadow-sm table-view">
        <table class="table table-hover align-middle" id="recordsTable">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Titular (Nombre / Apellido)</th>
                    <th>Cédula</th>
                    <th>Discap.</th>
                    <th>Invitado 1 (Nombre / CC)</th>
                    <th>Invitado 2 (Nombre / CC)</th>
                    <th>Invitado 3 (Nombre / CC)</th>
                    <th class="text-center">Acciones</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>

    <div class="d-flex justify-content-center align-items-center mt-3 p-3 bg-white rounded shadow-sm">
        <div class="h5 mb-0">
            ✅ Total en Sitio: <span id="contadorInvitados" class="badge bg-success fs-5">0</span>
        </div>
    </div>
</div>

<script>
const api = 'api.php';

// Función unificada para crear los cuadros apilados (Igual al Titular)
function createStackedInput(name1, val1, name2, val2, placeholder1, placeholder2) {
    const td = document.createElement('td');
    const container = document.createElement('div');
    container.className = 'stack-container';

    const in1 = document.createElement('input');
    in1.className = `form-control form-control-sm ${!val1 ? 'empty-cell' : ''}`;
    in1.name = name1;
    in1.value = val1 ?? '';
    in1.placeholder = placeholder1;

    const in2 = document.createElement('input');
    in2.className = `form-control form-control-sm ${!val2 ? 'empty-cell' : ''}`;
    in2.name = name2;
    in2.value = val2 ?? '';
    in2.placeholder = placeholder2;

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
    actions.className = 'd-flex gap-1 justify-content-center';
    actions.innerHTML = `
        <button class="btn btn-sm btn-save" onclick="saveRow(${id}, this)">💾</button>
        <button class="btn btn-sm btn-delete" onclick="deleteRow(${id})">🗑️</button>
    `;
    td.append(group, actions);
    return td;
}

async function load() {
    const tbody = document.querySelector('#recordsTable tbody');
    tbody.innerHTML = '<tr><td colspan="8" class="text-center">Cargando...</td></tr>';
    
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
            tdId.className = 'text-muted fw-bold text-center';
            tr.appendChild(tdId);

            // Titular (Apilado)
            tr.appendChild(createStackedInput(
                'titular_nombre', item.titular_nombre, 
                'titular_apellidos', item.titular_apellidos,
                'Nombre', 'Apellido'
            ));

            // Cédula Titular (Simple)
            tr.appendChild(createSimpleInput('titular_cc', item.titular_cc));

            // Discapacidad (Simple)
            tr.appendChild(createSimpleInput('discapacidad', item.discapacidad));

            // Invitado 1 (Apilado como el titular)
            tr.appendChild(createStackedInput(
                'invitado1_nombre', item.invitado1_nombre, 
                'invitado1_cc', item.invitado1_cc,
                'Nombre Invitado 1', 'Cédula'
            ));

            // Invitado 2 (Apilado como el titular)
            tr.appendChild(createStackedInput(
                'invitado2_nombre', item.invitado2_nombre, 
                'invitado2_cc', item.invitado2_cc,
                'Nombre Invitado 2', 'Cédula'
            ));

            // Invitado 3 (Apilado como el titular)
            tr.appendChild(createStackedInput(
                'invitado3_nombre', item.invitado3_nombre, 
                'invitado3_cc', item.invitado3_cc,
                'Nombre Invitado 3', 'Cédula'
            ));

            // Acciones
            tr.appendChild(createActions(item.id, item.arrived_count));

            tbody.appendChild(tr);
        });
    } catch (e) {
        tbody.innerHTML = '<tr><td colspan="8" class="text-center text-danger">Error de conexión</td></tr>';
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
        btn.innerHTML = '✅';
        setTimeout(() => btn.innerHTML = '💾', 2000);
    }
}

async function setArrived(id, val) {
    const params = new URLSearchParams({action: 'update', id: id, arrived_count: val});
    await fetch(api, {method: 'POST', body: params});
    load();
    updateCounter();
}

async function deleteRow(id) {
    if(!confirm('¿Eliminar este registro permanentemente?')) return;
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

document.getElementById('searchCedula').addEventListener('input', e => {
    const q = e.target.value.toLowerCase();
    document.querySelectorAll('#recordsTable tbody tr').forEach(tr => {
        const content = tr.innerText.toLowerCase() + Array.from(tr.querySelectorAll('input')).map(i => i.value.toLowerCase()).join(' ');
        tr.style.display = content.includes(q) ? '' : 'none';
    });
});

document.getElementById('reload').onclick = load;
setInterval(updateCounter, 5000);
updateCounter();
load();
</script>
</body>
</html>
