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
    .empty-cell { background: #f0f0f0 !important; color: #6c757d !important; }
    #recordsTable td { background: #fff; color: #000; vertical-align: middle; }
    #recordsTable td input.empty-cell { background: #f0f0f0; color: #6c757d; }
    .semaforo-0 td, .semaforo-0 input, .semaforo-0 select { background: #f8f9fa !important; color: #212529 !important; }
    .semaforo-1 td, .semaforo-1 input, .semaforo-1 select { background: #dc3545 !important; color: #fff !important; }
    .semaforo-2 td, .semaforo-2 input, .semaforo-2 select { background: #fd7e14 !important; color: #fff !important; }
    .semaforo-3 td, .semaforo-3 input, .semaforo-3 select { background: #198754 !important; color: #fff !important; }
    .semaforo-1 td, .semaforo-2 td, .semaforo-3 td { border-color: rgba(255,255,255,0.1); }
    .arrive-btn { width: 38px; height: 32px; padding: 0; border-radius: 6px; font-weight: 700; }
    td > .arrive-btn { margin-right: 8px; }
    .table-responsive { overflow: auto; max-height: 62vh; padding: 8px; }
    #recordsTable thead th, #recordsTable tbody td { white-space: nowrap; }
    #recordsTable input.form-control, #recordsTable select.form-select { min-width: 160px; }
    #recordsTable td:nth-child(1) { min-width: 60px; }
    #recordsTable td:nth-child(2) { min-width: 48px; }
    #recordsTable td:nth-child(20) { min-width: 180px; }
    @media (max-width: 768px) {
      body { font-size: 16px; }
      table { font-size: 16px; }
      #recordsTable input.form-control, #recordsTable select.form-select { min-width: 120px; }
    }
    .wrap{ position:relative; overflow:hidden }
    .escudo-center{
      position:absolute; left:50%; top:50%; transform:translate(-50%,-50%);
      width:420px; height:420px; background-repeat:no-repeat; background-position:center; background-size:contain;
      border-radius:50%; opacity:0.12; pointer-events:none; z-index:0; mix-blend-mode:screen;
      box-shadow: inset 0 -18px 60px rgba(255,255,255,0.06), inset 0 10px 30px rgba(0,0,0,0.02);
      background-image: radial-gradient(circle at 35% 30%, rgba(255,255,255,0.85), rgba(255,255,255,0.35) 25%, rgba(255,255,255,0) 52%), url('/formulario/registros/escudo.png');
      background-size: contain, contain; background-position: center, center; background-repeat: no-repeat, no-repeat;
    }
.escudo-corner {
  position: fixed;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%); /* ✅ Centra horizontal y verticalmente */
  width: 380px; /* ✅ Tamaño grande, puedes ajustar */
  height: 380px;
  background-image: url('/formulario/imagenes/logo.png'); /* ✅ Ruta de tu logo */
  background-repeat: no-repeat;
  background-position: center;
  background-size: contain; /* ✅ Ajusta proporciones del logo */
  opacity: 0.15; /* ✅ Semitransparente para no tapar el contenido */
  z-index: 0; /* ✅ Detrás del contenido */
  pointer-events: none; /* ✅ No interfiere con clics */
}

    
    @media(max-width:720px){ 
      .escudo-center{ width:260px; height:260px } 
      .escudo-corner{ right:10px; top:6px; width:110px; height:110px }
    }
  </style>
</head>
<body class="bg-light">
  <div class="container py-4 wrap">
    <div class="escudo-center" aria-hidden="true"></div>
    <div class="escudo-corner" aria-hidden="true"></div>
    <div class="d-flex justify-content-between align-items-center mb-3">
      <h3>Registros guardados</h3>
      <!-- 🔘 Botón de salida -->
<a href="index.php" class="boton-salida">Salir</a>

<style>
.boton-salida {
    position: fixed;
    top: 20px;
    right: 20px;
    background-color: #dc3545; /* Rojo */
    color: white;
    padding: 10px 18px;
    border-radius: 25px;
    text-decoration: none;
    font-weight: bold;
    box-shadow: 0 2px 6px rgba(0,0,0,0.2);
    transition: background 0.3s ease;
}
.boton-salida:hover {
    background-color: #c82333;
}
</style>

      <div class="d-flex gap-2 align-items-center">
        <input id="searchCedula" class="form-control form-control-sm" placeholder="Buscar por cédula..." style="min-width:200px;">
        <button id="reload" class="btn btn-sm btn-outline-secondary">Recargar</button>
      </div>
    </div>
    <div id="alert"></div>
    <div class="shadow-sm rounded" style="background:#fff;padding:6px;">
      <div style="font-size:14px;color:#6c757d;margin-bottom:6px;">Desplaza horizontalmente dentro de esta ventana para ver todas las columnas ⇄</div>
      <div class="table-responsive">
        <table class="table table-sm table-bordered" id="recordsTable">
          <thead class="table-dark text-white">
            <tr>
              <th style="width:60px">ID</th>
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
              <th>Disc. 1</th>
              <th>Invitado 2</th>
              <th>CC 2</th>
              <th>Disc. 2</th>
              <th>Invitado 3</th>
              <th>CC 3</th>
              <th>Disc. 3</th>
              <th style="width:140px">Acciones</th>
            </tr>
          </thead>
          <tbody></tbody>
        </table>
      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <script>
  const api = '/formulario/api.php';
  const alertEl = document.getElementById('alert');

  function showAlert(msg, type='success'){
    alertEl.innerHTML = `<div class="alert alert-${type} alert-dismissible" role="alert">
      ${msg}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>`;
  }

  async function fetchRecords(){
    try {
      const res = await fetch(api + '?action=list');
      const j = await res.json();
      if(!j.success) {
        showAlert(j.message || 'Error al listar', 'danger');
        return [];
      }
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

    const colors = ['#6c757d','#dc3545','#fd7e14','#198754'];

    async function setArrived(n){
      const data = new URLSearchParams({action:'update', id, arrived_count:n});
      try{
        const r = await fetch(api, {method:'POST', body:data});
        const j = await r.json();
        if(j.success){
          showAlert('Llegada actualizada', 'success');
          const tr = td.closest('tr');
          tr.className = `semaforo-${n}`; // ✅ cambia color sin recargar
          tr.querySelector('td:nth-child(2) div').style.background = colors[n]; // ✅ actualiza círculo
        } else showAlert(j.message || 'Error', 'danger');
      }catch{
        showAlert('Error de red', 'danger');
      }
    }

    // Botones semáforo
    for(let i=0;i<4;i++){
      const btn = document.createElement('button');
      btn.className='btn btn-sm arrive-btn me-1';
      btn.style.background = colors[i];
      btn.style.color = '#fff';
      btn.textContent = i;
      btn.addEventListener('click',()=>setArrived(i));
      td.appendChild(btn);
    }

    save.addEventListener('click', async ()=>{
      const tr = td.closest('tr');
      const inputs = tr.querySelectorAll('input, select');
      const data = new URLSearchParams({action:'update', id});
      inputs.forEach(i=>data.append(i.name,i.value));
      try{
        const r = await fetch(api,{method:'POST',body:data});
        const j = await r.json();
        if(j.success) showAlert('Registro actualizado','success');
        else showAlert(j.message || 'Error','danger');
      }catch{
        showAlert('Error de red','danger');
      }
    });

    del.addEventListener('click', async ()=>{
      if(!confirm('¿Eliminar este registro?')) return;
      try{
        const r = await fetch(api + '?action=delete&id=' + id);
        const j = await r.json();
        if(j.success){
          showAlert('Registro eliminado','success');
          load();
        } else showAlert(j.message || 'Error','danger');
      }catch{
        showAlert('Error de red','danger');
      }
    });

    td.appendChild(document.createElement('br'));
    td.appendChild(save);
    td.appendChild(del);
    return td;
  }

  async function load(){
    const tbody = document.querySelector('#recordsTable tbody');
    tbody.innerHTML = '';
    const records = await fetchRecords();
    const colors = ['#6c757d','#dc3545','#fd7e14','#198754'];

    for(const item of records){
      const tr = document.createElement('tr');
      tr.classList.add(`semaforo-${item.arrived_count ?? 0}`);

      // ID
      tr.appendChild(Object.assign(document.createElement('td'),{textContent:item.id}));

      // Semáforo visual (círculo)
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
      tr.appendChild(createCellInput('invitado1_discapacidad', item.invitado1_discapacidad));
      tr.appendChild(createGuestInput(2, item.invitado2_nombre));
      tr.appendChild(createCellInput('invitado2_cc', item.invitado2_cc));
      tr.appendChild(createCellInput('invitado2_discapacidad', item.invitado2_discapacidad));
      tr.appendChild(createGuestInput(3, item.invitado3_nombre));
      tr.appendChild(createCellInput('invitado3_cc', item.invitado3_cc));
      tr.appendChild(createCellInput('invitado3_discapacidad', item.invitado3_discapacidad));
      tr.appendChild(createActionsCell(item.id));

      tbody.appendChild(tr);
    }
  }

  document.getElementById('reload').addEventListener('click', load);

  document.getElementById('searchCedula').addEventListener('input', e=>{
    const q = e.target.value.toLowerCase();
    document.querySelectorAll('#recordsTable tbody tr').forEach(tr=>{
      const cedula = tr.children[4]?.firstChild?.value?.toLowerCase() || '';
      tr.style.display = cedula.includes(q) ? '' : 'none';
    });
  });

  load();
  </script>
</body>
</html>
