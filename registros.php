<?php
// Página de administración de registros sin autenticación.
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
body { font-size: 18px; }
table { font-size: 18px; }
.form-control-sm { font-size: 16px; }
.empty-cell { background:#f0f0f0; color:#6c757d; }

.semaforo-0 td { background:#f8f9fa; }
.semaforo-1 td { background:#dc3545; color:#fff; }
.semaforo-2 td { background:#fd7e14; color:#fff; }
.semaforo-3 td { background:#198754; color:#fff; }

.arrive-btn { width:38px; height:32px; font-weight:bold; }
.table-responsive { max-height:62vh; overflow:auto; }
</style>
</head>

<body class="bg-light">
<div class="container py-4">

<h3 class="mb-3">Registros guardados</h3>

<div class="table-responsive">
<table class="table table-bordered table-sm" id="recordsTable">
<thead class="table-dark">
<tr>
<th>ID</th>
<th>Titular (Nombre / Apellido)</th>
<th>Cédula</th>
<th>Invitado 1</th>
<th>CC</th>
<th>Discapacidad</th>
<th>Invitado 2</th>
<th>CC</th>
<th>Discapacidad</th>
<th>Invitado 3</th>
<th>CC</th>
<th>Discapacidad</th>
<th>Semáforo / Acciones</th>
</tr>
</thead>
<tbody></tbody>
</table>
</div>

<div class="fw-bold mt-3 text-center">
Invitados que han llegado: <span id="contadorInvitados">0</span>
</div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<script>
const api = 'api.php';

function createInput(name, value){
  const input = document.createElement('input');
  input.className = 'form-control form-control-sm';
  input.name = name;
  input.value = value ?? '';
  if(!input.value) input.classList.add('empty-cell');
  input.oninput = () => input.classList.toggle('empty-cell', !input.value);
  return input;
}

function actionsCell(id, arrived){
  const td = document.createElement('td');
  const colors = ['#6c757d','#dc3545','#fd7e14','#198754'];

  for(let i=0;i<4;i++){
    const b = document.createElement('button');
    b.className = 'btn btn-sm arrive-btn me-1';
    b.style.background = colors[i];
    b.style.color = '#fff';
    b.textContent = i;
    b.onclick = ()=>updateArrived(id,i);
    td.appendChild(b);
  }

  const save = document.createElement('button');
  save.className = 'btn btn-sm btn-primary ms-1';
  save.textContent = 'Guardar';
  save.onclick = ()=>saveRow(td.closest('tr'), id);

  const del = document.createElement('button');
  del.className = 'btn btn-sm btn-danger ms-1';
  del.textContent = 'Eliminar';
  del.onclick = ()=>deleteRow(id);

  td.append(save, del);
  return td;
}

async function updateArrived(id,n){
  const d = new URLSearchParams({action:'update', id, arrived_count:n});
  await fetch(api,{method:'POST',body:d});
  load();
  actualizarInvitados();
}

async function saveRow(tr,id){
  const data = new URLSearchParams({action:'update', id});
  tr.querySelectorAll('input').forEach(i=>data.append(i.name,i.value));
  await fetch(api,{method:'POST',body:data});
}

async function deleteRow(id){
  if(confirm('¿Eliminar registro?')){
    await fetch(`${api}?action=delete&id=${id}`);
    load();
  }
}

async function load(){
  const res = await fetch(api+'?action=list');
  const j = await res.json();
  const tbody = document.querySelector('#recordsTable tbody');
  tbody.innerHTML='';

  j.records.forEach(r=>{
    const tr = document.createElement('tr');
    tr.className = `semaforo-${r.arrived_count ?? 0}`;

    tr.innerHTML = `<td>${r.id}</td>`;

    const titular = document.createElement('td');
    titular.append(createInput('titular_nombre',r.titular_nombre));
    titular.append(createInput('titular_apellidos',r.titular_apellidos));
    tr.appendChild(titular);

    tr.appendChild(tdWrap(createInput('titular_cc',r.titular_cc)));

    tr.appendChild(tdWrap(createInput('invitado1_nombre',r.invitado1_nombre)));
    tr.appendChild(tdWrap(createInput('invitado1_cc',r.invitado1_cc)));
    tr.appendChild(tdWrap(createInput('discapacidad',r.discapacidad)));

    tr.appendChild(tdWrap(createInput('invitado2_nombre',r.invitado2_nombre)));
    tr.appendChild(tdWrap(createInput('invitado2_cc',r.invitado2_cc)));
    tr.appendChild(tdWrap(createInput('discapacidad2',r.discapacidad2)));

    tr.appendChild(tdWrap(createInput('invitado3_nombre',r.invitado3_nombre)));
    tr.appendChild(tdWrap(createInput('invitado3_cc',r.invitado3_cc)));
    tr.appendChild(tdWrap(createInput('discapacidad3',r.discapacidad3)));

    tr.appendChild(actionsCell(r.id,r.arrived_count));
    tbody.appendChild(tr);
  });
}

function tdWrap(el){
  const td=document.createElement('td');
  td.appendChild(el);
  return td;
}

async function actualizarInvitados(){
  const r = await fetch("contador_invitados.php");
  const j = await r.json();
  document.getElementById('contadorInvitados').textContent = j.total ?? 0;
}

setInterval(actualizarInvitados,2000);
actualizarInvitados();
load();
</script>

</body>
</html>


