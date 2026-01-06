<!doctype html>
<html lang="es">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Registros - Administración</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

<style>
body { font-size: 17px; background:#f8f9fa }
table { font-size: 16px }
.empty-cell { background:#f0f0f0!important; color:#6c757d!important }

.semaforo-0 td { background:#f8f9fa }
.semaforo-1 td { background:#dc3545; color:#fff }
.semaforo-2 td { background:#fd7e14; color:#fff }
.semaforo-3 td { background:#198754; color:#fff }

.arrive-btn{
  width:34px;height:30px;
  padding:0;border-radius:6px;
  font-weight:700;margin-right:4px
}

.table-responsive{ max-height:65vh; overflow:auto }

.group-grid{
  display:grid;
  grid-template-columns: 2fr 1fr;
  gap:6px;
}

.inv-grid{
  display:grid;
  grid-template-columns: 2fr 1fr 1fr 2fr;
  gap:6px;
}

@media(max-width:768px){
  .group-grid{ grid-template-columns:1fr }
  .inv-grid{ grid-template-columns:1fr }
}
</style>
</head>

<body>

<div class="container py-4">

<h4 class="mb-3">Registros guardados</h4>

<div class="mb-2 d-flex gap-2">
  <input id="searchCedula" class="form-control form-control-sm" placeholder="Buscar por nombre o cédula">
  <button id="reload" class="btn btn-sm btn-outline-secondary">Recargar</button>
</div>

<div class="table-responsive">
<table class="table table-bordered table-sm" id="recordsTable">
<thead class="table-dark">
<tr>
<th>ID</th>
<th>Semáforo</th>
<th>Titular</th>
<th>Invitado 1</th>
<th>Invitado 2</th>
<th>Invitado 3</th>
<th>Acciones</th>
</tr>
</thead>
<tbody></tbody>
</table>
</div>

<div class="fw-bold mt-3 text-center">
Invitados que han llegado: <span id="contadorInvitados">0</span>
</div>

</div>

<script>
const api='api.php';
const colors=['#6c757d','#dc3545','#fd7e14','#198754'];

function createInput(name,val){
  const i=document.createElement('input');
  i.className='form-control form-control-sm';
  i.name=name;
  i.value=val??'';
  if(!i.value)i.classList.add('empty-cell');
  i.oninput=()=>i.classList.toggle('empty-cell',!i.value);
  return i;
}

function invitadoBlock(i,item){
  const d=document.createElement('div');
  d.className='inv-grid';

  d.append(
    createInput(`invitado${i}_nombre`,item[`invitado${i}_nombre`]),
    createInput(`invitado${i}_cc`,item[`invitado${i}_cc`]),
    createInput(`invitado${i}_discapacidad`,item[`invitado${i}_discapacidad`]),
    createInput(`invitado${i}_descripcion`,item[`invitado${i}_descripcion`])
  );
  return d;
}

function acciones(id,tr){
  const td=document.createElement('td');

  for(let i=0;i<4;i++){
    const b=document.createElement('button');
    b.className='btn btn-sm arrive-btn';
    b.style.background=colors[i];
    b.style.color='#fff';
    b.textContent=i;
    b.onclick=()=>setArrived(id,i,tr);
    td.appendChild(b);
  }

  td.appendChild(document.createElement('br'));

  const g=document.createElement('button');
  g.className='btn btn-sm btn-primary mt-1 me-1';
  g.textContent='Guardar';
  g.onclick=()=>guardar(id,tr);

  const e=document.createElement('button');
  e.className='btn btn-sm btn-danger mt-1';
  e.textContent='Eliminar';
  e.onclick=()=>eliminar(id);

  td.append(g,e);
  return td;
}

async function setArrived(id,n,tr){
  await fetch(api,{method:'POST',body:new URLSearchParams({action:'update',id,arrived_count:n})});
  tr.className=`semaforo-${n}`;
}

async function guardar(id,tr){
  const data=new URLSearchParams({action:'update',id});
  tr.querySelectorAll('input').forEach(i=>data.append(i.name,i.value));
  await fetch(api,{method:'POST',body:data});
}

async function eliminar(id){
  if(confirm('¿Eliminar registro?')){
    await fetch(api+'?action=delete&id='+id);
    load();
  }
}

async function load(){
  const r=await fetch(api+'?action=list');
  const j=await r.json();
  const tb=document.querySelector('#recordsTable tbody');
  tb.innerHTML='';

  j.records.forEach(item=>{
    const tr=document.createElement('tr');
    tr.className=`semaforo-${item.arrived_count||0}`;

    tr.innerHTML=`<td>${item.id}</td><td></td>`;

    const tit=document.createElement('td');
    const tg=document.createElement('div');
    tg.className='group-grid';
    tg.append(
      createInput('titular_nombre',item.titular_nombre+' '+item.titular_apellidos),
      createInput('titular_cc',item.titular_cc)
    );
    tit.appendChild(tg);
    tr.appendChild(tit);

    for(let i=1;i<=3;i++){
      const td=document.createElement('td');
      td.appendChild(invitadoBlock(i,item));
      tr.appendChild(td);
    }

    tr.appendChild(acciones(item.id,tr));
    tb.appendChild(tr);
  });
}

function normalize(s){return s.toLowerCase().normalize("NFD").replace(/[\u0300-\u036f]/g,"")}

document.getElementById('searchCedula').oninput=e=>{
  const q=normalize(e.target.value);
  document.querySelectorAll('#recordsTable tbody tr').forEach(tr=>{
    tr.style.display=normalize(tr.innerText).includes(q)?'':'none';
  });
};

document.getElementById('reload').onclick=load;
load();
</script>

</body>
</html>



