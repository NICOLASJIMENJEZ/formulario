<?php ?>
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
.empty-cell { background:#f0f0f0 !important; color:#6c757d !important; }

.semaforo-0 td { background:#f8f9fa !important; }
.semaforo-1 td { background:#dc3545 !important; color:#fff !important; }
.semaforo-2 td { background:#fd7e14 !important; color:#fff !important; }
.semaforo-3 td { background:#198754 !important; color:#fff !important; }

.arrive-btn { width:36px; height:32px; padding:0; font-weight:700; }
.table-responsive { max-height:62vh; overflow:auto; }
</style>
</head>

<body class="bg-light">

<div class="container py-4">
<h3>Registros guardados</h3>

<div id="alert"></div>

<div class="table-responsive bg-white shadow rounded p-2">
<table class="table table-sm table-bordered" id="recordsTable">
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

<div class="fw-bold text-center mt-3">
Invitados que han llegado: <span id="contadorInvitados">0</span>
</div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<script>
const api = 'api.php';
const alertEl = document.getElementById('alert');

function showAlert(msg,type='success'){
  alertEl.innerHTML = `<div class="alert alert-${type} alert-dismissible">
  ${msg}<button class="btn-close" data-bs-dismiss="alert"></button></div>`;
}

async function fetchRecords(){
  const r = await fetch(api+'?action=list');
  const j = await r.json();
  return j.success ? j.records : [];
}

function input(name,val){
  const i=document.createElement('input');
  i.className='form-control form-control-sm';
  i.name=name;
  i.value=val??'';
  if(!i.value) i.classList.add('empty-cell');
  i.oninput=()=>i.classList.toggle('empty-cell',!i.value);
  return i;
}

function semaforoCell(n){
  const td=document.createElement('td');
  const c=document.createElement('div');
  c.style.width='18px';
  c.style.height='18px';
  c.style.borderRadius='50%';
  c.style.background=['#6c757d','#dc3545','#fd7e14','#198754'][n||0];
  td.appendChild(c);
  return td;
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
    b.onclick=()=>fetch(api,{method:'POST',
      body:new URLSearchParams({action:'update',id,arrived_count:i})
    }).then(()=>load());
    td.appendChild(b);
  }

  const save=document.createElement('button');
  save.className='btn btn-sm btn-primary ms-1';
  save.textContent='Guardar';
  save.onclick=()=>{
    const tr=td.parentElement;
    const data=new URLSearchParams({action:'update',id});
    tr.querySelectorAll('input').forEach(i=>data.append(i.name,i.value));
    fetch(api,{method:'POST',body:data});
  };

  const del=document.createElement('button');
  del.className='btn btn-sm btn-danger ms-1';
  del.textContent='Eliminar';
  del.onclick=()=>confirm('¿Eliminar?') &&
    fetch(api+'?action=delete&id='+id).then(()=>load());

  td.append(document.createElement('br'),save,del);
  return td;
}

async function load(){
  const tbody=document.querySelector('#recordsTable tbody');
  tbody.innerHTML='';
  const records=await fetchRecords();

  records.forEach(r=>{
    const tr=document.createElement('tr');
    tr.className='semaforo-'+(r.arrived_count||0);

    tr.appendChild(Object.assign(document.createElement('td'),{textContent:r.id}));
    tr.appendChild(semaforoCell(r.arrived_count));

    tr.appendChild(Object.assign(document.createElement('td'),{appendChild:input('titular_nombre',r.titular_nombre)}));
    tr.appendChild(Object.assign(document.createElement('td'),{appendChild:input('titular_apellidos',r.titular_apellidos)}));
    tr.appendChild(Object.assign(document.createElement('td'),{appendChild:input('titular_cc',r.titular_cc)}));
    tr.appendChild(Object.assign(document.createElement('td'),{appendChild:input('titular_celular',r.titular_celular)}));
    tr.appendChild(Object.assign(document.createElement('td'),{appendChild:input('titular_correo',r.titular_correo)}));
    tr.appendChild(Object.assign(document.createElement('td'),{appendChild:input('hora',r.hora)}));
    tr.appendChild(Object.assign(document.createElement('td'),{appendChild:input('programa',r.programa)}));
    tr.appendChild(Object.assign(document.createElement('td'),{appendChild:input('discapacidad',r.discapacidad)}));

    tr.appendChild(Object.assign(document.createElement('td'),{appendChild:input('invitado1_nombre',r.invitado1_nombre)}));
    tr.appendChild(Object.assign(document.createElement('td'),{appendChild:input('invitado1_cc',r.invitado1_cc)}));
    tr.appendChild(Object.assign(document.createElement('td'),{appendChild:input('invitado2_nombre',r.invitado2_nombre)}));
    tr.appendChild(Object.assign(document.createElement('td'),{appendChild:input('invitado2_cc',r.invitado2_cc)}));
    tr.appendChild(Object.assign(document.createElement('td'),{appendChild:input('invitado3_nombre',r.invitado3_nombre)}));
    tr.appendChild(Object.assign(document.createElement('td'),{appendChild:input('invitado3_cc',r.invitado3_cc)}));

    tr.appendChild(acciones(r.id));
    tbody.appendChild(tr);
  });
}

load();
</script>
</body>
</html>


