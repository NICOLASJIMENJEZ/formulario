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
body { font-size: 17px; }
table { font-size: 16px; }

.form-control-sm {
  font-size: 15px;
  padding: .3rem .45rem;
}

.empty-cell {
  background: #f0f0f0 !important;
  color: #6c757d !important;
}

.input-stack input {
  margin-bottom: 4px;
}

.semaforo-0 td { background:#f8f9fa !important; }
.semaforo-1 td { background:#dc3545 !important; color:#fff; }
.semaforo-2 td { background:#fd7e14 !important; color:#fff; }
.semaforo-3 td { background:#198754 !important; color:#fff; }

.arrive-btn {
  width: 36px;
  height: 32px;
  padding: 0;
  font-weight: bold;
}

.table-responsive {
  max-height: 62vh;
  overflow:auto;
}

@media(max-width:768px){
  body { font-size:15px; }
  .form-control-sm { font-size:14px; }
}
</style>
</head>

<body class="bg-light">

<div class="container py-4">

<h4 class="mb-3">Registros guardados</h4>

<div class="table-responsive shadow bg-white rounded p-2">
<table class="table table-sm table-bordered" id="recordsTable">

<thead class="table-dark">
<tr>
  <th>ID</th>
  <th>Titular<br><small>Nombre / Apellido</small></th>
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

<div class="fw-bold text-center mt-3">
Invitados que han llegado: <span id="contadorInvitados">0</span>
</div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<script>
const api = 'api.php';

function input(name, value='') {
  const i = document.createElement('input');
  i.type = 'text';
  i.name = name;
  i.value = value ?? '';
  i.className = 'form-control form-control-sm';
  if(!i.value) i.classList.add('empty-cell');
  i.oninput = ()=>i.classList.toggle('empty-cell', !i.value);
  return i;
}

function stack(...inputs){
  const d=document.createElement('div');
  d.className='input-stack';
  inputs.forEach(i=>d.appendChild(i));
  return d;
}

async function fetchRecords(){
  const r = await fetch(api+'?action=list');
  const j = await r.json();
  return j.records || [];
}

function acciones(id){
  const td=document.createElement('td');
  const colors=['#6c757d','#dc3545','#fd7e14','#198754'];

  for(let i=0;i<4;i++){
    const b=document.createElement('button');
    b.className='btn btn-sm arrive-btn me-1';
    b.style.background=colors[i];
    b.style.color='#fff';
    b.textContent=i;
    b.onclick=()=>updateArrived(id,i);
    td.appendChild(b);
  }

  td.appendChild(document.createElement('br'));

  const save=document.createElement('button');
  save.className='btn btn-sm btn-primary me-1';
  save.textContent='Guardar';
  save.onclick=()=>saveRow(td);

  const del=document.createElement('button');
  del.className='btn btn-sm btn-danger';
  del.textContent='Eliminar';
  del.onclick=()=>deleteRow(id);

  td.append(save,del);
  return td;
}

async function updateArrived(id,n){
  await fetch(api,{method:'POST',body:new URLSearchParams({action:'update',id,arrived_count:n})});
  load();
  actualizarInvitados();
}

async function saveRow(td){
  const tr=td.parentElement;
  const data=new URLSearchParams({action:'update',id:tr.dataset.id});
  tr.querySelectorAll('input').forEach(i=>data.append(i.name,i.value));
  await fetch(api,{method:'POST',body:data});
}

async function deleteRow(id){
  if(confirm('¿Eliminar registro?')){
    await fetch(api+'?action=delete&id='+id);
    load();
  }
}

async function load(){
  const tbody=document.querySelector('#recordsTable tbody');
  tbody.innerHTML='';
  const records=await fetchRecords();

  records.forEach(it=>{
    const tr=document.createElement('tr');
    tr.dataset.id=it.id;
    tr.className='semaforo-'+(it.arrived_count||0);

    tr.appendChild(Object.assign(document.createElement('td'),{textContent:it.id}));

    tr.appendChild(Object.assign(document.createElement('td'),{
      appendChild:stack(
        input('titular_nombre',it.titular_nombre),
        input('titular_apellidos',it.titular_apellidos)
      )
    }));

    tr.appendChild(Object.assign(document.createElement('td'),{
      appendChild:input('titular_cc',it.titular_cc)
    }));

    for(let i=1;i<=3;i++){
      tr.appendChild(Object.assign(document.createElement('td'),{
        appendChild:stack(
          input(`invitado${i}_nombre`,it[`invitado${i}_nombre`]),
          input(`invitado${i}_apellidos`,it[`invitado${i}_apellidos`])
        )
      }));
      tr.appendChild(Object.assign(document.createElement('td'),{
        appendChild:input(`invitado${i}_cc`,it[`invitado${i}_cc`])
      }));
      tr.appendChild(Object.assign(document.createElement('td'),{
        appendChild:input(`invitado${i}_discapacidad`,it[`invitado${i}_discapacidad`])
      }));
    }

    tr.appendChild(acciones(it.id));
    tbody.appendChild(tr);
  });
}

async function actualizarInvitados(){
  const r=await fetch('contador_invitados.php');
  const j=await r.json();
  document.getElementById('contadorInvitados').textContent=j.total||0;
}

setInterval(actualizarInvitados,2000);
load();
actualizarInvitados();
</script>

</body>
</html>



