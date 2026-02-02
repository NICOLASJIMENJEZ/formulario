<?php
// Página de administración de registros optimizada
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
        font-size: 14px;
        padding: 12px 8px !important;
    }

    .form-control-sm {
        font-size: 15px;
        border: 1px solid transparent;
        background: rgba(0,0,0,0.03);
        transition: all 0.2s;
        padding: 2px 5px;
    }
    .form-control-sm:focus {
        background: #fff;
        border-color: var(--accent);
        box-shadow: 0 0 5px rgba(52,152,219,0.3);
    }
    .empty-cell { background: #fff3cd !important; }

    tr.semaforo-0 { border-left: 5px solid #6c757d; }
    tr.semaforo-1 { border-left: 5px solid #dc3545; background-color: #fff5f5; }
    tr.semaforo-2 { border-left: 5px solid #fd7e14; background-color: #fff9f0; }
    tr.semaforo-3 { border-left: 5px solid #198754; background-color: #f4fff9; }

    .btn-group-arrive .btn {
        padding: 2px 8px;
        font-weight: bold;
        font-size: 12px;
    }
    .btn-save { background: #3498db; color: white; border: none; }
    .btn-delete { background: #e74c3c; color: white; border: none; }

    .bg-deco {
        position: fixed; top: 50%; left: 50%; transform: translate(-50%,-50%);
        opacity: 0.05; z-index: -1; width: 400px;
    }

    .boton-salida {
        background: #e74c3c; color: white; padding: 6px 15px;
        border-radius: 20px; text-decoration: none; font-weight: bold;
    }

    .card-view { display: none; }
    
    .record-card {
        background: white; border-radius: 12px; padding: 20px;
        margin-bottom: 15px; box-shadow: 0 2px 10px rgba(0,0,0,0.08);
        border-left: 5px solid #6c757d; position: relative;
    }
    
    .record-card.semaforo-1 { border-left-color: #dc3545; background-color: #fff5f5; }
    .record-card.semaforo-2 { border-left-color: #fd7e14; background-color: #fff9f0; }
    .record-card.semaforo-3 { border-left-color: #198754; background-color: #f4fff9; }
    
    .card-header-info {
        display: flex; justify-content: space-between; align-items: center;
        margin-bottom: 15px; padding-bottom: 10px; border-bottom: 2px solid #e9ecef;
    }
    
    .card-name { font-size: 18px; font-weight: bold; color: var(--primary-dark); }
    .card-cedula { font-size: 14px; color: #6c757d; }
    .card-label { font-size: 12px; font-weight: 600; color: #6c757d; text-transform: uppercase; margin-bottom: 5px; }
    .card-input { width: 100%; padding: 8px 12px; border: 1px solid #dee2e6; border-radius: 6px; font-size: 15px; }
    
    .semaforo-controls { display: flex; gap: 8px; margin: 15px 0; }
    .semaforo-btn {
        flex: 1; padding: 12px; border: 2px solid #dee2e6; border-radius: 8px;
        font-weight: bold; font-size: 16px; cursor: pointer; transition: all 0.2s; background: white;
    }
    
    .semaforo-btn.active-0 { background: #6c757d; color: white; border-color: #6c757d; }
    .semaforo-btn.active-1 { background: #dc3545; color: white; border-color: #dc3545; }
    .semaforo-btn.active-2 { background: #fd7e14; color: white; border-color: #fd7e14; }
    .semaforo-btn.active-3 { background: #198754; color: white; border-color: #198754; }
    
    .card-actions { display: flex; gap: 10px; margin-top: 15px; }
    .card-btn { flex: 1; padding: 12px; border: none; border-radius: 8px; font-weight: bold; cursor: pointer; }
    .card-btn-save { background: #3498db; color: white; }
    .card-btn-delete { background: #e74c3c; color: white; }

    @media (max-width: 768px) {
        .col-hide-mobile { display: none; }
        .table-view { display: none; }
        .card-view { display: block; }
        #searchCedula { width: 100% !important; }
        .bg-deco { display: none; }
    }
    
    @media (min-width: 769px) {
        .table-view { display: block; }
        .card-view { display: none; }
    }
</style>
</head>
<body>

<div class="container-fluid px-4 py-3">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h4 class="mb-0 text-dark fw-bold">Gestión de Invitados</h4>
            <span class="badge bg-secondary">Admin Panel</span>
        </div>
        
        <div class="d-flex gap-3 align-items-center">
            <input id="searchCedula" class="form-control" placeholder="🔍 Buscar nombre o CC..." style="width:280px; border-radius: 20px;">
            <button id="reload" class="btn btn-outline-primary btn-sm rounded-pill">🔄 Actualizar</button>
            <a href="formulario.php" class="boton-salida">Salir</a>
        </div>
    </div>

    <div id="alert"></div>

    <div class="table-responsive shadow-sm table-view">
        <table class="table table-hover align-middle" id="recordsTable">
            <thead>
                <tr>
                    <th class="col-hide-mobile">ID</th>
                    <th>Titular (Nombre y Apellido)</th>
                    <th>Cédula</th>
                    <th>Discapacidad</th>
                    <th style="border-left: 2px solid #ddd">Invitado 1</th>
                    <th>CC 1</th>
                    <th style="border-left: 2px solid #ddd">Invitado 2</th>
                    <th>CC 2</th>
                    <th style="border-left: 2px solid #ddd">Invitado 3</th>
                    <th>CC 3</th>
                    <th class="text-center">Semáforo / Acciones</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>

    <div class="card-view" id="cardView"></div>

    <div class="d-flex justify-content-center align-items-center mt-3 p-3 bg-white rounded shadow-sm">
        <div class="h5 mb-0">
            ✅ Total Invitados en Sitio: <span id="contadorInvitados" class="badge bg-success fs-5">0</span>
        </div>
    </div>
</div>

<script>
const api = 'api.php';
const alertEl = document.getElementById('alert');

function showAlert(msg, type='success'){
    alertEl.innerHTML = `<div class="alert alert-${type} alert-dismissible fade show" role="alert">
    ${msg}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>`;
    setTimeout(() => alertEl.innerHTML = '', 3000);
}

async function fetchRecords(){
    try {
        const res = await fetch(api + '?action=list');
        const j = await res.json();
        return j.success ? j.records : [];
    } catch (e) { return []; }
}

function createInput(name, value){
    const td = document.createElement('td');
    const input = document.createElement('input');
    input.type = 'text';
    input.className = 'form-control form-control-sm';
    input.name = name;
    input.value = value ?? '';
    if(!input.value) input.classList.add('empty-cell');
    input.addEventListener('input', () => input.classList.toggle('empty-cell', !input.value));
    td.appendChild(input);
    return td;
}

function createActionsCell(id, currentCount){
    const td = document.createElement('td');
    td.className = 'text-center';
    const groupArrive = document.createElement('div');
    groupArrive.className = 'btn-group btn-group-arrive mb-2';
    const colors = ['#6c757d','#dc3545','#fd7e14','#198754'];
    
    for(let i=0; i<4; i++){
        const btn = document.createElement('button');
        btn.className = 'btn btn-outline-secondary';
        btn.style.backgroundColor = (currentCount == i) ? colors[i] : 'transparent';
        btn.style.color = (currentCount == i) ? '#fff' : '#000';
        btn.textContent = i;
        btn.onclick = () => setArrived(id, i);
        groupArrive.appendChild(btn);
    }
    
    const groupActions = document.createElement('div');
    groupActions.className = 'd-flex gap-1 justify-content-center';
    const save = document.createElement('button');
    save.className = 'btn btn-sm btn-save'; save.innerHTML = '💾';
    save.onclick = () => saveRecord(id, save);
    const del = document.createElement('button');
    del.className = 'btn btn-sm btn-delete'; del.innerHTML = '🗑️';
    del.onclick = () => deleteRecord(id);

    groupActions.append(save, del);
    td.append(groupArrive, groupActions);
    return td;
}

function createMobileCard(item){
    const card = document.createElement('div');
    card.className = `record-card semaforo-${item.arrived_count ?? 0}`;
    card.innerHTML = `
        <div class="card-header-info">
            <div>
                <div class="card-name">${item.titular_nombre} ${item.titular_apellidos}</div>
                <div class="card-cedula">CC: ${item.titular_cc}</div>
            </div>
        </div>
        <div class="card-section">
            <div class="card-label">Discapacidad</div>
            <input type="text" class="card-input" name="discapacidad" value="${item.discapacidad || ''}">
        </div>
        <div class="card-section">
            <div class="card-label">Invitado 1</div>
            <input type="text" class="card-input mb-1" name="invitado1_nombre" value="${item.invitado1_nombre || ''}">
            <input type="text" class="card-input" name="invitado1_cc" value="${item.invitado1_cc || ''}">
        </div>
        <div class="card-actions">
            <button class="card-btn card-btn-save" onclick="saveRecordFromCard(${item.id}, this.closest('.record-card'))">💾 Guardar</button>
            <button class="card-btn card-btn-delete" onclick="deleteRecord(${item.id})">🗑️ Eliminar</button>
        </div>
    `;
    return card;
}

async function setArrived(id, n){
    const data = new URLSearchParams({action:'update', id, arrived_count:n});
    const r = await fetch(api, {method:'POST', body:data});
    const j = await r.json();
    if(j.success) { load(); actualizarInvitados(); }
}

async function saveRecord(id, btn){
    const tr = btn.closest('tr');
    const inputs = tr.querySelectorAll('input');
    const data = new URLSearchParams({action:'update', id});
    inputs.forEach(i => data.append(i.name, i.value));
    const r = await fetch(api, {method:'POST', body:data});
    const j = await r.json();
    if(j.success) showAlert('Actualizado correctamente');
}

async function deleteRecord(id){
    if(!confirm('¿Eliminar este registro?')) return;
    const r = await fetch(api + '?action=delete&id=' + id);
    const j = await r.json();
    if(j.success) load();
}

async function load(){
    const tbody = document.querySelector('#recordsTable tbody');
    const cardView = document.getElementById('cardView');
    tbody.innerHTML = '<tr><td colspan="11" class="text-center">Cargando...</td></tr>';
    
    const records = await fetchRecords();
    tbody.innerHTML = '';
    cardView.innerHTML = '';

    records.forEach(item => {
        const tr = document.createElement('tr');
        tr.className = `semaforo-${item.arrived_count ?? 0}`;
        
        // ID
        const tdId = document.createElement('td');
        tdId.className = 'col-hide-mobile text-muted';
        tdId.textContent = item.id;
        tr.appendChild(tdId);

        // TITULAR: NOMBRE Y APELLIDO ABAJO (MODIFICADO)
        const tdTitular = document.createElement('td');
        const divTitular = document.createElement('div');
        divTitular.className = 'd-flex flex-column gap-1'; // flex-column hace que se pongan uno abajo del otro
        
        const inNom = document.createElement('input');
        inNom.className = 'form-control form-control-sm';
        inNom.name = 'titular_nombre';
        inNom.value = item.titular_nombre;
        inNom.placeholder = "Nombre";
        
        const inApe = document.createElement('input');
        inApe.className = 'form-control form-control-sm';
        inApe.name = 'titular_apellidos';
        inApe.value = item.titular_apellidos;
        inApe.placeholder = "Apellido";
        
        divTitular.append(inNom, inApe);
        tdTitular.appendChild(divTitular);
        tr.appendChild(tdTitular);

        tr.appendChild(createInput('titular_cc', item.titular_cc));
        tr.appendChild(createInput('discapacidad', item.discapacidad));
        tr.appendChild(createInput('invitado1_nombre', item.invitado1_nombre));
        tr.appendChild(createInput('invitado1_cc', item.invitado1_cc));
        tr.appendChild(createInput('invitado2_nombre', item.invitado2_nombre));
        tr.appendChild(createInput('invitado2_cc', item.invitado2_cc));
        tr.appendChild(createInput('invitado3_nombre', item.invitado3_nombre));
        tr.appendChild(createInput('invitado3_cc', item.invitado3_cc));
        tr.appendChild(createActionsCell(item.id, item.arrived_count));

        tbody.appendChild(tr);
        cardView.appendChild(createMobileCard(item));
    });
}

async function actualizarInvitados(){
    try {
        const r = await fetch("contador_invitados.php");
        const data = await r.json();
        document.getElementById("contadorInvitados").textContent = data.total ?? 0;
    } catch (e) {}
}

document.getElementById('searchCedula').addEventListener('input', e => {
    const q = e.target.value.toLowerCase();
    document.querySelectorAll('#recordsTable tbody tr').forEach(tr => {
        const text = tr.innerText.toLowerCase() + Array.from(tr.querySelectorAll('input')).map(i => i.value.toLowerCase()).join('');
        tr.style.display = text.includes(q) ? '' : 'none';
    });
});

document.getElementById('reload').onclick = load;
setInterval(actualizarInvitados, 5000);
actualizarInvitados();
load();
</script>
</body>
</html>
