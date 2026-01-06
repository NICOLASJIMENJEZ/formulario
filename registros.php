<?php
require_once __DIR__ . '/db.php';
?>
<!doctype html>
<html lang="es">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Administración de Registros</title>

<link href="/formulario/assets/css/styles.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

<style>
body{ background:#f8f9fa; font-size:16px }

.panel{
  background:#fff;
  padding:12px;
  border-radius:12px;
  box-shadow:0 2px 8px rgba(0,0,0,.08);
}

.controls{
  display:flex;
  flex-wrap:wrap;
  gap:8px;
  align-items:center;
  margin-bottom:15px;
}

.table thead th{ white-space:nowrap }
.table td input{ min-width:150px }

.semaforo-0{ background:#f8f9fa }
.semaforo-1{ background:#dc3545!important; color:#fff }
.semaforo-2{ background:#fd7e14!important; color:#fff }
.semaforo-3{ background:#198754!important; color:#fff }

.arrive-btn{
  width:30px;
  height:30px;
  padding:0;
  border-radius:6px;
  font-weight:bold;
}

.contador-box{
  margin-top:20px;
  padding:12px;
  background:#fff;
  border-radius:10px;
  text-align:center;
  font-weight:bold;
  box-shadow:0 1px 4px rgba(0,0,0,.1);
}

#cardsContainer{ display:none }
@media(max-width:768px){
  #tableContainer{ display:none }
  #cardsContainer{ display:block }
}
</style>
</head>

<body>

<div class="container py-3 wrap">

  <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <h4 class="m-0">Administración de Registros</h4>
    <a href="index.php" class="btn btn-danger">Salir</a>
  </div>

  <!-- CONTROLES -->
  <div class="controls">
    <input id="searchCedula" class="form-control form-control-sm" placeholder="Buscar por nombre o cédula">
    <button id="reload" class="btn btn-sm btn-outline-secondary">Recargar</button>
  </div>

  <div id="alert"></div>

  <div class="panel">

    <!-- TABLA -->
    <div id="tableContainer" class="table-responsive">
      <table class="table table-sm table-bordered align-middle text-center" id="recordsTable">
        <thead class="table-dark">
          <tr>
            <th>ID</th>
            <th>Semáforo</th>
            <th>Nombre</th>
            <th>Apellidos</th>
            <th>Cédula</th>
            <th>Celular</th>
            <th>Correo</th>
            <th>Hora</th>
            <th>Programa</th>
            <th>Invitados</th>
            <th>Acciones</th>
          </tr>
        </thead>
        <tbody></tbody>
      </table>
    </div>

    <!-- CARDS (MÓVIL) -->
    <div id="cardsContainer"></div>

  </div>

  <div class="contador-box">
    Invitados que han llegado: <span id="contadorInvitados">0</span>
  </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<script>
const api = 'api.php';
let recordsCache = [];

function showAlert(msg,type='success'){
  alert.innerHTML=`<div class="alert alert-${type} alert-dismissible">
  ${msg}<button class="btn-close" data-bs-dismiss="alert"></button></div>`;
}

async function fetchRecords(){
  const r = await fetch(api+'?action=list');
  const j = await r.json();
  return j.success ? j.records : [];
}

function semaforoBtns(id){
  const colors=['secondary','danger','warning','success'];
  return colors.map((c,i)=>
    `<button class="btn btn-${c} btn-sm arrive-btn me-1"
      onclick="setArrived(${id},${i})">${i}</button>`).join('');
}

async function setArrived(id,n){
  const d=new URLSearchParams({action:'update',id,arrived_count:n});
  const r=await fetch(api,{method:'POST',body:d});
  const j=await r.json();
  if(j.success){ load(); actualizarInvitados(); }
}

async function load(){
  const tbody=document.querySelector('#recordsTable tbody');
  const cards=document.getElementById('cardsContainer');
  tbody.innerHTML=''; cards.innerHTML='';
  recordsCache=await fetchRecords();

  recordsCache.forEach(it=>{
    const tr=document.createElement('tr');
    tr.className=`semaforo-${it.arrived_count||0}`;
    tr.innerHTML=`
      <td>${it.id}</td>
      <td>${semaforoBtns(it.id)}</td>
      <td>${it.titular_nombre||''}</td>
      <td>${it.titular_apellidos||''}</td>
      <td>${it.titular_cc||''}</td>
      <td>${it.titular_celular||''}</td>
      <td>${it.titular_correo||''}</td>
      <td>${it.hora||''}</td>
      <td>${it.programa||''}</td>
      <td>${[it.invitado1_nombre,it.invitado2_nombre,it.invitado3_nombre].filter(Boolean).length}</td>
      <td>
        <button class="btn btn-primary btn-sm" onclick="guardar(${it.id})">Guardar</button>
        <button class="btn btn-danger btn-sm" onclick="eliminar(${it.id})">Eliminar</button>
      </td>`;
    tbody.appendChild(tr);

    cards.innerHTML+=`
      <div class="card p-2 mb-2">
        <h6>${it.titular_nombre} ${it.titular_apellidos}</h6>
        <p><strong>Cédula:</strong> ${it.titular_cc}</p>
        <p><strong>Programa:</strong> ${it.programa}</p>
        ${semaforoBtns(it.id)}
      </div>`;
  });
}

async function eliminar(id){
  if(!confirm('¿Eliminar registro?'))return;
  const r=await fetch(api+'?action=delete&id='+id);
  const j=await r.json();
  if(j.success){ showAlert('Eliminado'); load(); }
}

async function actualizarInvitados(){
  const r=await fetch('contador_invitados.php');
  const j=await r.json();
  contadorInvitados.textContent=j.total||0;
}

document.getElementById('reload').onclick=load;
document.getElementById('searchCedula').addEventListener('input',e=>{
  const q=e.target.value.toLowerCase();
  document.querySelectorAll('#recordsTable tbody tr').forEach(tr=>{
    tr.style.display=tr.innerText.toLowerCase().includes(q)?'':'none';
  });
});

load();
actualizarInvitados();
setInterval(actualizarInvitados,2000);
</script>

</body>
</html>



