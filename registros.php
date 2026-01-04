<?php
// Página de administración de registros sin autenticación.
// Se eliminó auth.php y require_login(), ya no pide contraseña.
?>
<!doctype html>
<html lang="es">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Registros - Administración</title>
<link href="/formulario/assets/css/styles.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

<style>
body { 
    font-size: 18px; 
    background-color: #f8f9fa;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
}

table { 
    font-size: 18px;
    border-radius: 8px;
    overflow: hidden;
}

.form-control-sm, .form-select-sm { 
    font-size: 16px; 
    padding: .35rem .5rem; 
    border-radius: 6px;
    border: 1px solid #ced4da;
    transition: all 0.2s;
}

.form-control-sm:focus, .form-select-sm:focus {
    border-color: #0d6efd;
    box-shadow: 0 0 5px rgba(13,110,253,.3);
}

.empty-cell { 
    background: #f0f0f0 !important; 
    color: #6c757d !important; 
    font-style: italic;
}

#recordsTable td { 
    background: #fff; 
    color: #000; 
    vertical-align: middle;
}

/* Semáforo - colores CESMA */
.semaforo-0 td, .semaforo-0 input, .semaforo-0 select { 
    background: #fff !important; 
    color: #212529 !important; 
}
.semaforo-1 td, .semaforo-1 input, .semaforo-1 select { 
    background: #dc3545 !important; /* rojo CESMA */
    color: #fff !important; 
}
.semaforo-2 td, .semaforo-2 input, .semaforo-2 select { 
    background: #ffc107 !important; /* amarillo (para contraste) */
    color: #212529 !important; 
}
.semaforo-3 td, .semaforo-3 input, .semaforo-3 select { 
    background: #0d6efd !important; /* azul CESMA */
    color: #fff !important; 
}

/* Botones de llegada */
.arrive-btn { 
    width: 38px; 
    height: 32px; 
    padding: 0; 
    border-radius: 8px; 
    font-weight: 700; 
    box-shadow: 0 2px 4px rgba(0,0,0,0.15);
    transition: transform 0.1s;
}

.arrive-btn:hover { transform: scale(1.05); }

td > .arrive-btn { margin-right: 6px; }

.table-responsive { 
    overflow: auto; 
    max-height: 62vh; 
    padding: 8px; 
    border-radius: 8px;
    box-shadow: 0 2px 6px rgba(0,0,0,0.08);
    background: #fff;
}

#recordsTable thead th, #recordsTable tbody td { 
    white-space: nowrap; 
    text-align: center;
}

#recordsTable input.form-control, #recordsTable select.form-select { 
    min-width: 160px; 
    text-align: center;
}

#recordsTable td:nth-child(20) { min-width: 180px; }

.boton-salida {
    position: fixed;
    top: 20px;
    right: 20px;
    background-color: #dc3545;
    color: white;
    padding: 10px 20px;
    border-radius: 25px;
    text-decoration: none;
    font-weight: bold;
    box-shadow: 0 4px 10px rgba(0,0,0,0.15);
    transition: all 0.3s ease;
}

.boton-salida:hover {
    background-color: #c82333;
    box-shadow: 0 6px 12px rgba(0,0,0,0.2);
}

.fw-bold#contadorInvitados {
    font-size: 1.2rem;
    color: #0d6efd;
}

/* ✨ OCULTAR ID Y SEMÁFORO VISUALMENTE, pero siguen funcionando */
#recordsTable th:nth-child(1),
#recordsTable th:nth-child(2),
#recordsTable td:nth-child(1),
#recordsTable td:nth-child(2) {
    display: none;
}

/* RESPONSIVE */
@media (max-width: 992px) { /* tablets */
    body { font-size: 16px; }
    table { font-size: 16px; }
    #recordsTable input.form-control, #recordsTable select.form-select { min-width: 120px; }
}

@media (max-width: 576px) { /* celulares */
    body { font-size: 14px; }
    table { font-size: 14px; }
    #recordsTable input.form-control, #recordsTable select.form-select { min-width: 100px; }
    .arrive-btn { width: 30px; height: 28px; font-size: 12px; }
}
</style>

</head>
<body class="bg-light">

<div class="container py-4 wrap">

  <div class="escudo-center" aria-hidden="true"></div>
  <div class="escudo-corner" aria-hidden="true"></div>
  <div class="gota-fondo" aria-hidden="true"></div>

  <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap">
    <h3>Registros guardados</h3>
    <a href="index.php" class="boton-salida">Salir</a>

    <div class="d-flex gap-2 align-items-center mt-2 mt-md-0">
      <input id="searchCedula" class="form-control form-control-sm" placeholder="Buscar por cédula...">
      <button id="reload" class="btn btn-sm btn-outline-secondary">Recargar</button>
    </div>
  </div>

  <div id="alert"></div>

  <div class="shadow-sm rounded" style="background:#fff;padding:6px;">
    <div style="font-size:14px;color:#6c757d;margin-bottom:6px;">
      Desplaza horizontalmente dentro de esta ventana para ver todas las columnas ⇄
    </div>
    <div class="table-responsive">
      <table class="table table-sm table-bordered" id="recordsTable">
        <thead class="table-dark text-white">
          <tr>
            <th>ID</th>
            <th>Semáforo</th>
            <th>Titular Nombre</th>
            <th>Titular Apellidos</th>
            <th>Cédula</th>
            <th>Celular</th>
            <th>Correo</th>
            <th>Hora</th>
            <th>Programa</th>
            <th>Discapacidad</th>
            <th>Invitado 1</th>
            <th>CC 1</th>
            <th>Invitado 2</th>
            <th>CC 2</th>
            <th>Invitado 3</th>
            <th>CC 3</th>
            <th>Acciones</th>
          </tr>
        </thead>
        <tbody></tbody>
      </table>
    </div>
  </div>
</div>

<!-- ✔ CONTADOR DE INVITADOS -->
<div class="fw-bold mt-3 text-center">
  Invitados que han llegado: <span id="contadorInvitados">0</span>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<script>
const api = 'api.php';
const alertEl = document.getElementById('alert');

function showAlert(msg, type='success'){
  alertEl.innerHTML = `<div class="alert alert-${type} alert-dismissible" role="alert">
  ${msg}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>`;
}

async function fetchRecords(){
  try {
    const res = await fetch(api + '?action=list');
    const j = await res.json();
    if(!j.success) { showAlert(j.message || 'Error al listar', 'danger'); return []; }
    return j.records || [];
  } catch (e) {
    showAlert('Error al conectar con la API', 'danger');
    return [];
  }
}

function createCellInput(name, value){
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

function createGuestInput(index, value){
  const td = document.createElement('td');
  const input = document.createElement('input');
  input.type = 'text';
  input.className = 'form-control form-control-sm';
  input.name = `invitado${index}_nombre`;
  input.value = value ?? '';
  if(!input.value) input.classList.add('empty-cell');
  input.addEventListener('input', () => input.classList.toggle('empty-cell', !input.value));
  td.appendChild(input);
  return td;
}

function createActionsCell(id){
  const td = document.createElement('td');
  const save = document.createElement('button');
  save.className = 'btn btn-sm btn-primary me-2';
  save.textContent = 'Guardar';
  const del = document.createElement('button');
  del.className = 'btn btn-sm btn-danger';
  del.textContent = 'Eliminar';
  const colors = ['#fff','#dc3545','#ffc107','#0d6efd'];

  async function setArrived(n){
    const data = new URLSearchParams({action:'update', id, arrived_count:n});
    try{
      const r = await fetch(api, {method:'POST', body:data});
      const j = await r.json();
      if(j.success){
        showAlert('Llegada actualizada', 'success');
        const tr = td.closest('tr');
        tr.className = `semaforo-${n}`;
        tr.querySelector('td:nth-child(2) div')?.style.background = colors[n];
        actualizarInvitados();
      } else showAlert(j.message || 'Error', 'danger');
    }catch{ showAlert('Error de red', 'danger'); }
  }

  for(let i=0;i<4;i++){
    const btn = document.createElement('button');
    btn.className='btn btn-sm arrive-btn me-1';
    btn.style.background = colors[i];
    btn.style.color = i===2 ? '#000' : '#fff';
    btn.textContent = i;
    btn.addEventListener('click',()=>setArrived(i));
    td.appendChild(btn);
  }

  save.addEventListener('click', async ()=>{ const tr = td.closest('tr'); const inputs = tr.querySelectorAll('input, select'); const data = new URLSearchParams({action:'update', id}); inputs.forEach(i=>data.append(i.name,i.value)); try{ const r = await fetch(api,{method:'POST',body:data}); const j = await r.json(); if(j.success) showAlert('Registro actualizado','success'); else showAlert(j.message || 'Error','danger'); }catch{ showAlert('Error de red','danger'); } });

  del.addEventListener('click', async ()=>{ if(!confirm('¿Eliminar este registro?')) return; try{ const r = await fetch(api + '?action=delete&id=' + id); const j = await r.json(); if(j.success){ showAlert('Registro eliminado','success'); load(); } else showAlert(j.message || 'Error','danger'); }catch{ showAlert('Error de red','danger'); } });

  td.appendChild(document.createElement('br'));
  td.appendChild(save);
  td.appendChild(del);
  return td;
}

async function load(){
  const tbody = document.querySelector('#recordsTable tbody');
  tbody.innerHTML = '';
  const records = await fetchRecords();
  const colors = ['#fff','#dc3545','#ffc107','#0d6efd'];
  for(const item of records){
    const tr = document.createElement('tr');
    tr.classList.add(`semaforo-${item.arrived_count ?? 0}`);
    tr.appendChild(Object.assign(document.createElement('td'),{textContent:item.id}));

    const semTd = document.createElement('td');
    const circle = document.createElement('div');
    circle.style.width = '20px';
    circle.style.height = '20px';
    circle.style.borderRadius = '50%';
    circle.style.background = colors[item.arrived_count ?? 0];
    semTd.appendChild(circle);
    tr.appendChild(semTd);

    tr.appendChild(createCellInput('titular_nombre', item.titular_nombre));
    tr.appendChild(createCellInput('titular_apellidos', item.titular_apellidos));
    tr.appendChild(createCellInput('titular_cc', item.titular_cc));
    tr.appendChild(createCellInput('titular_celular', item.titular_celular));
    tr.appendChild(createCellInput('titular_correo', item.titular_correo));
    tr.appendChild(createCellInput('hora', item.hora));
    tr.appendChild(createCellInput('programa', item.programa));
    tr.appendChild(createCellInput('discapacidad', item.discapacidad));
    tr.appendChild(createGuestInput(1, item.invitado1_nombre));
    tr.appendChild(createCellInput('invitado1_cc', item.invitado1_cc));
    tr.appendChild(createGuestInput(2, item.invitado2_nombre));
    tr.appendChild(createCellInput('invitado2_cc', item.invitado2_cc));
    tr.appendChild(createGuestInput(3, item.invitado3_nombre));
    tr.appendChild(createCellInput('invitado3_cc', item.invitado3_cc));

    tr.appendChild(createActionsCell(item.id));
    tbody.appendChild(tr);
  }
}

async function actualizarInvitados(){
  try {
    const r = await fetch("contador_invitados.php");
    const data = await r.json();
    document.getElementById("contadorInvitados").textContent = data.total ?? 0;
  } catch (e) { console.error("Error contando invitados:", e); }
}

// refresca cada 2 segundos
setInterval(actualizarInvitados, 2000);
actualizarInvitados();

document.getElementById('searchCedula').addEventListener('input', e=>{
  const q = e.target.value.toLowerCase();
  document.querySelectorAll('#recordsTable tbody tr').forEach(tr=>{
    const nombre = tr.children[2]?.firstChild?.value?.toLowerCase() || '';
    const apellidos = tr.children[3]?.firstChild?.value?.toLowerCase() || '';
    const cedula = tr.children[4]?.firstChild?.value?.toLowerCase() || '';
    const inv1 = tr.children[10]?.firstChild?.value?.toLowerCase() || '';
    const cc1 = tr.children[11]?.firstChild?.value?.toLowerCase() || '';
    const inv2 = tr.children[12]?.firstChild?.value?.toLowerCase() || '';
    const cc2 = tr.children[13]?.firstChild?.value?.toLowerCase() || '';
    const inv3 = tr.children[14]?.firstChild?.value?.toLowerCase() || '';
    const cc3 = tr.children[15]?.firstChild?.value?.toLowerCase() || '';
    tr.style.display = (nombre.includes(q)||apellidos.includes(q)||cedula.includes(q)||inv1.includes(q)||cc1.includes(q)||inv2.includes(q)||cc2.includes(q)||inv3.includes(q)||cc3.includes(q)) ? '' : 'none';
  });
});

load();
</script>

</body>
</html>

