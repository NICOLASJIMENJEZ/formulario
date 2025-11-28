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
body { font-size: 18px; }
table { font-size: 18px; }
.form-control-sm, .form-select-sm { font-size: 16px; padding: .35rem .5rem; }

/* ============================
    ★ NUEVO: BADGE ESPECIAL
============================ */
.badge-especial {
    background: #d9c7ff;
    color: #3a1670;
    padding: 3px 8px;
    border-radius: 8px;
    font-size: 12px;
    font-weight: bold;
    margin-left: 5px;
    border: 1px solid #b79df3;
}

/* ============================
    ★ NUEVO: COLUMNA ID FIJA
============================ */
.sticky-col {
    position: sticky;
    left: 0;
    background: #fff !important;
    z-index: 10;
    box-shadow: 2px 0 4px rgba(0,0,0,0.15);
}

.empty-cell { background: #f0f0f0 !important; color: #6c757d !important; }
#recordsTable td { background: #fff; color: #000; vertical-align: middle; }

/* Estilos de semáforo */
.semaforo-0 td, .semaforo-0 input { background: #f8f9fa !important; color:#212529!important; }
.semaforo-1 td, .semaforo-1 input { background:#dc3545!important;color:#fff!important; }
.semaforo-2 td, .semaforo-2 input { background:#fd7e14!important;color:#fff!important; }
.semaforo-3 td, .semaforo-3 input { background:#198754!important;color:#fff!important; }

.table-responsive { overflow: auto; max-height: 62vh; padding: 8px; }
#recordsTable thead th, #recordsTable tbody td { white-space: nowrap; }

#recordsTable td:nth-child(1){ min-width: 90px; } /* ID más ancho */
#recordsTable td:nth-child(2){ min-width: 48px; }

.wrap{ position:relative; overflow:hidden }

/* Fondo visual */
.escudo-center{
  position:absolute;
  left:50%; top:50%;
  transform:translate(-50%,-50%);
  width:420px; height:420px;
  opacity:0.12;
  pointer-events:none;
  background-image:url('/formulario/registros/escudo.png');
  background-size:contain;
  background-repeat:no-repeat;
}

.escudo-corner {
  position: fixed; top: 50%; left: 50%;
  transform: translate(-50%, -50%);
  width: 380px; height: 380px;
  background-image: url('imagenes/logo.png');
  opacity: 0.15; pointer-events: none;
}

.gota-fondo {
  position: fixed; bottom: 25px; right: 35px;
  width: 160px; height: 160px;
  background-image:url('/formulario/imagenes/gota.png');
  background-size:contain;
  opacity:0.12;
  pointer-events:none;
}

</style>

</head>
<body class="bg-light">

<div class="container py-4 wrap">

  <div class="escudo-center"></div>
  <div class="escudo-corner"></div>
  <div class="gota-fondo"></div>

  <div class="d-flex justify-content-between align-items-center mb-3">
    <h3>Registros guardados</h3>

    <a href="index.php" class="boton-salida">Salir</a>

    <style>
      .boton-salida {
        position: fixed;
        top: 20px; right: 20px;
        background-color:#dc3545;
        color:white;
        padding:10px 18px;
        border-radius:25px;
        text-decoration:none;
        font-weight:bold;
        box-shadow:0 2px 6px rgba(0,0,0,0.2);
      }
    </style>

    <div class="d-flex gap-2 align-items-center">
      <input id="searchCedula" class="form-control form-control-sm"
      placeholder="Buscar por cédula..." style="min-width:200px;">
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
            <th class="sticky-col">ID</th>
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

<div class="fw-bold mt-3 text-center">
  Invitados que han llegado: <span id="contadorInvitados">0</span>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<script>
const api = 'api.php';
const alertEl = document.getElementById('alert');

function showAlert(msg,type='success'){
  alertEl.innerHTML = `<div class="alert alert-${type} alert-dismissible">${msg}
  <button class="btn-close" data-bs-dismiss="alert"></button></div>`;
}

/* ======================================================
   ★ AQUÍ SE AGREGA EL BADGE DE INVITADO ESPECIAL
====================================================== */
function buildIDCell(item){
  const td = document.createElement('td');
  td.classList.add("sticky-col");

  td.innerHTML = item.id;

  if(item.es_especial == 1){
    td.innerHTML += ` <span class="badge-especial">⭐ ${item.id_especial}</span>`;
  }

  return td;
}

/* ====================================
      Funciones existentes (NO TOCADAS)
===================================== */

async function fetchRecords(){
  try{
    const r=await fetch(api+'?action=list');
    const j=await r.json();
    return j.success? j.records : [];
  }catch{ return []; }
}

function createCellInput(name,val){
  const td=document.createElement('td');
  const i=document.createElement('input');
  i.type="text"; i.className="form-control form-control-sm";
  i.name=name; i.value=val??'';
  if(!i.value) i.classList.add('empty-cell');
  i.addEventListener('input',()=>i.classList.toggle('empty-cell',!i.value));
  td.appendChild(i); return td;
}

function createGuestInput(index,val){
  const td=document.createElement('td');
  const i=document.createElement('input');
  i.type="text"; i.className="form-control form-control-sm";
  i.name=`invitado${index}_nombre`; i.value=val??'';
  if(!i.value) i.classList.add('empty-cell');
  i.addEventListener('input',()=>i.classList.toggle('empty-cell',!i.value));
  td.appendChild(i); return td;
}

function createActionsCell(id){
  const td=document.createElement('td');
  const save=document.createElement('button');
  save.className='btn btn-sm btn-primary me-2'; save.textContent='Guardar';
  const del=document.createElement('button');
  del.className='btn btn-sm btn-danger'; del.textContent='Eliminar';

  // botones de semáforo
  const colors=['#6c757d','#dc3545','#fd7e14','#198754'];
  async function setArrived(n){
    const data=new URLSearchParams({action:'update',id,arrived_count:n});
    const r=await fetch(api,{method:'POST',body:data});
    const j=await r.json();
    if(j.success){
      showAlert('Llegada actualizada');
      const tr=td.closest('tr');
      tr.className=`semaforo-${n}`;
      tr.querySelector('td:nth-child(2) div').style.background=colors[n];
      actualizarInvitados();
    }
  }
  for(let i=0;i<4;i++){
    const b=document.createElement('button');
    b.className='btn btn-sm arrive-btn me-1';
    b.style.background=colors[i];
    b.textContent=i;
    b.addEventListener('click',()=>setArrived(i));
    td.appendChild(b);
  }

  save.addEventListener('click',async()=>{
    const tr=td.closest('tr');
    const inputs=tr.querySelectorAll('input, select');
    const data=new URLSearchParams({action:'update',id});
    inputs.forEach(i=>data.append(i.name,i.value));
    const r=await fetch(api,{method:'POST',body:data});
    const j=await r.json();
    showAlert(j.success?'Registro actualizado':'Error','success');
  });

  del.addEventListener('click',async()=>{
    if(!confirm('¿Eliminar este registro?'))return;
    const r=await fetch(api+'?action=delete&id='+id);
    const j=await r.json();
    if(j.success) load();
  });

  td.appendChild(document.createElement('br'));
  td.appendChild(save);
  td.appendChild(del);
  return td;
}

async function load(){
  const tbody=document.querySelector('#recordsTable tbody');
  tbody.innerHTML='';

  const records=await fetchRecords();
  const colors=['#6c757d','#dc3545','#fd7e14','#198754'];

  for(const item of records){
    const tr=document.createElement('tr');
    tr.classList.add(`semaforo-${item.arrived_count??0}`);

    /* ============================
       ★ USAMOS NUEVA CELDA ID
    ============================= */
    tr.appendChild(buildIDCell(item));

    const semTd=document.createElement('td');
    const circle=document.createElement('div');
    circle.style.width='20px';
    circle.style.height='20px';
    circle.style.borderRadius='50%';
    circle.style.background=colors[item.arrived_count??0];
    semTd.appendChild(circle);
    tr.appendChild(semTd);

    tr.appendChild(createCellInput('titular_nombre',item.titular_nombre));
    tr.appendChild(createCellInput('titular_apellidos',item.titular_apellidos));
    tr.appendChild(createCellInput('titular_cc',item.titular_cc));
    tr.appendChild(createCellInput('titular_celular',item.titular_celular));
    tr.appendChild(createCellInput('titular_correo',item.titular_correo));
    tr.appendChild(createCellInput('hora',item.hora));
    tr.appendChild(createCellInput('programa',item.programa));
    tr.appendChild(createCellInput('discapacidad',item.discapacidad));

    tr.appendChild(createGuestInput(1,item.invitado1_nombre));
    tr.appendChild(createCellInput('invitado1_cc',item.invitado1_cc));
    tr.appendChild(createGuestInput(2,item.invitado2_nombre));
    tr.appendChild(createCellInput('invitado2_cc',item.invitado2_cc));
    tr.appendChild(createGuestInput(3,item.invitado3_nombre));
    tr.appendChild(createCellInput('invitado3_cc',item.invitado3_cc));

    tr.appendChild(createActionsCell(item.id));

    tbody.appendChild(tr);
  }
}

async function actualizarInvitados(){
  try{
    const r=await fetch("contador_invitados.php");
    const d=await r.json();
    document.getElementById("contadorInvitados").textContent=d.total??0;
  }catch{}
}

setInterval(actualizarInvitados,2000);
actualizarInvitados();

document.getElementById('reload').addEventListener('click',load);

document.getElementById('searchCedula').addEventListener('input',e=>{
  const q=e.target.value.toLowerCase();
  document.querySelectorAll('#recordsTable tbody tr').forEach(tr=>{
    const ced = tr.children[4]?.querySelector('input')?.value?.toLowerCase()||'';
    tr.style.display = ced.includes(q) ? '' : 'none';
  });
});

load();
</script>

</body>
</html>


