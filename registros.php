<?php
// Página de administración de registros sin autenticación.
?>
<!doctype html>
<html lang="es">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Registros - Administración</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

<style>
body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; font-size:18px; background:#fff; color:#0d1b2a; }
.table-responsive { overflow-x:auto; max-height:62vh; }
#recordsTable thead th, #recordsTable tbody td { white-space:nowrap; text-align:center; }
.form-control-sm, .form-select-sm { font-size:16px; padding:.35rem .5rem; border-radius:6px; }
.empty-cell { background:#f8f9fa !important; color:#6c757d !important; font-style:italic; }
.arrive-btn { width:38px; height:32px; font-weight:700; border-radius:6px; margin-right:6px; color:#fff; }
.boton-salida { position:fixed; top:20px; right:20px; background:#dc143c; color:#fff; padding:10px 18px; border-radius:25px; font-weight:bold; text-decoration:none; box-shadow:0 2px 6px rgba(0,0,0,.2); transition:0.3s; }
.boton-salida:hover { background:#a3001c; }

@media (max-width:768px) {
  body { font-size:16px; }
  #recordsTable input.form-control, #recordsTable select.form-select { min-width:120px; }
}
</style>
</head>
<body>

<div class="container py-4">

  <div class="d-flex justify-content-between align-items-center mb-3">
    <h3>Registros guardados</h3>
    <a href="index.php" class="boton-salida">Salir</a>
    <div class="d-flex gap-2 align-items-center">
      <input id="searchCedula" class="form-control form-control-sm" placeholder="Buscar por cédula..." style="min-width:200px;">
      <button id="reload" class="btn btn-sm btn-outline-primary">Recargar</button>
    </div>
  </div>

  <div id="alert"></div>

  <div class="shadow-sm rounded p-2" style="background:#fff;">
    <div style="font-size:14px;color:#0d1b2a; margin-bottom:6px;">Desplaza horizontalmente dentro de esta ventana para ver todas las columnas ⇄</div>
    <div class="table-responsive">
      <table class="table table-sm table-bordered" id="recordsTable">
        <thead>
          <tr>
            <th style="display:none;">ID</th>
            <th style="display:none;">Semáforo</th>
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
const colors = ['#ffffff','#004aad','#dc143c','#ff0000']; // Blanco, azul, rojo, rojo más intenso CESMA

function showAlert(msg,type='success'){
  alertEl.innerHTML = `<div class="alert alert-${type} alert-dismissible fade show" role="alert">${msg}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>`;
}

async function fetchRecords(){
  try {
    const res = await fetch(api+'?action=list');
    const j = await res.json();
    if(!j.success) { showAlert(j.message||'Error al listar','danger'); return []; }
    return j.records||[];
  } catch(e){ showAlert('Error al conectar con la API','danger'); return []; }
}

function createCellInput(name,value){
  const td=document.createElement('td');
  const input=document.createElement('input');
  input.type='text'; input.className='form-control form-control-sm';
  input.name=name; input.value=value??'';
  if(!input.value) input.classList.add('empty-cell');
  input.addEventListener('input',()=>input.classList.toggle('empty-cell',!input.value));
  td.appendChild(input);
  return td;
}

function createGuestInput(index,value){
  const td=document.createElement('td');
  const input=document.createElement('input');
  input.type='text'; input.className='form-control form-control-sm';
  input.name=`invitado${index}_nombre`; input.value=value??'';
  if(!input.value) input.classList.add('empty-cell');
  input.addEventListener('input',()=>input.classList.toggle('empty-cell',!input.value));
  td.appendChild(input);
  return td;
}

function createActionsCell(id){
  const td=document.createElement('td');
  const save=document.createElement('button'); save.className='btn btn-sm btn-primary me-2'; save.textContent='Guardar';
  const del=document.createElement('button'); del.className='btn btn-sm btn-danger'; del.textContent='Eliminar';

  async function setArrived(n){
    const data = new URLSearchParams({action:'update', id, arrived_count:n});
    try{
      const r=await fetch(api,{method:'POST',body:data});
      const j=await r.json();
      if(j.success){
        showAlert('Llegada actualizada','success');
        td.closest('tr').style.backgroundColor=colors[n];
        actualizarInvitados();
      } else showAlert(j.message||'Error','danger');
    } catch{ showAlert('Error de red','danger'); }
  }

  for(let i=0;i<4;i++){
    const btn=document.createElement('button');
    btn.className='arrive-btn'; btn.style.background=colors[i]; btn.textContent=i; btn.addEventListener('click',()=>setArrived(i));
    td.appendChild(btn);
  }

  save.addEventListener('click',async()=>{
    const tr=td.closest('tr');
    const inputs=tr.querySelectorAll('input,select');
    const data=new URLSearchParams({action:'update',id});
    inputs.forEach(i=>data.append(i.name,i.value));
    try{ const r=await fetch(api,{method:'POST',body:data}); const j=await r.json(); if(j.success) showAlert('Registro actualizado','success'); else showAlert(j.message||'Error','danger'); } catch{ showAlert('Error de red','danger'); }
  });

  del.addEventListener('click',async()=>{
    if(!confirm('¿Eliminar este registro?')) return;
    try{ const r=await fetch(api+'?action=delete&id='+id); const j=await r.json(); if(j.success){ showAlert('Registro eliminado','success'); load(); } else showAlert(j.message||'Error','danger'); } catch{ showAlert('Error de red','danger'); }
  });

  td.appendChild(document.createElement('br')); td.appendChild(save); td.appendChild(del);
  return td;
}

async function load(){
  const tbody=document.querySelector('#recordsTable tbody'); tbody.innerHTML='';
  const records=await fetchRecords();
  for(const item of records){
    const tr=document.createElement('tr'); tr.style.backgroundColor=colors[item.arrived_count??0];

    const idTd=document.createElement('td'); idTd.textContent=item.id; idTd.style.display='none'; tr.appendChild(idTd);
    const semTd=document.createElement('td'); semTd.style.display='none'; tr.appendChild(semTd);

    tr.appendChild(createCellInput('titular_nombre', item.titular_nombre));
    tr.appendChild(createCellInput('titular_apellidos', item.titular_apellidos));
    tr.appendChild(createCellInput('titular_cc', item.titular_cc));
    tr.appendChild(createCellInput('titular_celular', item.titular_celular));
    tr.appendChild(createCellInput('titular_correo', item.titular_correo));
    tr.appendChild(createCellInput('hora', item.hora));
    tr.appendChild(createCellInput('programa', item.programa));
    tr.appendChild(createCellInput('discapacidad', item.discapacidad));
    tr.appendChild(createGuestInput(1,item.invitado1_nombre));
    tr.appendChild(createCellInput('invitado1_cc', item.invitado1_cc));
    tr.appendChild(createGuestInput(2,item.invitado2_nombre));
    tr.appendChild(createCellInput('invitado2_cc', item.invitado2_cc));
    tr.appendChild(createGuestInput(3,item.invitado3_nombre));
    tr.appendChild(createCellInput('invitado3_cc', item.invitado3_cc));

    tr.appendChild(createActionsCell(item.id));
    tbody.appendChild(tr);
  }
}

async function actualizarInvitados(){
  try{ const r=await fetch("contador_invitados.php"); const data=await r.json(); document.getElementById("contadorInvitados").textContent=data.total??0; } catch(e){ console.error(e); }
}
setInterval(actualizarInvitados,2000); actualizarInvitados();

document.getElementById('searchCedula').addEventListener('input', e=>{
  const q=e.target.value.toLowerCase();
  document.querySelectorAll('#recordsTable tbody tr').forEach(tr=>{
    tr.style.display=[2,3,4].some(i=> (tr.children[i].firstChild.value??'').toLowerCase().includes(q))?'':'none';
  });
});

load();
</script>
</body>
</html>

