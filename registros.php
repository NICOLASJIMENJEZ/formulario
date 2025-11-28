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

.wrap{ position:relative; overflow:hidden }

/* 🟣 ESTILO MORADO PARA EL ID ESPECIAL */
.id-especial-cell input {
    background: #7a3cff !important;
    color: #fff !important;
    border: 1px solid #5a28c7 !important;
    font-weight: bold;
}

</style>

</head>
<body class="bg-light">

<div class="container py-4 wrap">

  <div class="d-flex justify-content-between align-items-center mb-3">
    <h3>Registros guardados</h3>
    <a href="index.php" class="boton-salida">Salir</a>

    <style>
      .boton-salida {
        position: fixed;
        top: 20px;
        right: 20px;
        background-color: #dc3545;
        color: white;
        padding: 10px 18px;
        border-radius: 25px;
        text-decoration: none;
        font-weight: bold;
        box-shadow: 0 2px 6px rgba(0,0,0,0.2);
        transition: background 0.3s ease;
      }
      .boton-salida:hover { background-color: #c82333; }
    </style>

    <div class="d-flex gap-2 align-items-center">
      <input id="searchCedula" class="form-control form-control-sm" placeholder="Buscar por cédula..." style="min-width:200px;">
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
            <!-- 🟣 AGREGADO -->
            <th>ID Especial</th>
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
            <th style="width:140px">Acciones</th>
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

<script>
const api = 'api.php';

async function fetchRecords(){
  const res = await fetch(api + '?action=list');
  const j = await res.json();
  return j.records || [];
}

// 🟣 FUNCIÓN PARA PINTAR ID ESPECIAL
function createIdEspecialCell(value){
    const td = document.createElement('td');
    const input = document.createElement('input');
    input.type = 'text';
    input.className = 'form-control form-control-sm';

    input.value = value ?? "";

    if (value) {
        td.classList.add("id-especial-cell");
    }

    td.appendChild(input);
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

    tr.appendChild(Object.assign(document.createElement('td'), {textContent:item.id}));

    // 🟣 AGREGADO: ID ESPECIAL
    tr.appendChild(createIdEspecialCell(item.id_especial));

    const semTd = document.createElement('td');
    const circle = document.createElement('div');
    circle.style.width = '20px';
    circle.style.height = '20px';
    circle.style.borderRadius = '50%';
    circle.style.background = colors[item.arrived_count ?? 0];
    semTd.appendChild(circle);
    tr.appendChild(semTd);

    // resto idéntico…
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

load();
</script>

</body>
</html>


