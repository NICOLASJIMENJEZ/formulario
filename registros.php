<?php
// Página de administración de registros (sin autenticación)
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Registros - Administración</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    /* 🔹 Estilo base adaptable */
    body {
      font-size: clamp(14px, 1.8vw, 18px);
      background: #f8f9fa;
      color: #212529;
      overflow-x: hidden;
    }
    h3 {
      font-weight: 600;
      color: #333;
    }
    .table-responsive {
      overflow-x: auto;
      max-height: 65vh;
      border-radius: 12px;
    }
    table {
      width: 100%;
      border-collapse: collapse;
      font-size: clamp(13px, 1.5vw, 16px);
    }
    .table th, .table td {
      white-space: nowrap;
      vertical-align: middle;
    }
    .form-control-sm, .form-select-sm {
      font-size: clamp(13px, 1.5vw, 16px);
    }
    .empty-cell { background: #f0f0f0 !important; color: #6c757d !important; }

    /* 🔹 Colores semáforo */
    .semaforo-0 td, .semaforo-0 input, .semaforo-0 select { background: #f8f9fa !important; color: #212529; }
    .semaforo-1 td, .semaforo-1 input, .semaforo-1 select { background: #dc3545 !important; color: #fff; }
    .semaforo-2 td, .semaforo-2 input, .semaforo-2 select { background: #fd7e14 !important; color: #fff; }
    .semaforo-3 td, .semaforo-3 input, .semaforo-3 select { background: #198754 !important; color: #fff; }

    /* 🔹 Botones */
    .arrive-btn {
      width: 36px;
      height: 32px;
      font-weight: bold;
      color: #fff;
      border: none;
      border-radius: 6px;
      margin-right: 6px;
      cursor: pointer;
    }
    .btn-danger, .btn-primary { font-size: 14px; }

    /* 🔹 Botón de salida */
    .boton-salida {
      position: fixed;
      top: 15px;
      right: 15px;
      background-color: #dc3545;
      color: white;
      padding: 10px 18px;
      border-radius: 25px;
      text-decoration: none;
      font-weight: bold;
      box-shadow: 0 2px 6px rgba(0,0,0,0.2);
      transition: background 0.3s ease;
      z-index: 1000;
    }
    .boton-salida:hover { background-color: #c82333; }

    /* 🔹 Fondo logo */
    .escudo-center {
      position: fixed;
      left: 50%;
      top: 50%;
      transform: translate(-50%, -50%);
      width: 400px;
      height: 400px;
      background-image: url('/formulario/imagenes/logo.png');
      background-repeat: no-repeat;
      background-position: center;
      background-size: contain;
      opacity: 0.08;
      z-index: 0;
      pointer-events: none;
    }

    /* 🔹 Responsive (tabla → tarjetas en móviles) */
    @media (max-width: 768px) {
      .table-responsive { max-height: none; }
      table thead { display: none; }
      table, tbody, tr, td { display: block; width: 100%; }
      tr {
        margin-bottom: 1rem;
        border: 1px solid #ddd;
        border-radius: 10px;
        padding: 10px;
        background: #fff;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
      }
      td {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 8px;
        font-size: 15px;
        border: none;
      }
      td::before {
        content: attr(data-label);
        font-weight: bold;
        color: #333;
      }
      .arrive-btn { width: 30px; height: 28px; margin: 2px; }
    }
  </style>
</head>
<body>
  <div class="escudo-center" aria-hidden="true"></div>
  <a href="index.php" class="boton-salida">Salir</a>

  <div class="container py-4">
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-3">
      <h3>📋 Registros guardados</h3>
      <div class="d-flex gap-2">
        <input id="searchCedula" class="form-control form-control-sm" placeholder="Buscar por cédula...">
        <button id="reload" class="btn btn-sm btn-outline-secondary">Recargar</button>
      </div>
    </div>

    <div id="alert"></div>
    <div class="shadow-sm rounded p-2 bg-white">
      <div class="text-muted small mb-2">Desplaza horizontalmente ⇄ o verticalmente para ver todos los datos</div>
      <div class="table-responsive">
        <table class="table table-sm table-bordered" id="recordsTable">
          <thead class="table-dark text-white">
            <tr>
              <th>ID</th><th>Semáforo</th><th>Titular Nombre</th><th>Titular Apellidos</th><th>Cédula</th>
              <th>Celular</th><th>Correo</th><th>Hora</th><th>Programa</th><th>Discapacidad</th>
              <th>Invitado 1</th><th>CC 1</th><th>Disc. 1</th>
              <th>Invitado 2</th><th>CC 2</th><th>Disc. 2</th>
              <th>Invitado 3</th><th>CC 3</th><th>Disc. 3</th><th>Acciones</th>
            </tr>
          </thead>
          <tbody></tbody>
        </table>
      </div>
    </div>
  </div>

  <script>
  const api = 'api.php';
  const alertEl = document.getElementById('alert');

  function showAlert(msg, type='success'){
    alertEl.innerHTML = `<div class="alert alert-${type} alert-dismissible fade show" role="alert">
      ${msg}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>`;
  }

  async function fetchRecords(){
    try {
      const res = await fetch(api + '?action=list');
      const j = await res.json();
      if(!j.success) throw new Error(j.message || 'Error al listar');
      return j.records || [];
    } catch (e) {
      showAlert(e.message, 'danger');
      return [];
    }
  }

  function createCellInput(label, name, value){
    const td = document.createElement('td');
    td.dataset.label = label;
    const input = document.createElement('input');
    input.type = 'text';
    input.className = 'form-control form-control-sm';
    input.name = name;
    input.value = value ?? '';
    if(!value) input.classList.add('empty-cell');
    input.addEventListener('input', ()=>input.classList.toggle('empty-cell', !input.value));
    td.appendChild(input);
    return td;
  }

  function createActionsCell(id){
    const td = document.createElement('td');
    td.dataset.label = 'Acciones';
    const colors = ['#6c757d','#dc3545','#fd7e14','#198754'];

    // Semáforo
    for(let i=0;i<4;i++){
      const btn=document.createElement('button');
      btn.className='arrive-btn';
      btn.style.background=colors[i];
      btn.textContent=i;
      btn.onclick=()=>updateArrival(id,i,td);
      td.appendChild(btn);
    }

    const save=document.createElement('button');
    save.className='btn btn-sm btn-primary me-2';
    save.textContent='Guardar';
    save.onclick=()=>updateRecord(id,td);

    const del=document.createElement('button');
    del.className='btn btn-sm btn-danger';
    del.textContent='Eliminar';
    del.onclick=()=>deleteRecord(id);

    td.appendChild(document.createElement('br'));
    td.append(save, del);
    return td;
  }

  async function updateArrival(id,n,td){
    const data=new URLSearchParams({action:'update',id,arrived_count:n});
    try{
      const r=await fetch(api,{method:'POST',body:data});
      const j=await r.json();
      if(j.success){
        showAlert('Semáforo actualizado','success');
        const tr=td.closest('tr');
        tr.className=`semaforo-${n}`;
      }else showAlert(j.message,'danger');
    }catch{ showAlert('Error al conectar','danger'); }
  }

  async function updateRecord(id,td){
    const tr=td.closest('tr');
    const inputs=tr.querySelectorAll('input');
    const data=new URLSearchParams({action:'update',id});
    inputs.forEach(i=>data.append(i.name,i.value));
    try{
      const r=await fetch(api,{method:'POST',body:data});
      const j=await r.json();
      if(j.success) showAlert('Registro guardado','success');
      else showAlert(j.message,'danger');
    }catch{ showAlert('Error al conectar','danger'); }
  }

  async function deleteRecord(id){
    if(!confirm('¿Eliminar este registro?')) return;
    try{
      const r=await fetch(`${api}?action=delete&id=${id}`);
      const j=await r.json();
      if(j.success){ showAlert('Registro eliminado','success'); load(); }
      else showAlert(j.message,'danger');
    }catch{ showAlert('Error de conexión','danger'); }
  }

  async function load(){
    const tbody=document.querySelector('#recordsTable tbody');
    tbody.innerHTML='';
    const records=await fetchRecords();
    const colors=['#6c757d','#dc3545','#fd7e14','#198754'];

    for(const item of records){
      const tr=document.createElement('tr');
      tr.classList.add(`semaforo-${item.arrived_count ?? 0}`);
      tr.append(Object.assign(document.createElement('td'),{textContent:item.id, dataset:{label:'ID'}}));
      const semTd=document.createElement('td');
      semTd.dataset.label='Semáforo';
      const circle=document.createElement('div');
      circle.style.width='20px'; circle.style.height='20px';
      circle.style.borderRadius='50%'; circle.style.background=colors[item.arrived_count ?? 0];
      semTd.append(circle); tr.append(semTd);

      const fields=[
        ['Titular Nombre','titular_nombre'],['Titular Apellidos','titular_apellidos'],['Cédula','titular_cc'],
        ['Celular','titular_celular'],['Correo','titular_correo'],['Hora','hora'],['Programa','programa'],
        ['Discapacidad','discapacidad'],['Invitado 1','invitado1_nombre'],['CC 1','invitado1_cc'],
        ['Disc. 1','invitado1_discapacidad'],['Invitado 2','invitado2_nombre'],['CC 2','invitado2_cc'],
        ['Disc. 2','invitado2_discapacidad'],['Invitado 3','invitado3_nombre'],['CC 3','invitado3_cc'],
        ['Disc. 3','invitado3_discapacidad']
      ];
      fields.forEach(([label,name])=>tr.append(createCellInput(label,name,item[name])));
      tr.append(createActionsCell(item.id));
      tbody.append(tr);
    }
  }

  document.getElementById('reload').onclick=load;
  document.getElementById('searchCedula').oninput=e=>{
    const q=e.target.value.toLowerCase();
    document.querySelectorAll('#recordsTable tbody tr').forEach(tr=>{
      const ced=tr.querySelector('[name="titular_cc"]')?.value?.toLowerCase()||'';
      tr.style.display=ced.includes(q)?'':'none';
    });
  };
  load();
  </script>
</body>
</html>


