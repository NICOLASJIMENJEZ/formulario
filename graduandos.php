<?php
// Página de administración de GRADUANDOS sin autenticación.
// Usa la MISMA base de datos y la misma tabla que invitados.
?>
<!doctype html>
<html lang="es">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Registros de Graduandos</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

<style>
body { font-size: 18px; }
table { font-size: 18px; }
.form-control-sm { font-size: 16px; }
.semaforo-0 td { background: #f8f9fa !important; color:#212529!important; }
.semaforo-1 td { background: #198754 !important; color:white!important; }
.arrive-btn { width:45px; height:35px; font-weight:700; border-radius:6px; }
.table-responsive { max-height: 70vh; overflow:auto; }
</style>
</head>

<body class="bg-light">

<div class="container py-4">

  <div class="d-flex justify-content-between align-items-center mb-3">
    <h3>Registros de Graduandos</h3>

    <div class="d-flex gap-2">
      <input id="search" class="form-control form-control-sm" 
             placeholder="Buscar nombre, apellido o cédula..." style="min-width:280px;">
      <button id="reload" class="btn btn-sm btn-outline-secondary">Recargar</button>
    </div>
  </div>

  <div id="alert"></div>

  <div class="shadow-sm bg-white p-2 rounded">
    <div style="font-size:14px;color:#666">Desplaza la tabla ⇄</div>
    <div class="table-responsive">
      <table class="table table-sm table-bordered" id="gradTable">
        <thead class="table-dark">
          <tr>
            <th>ID</th>
            <th>Semáforo</th>
            <th>Nombre</th>
            <th>Apellido</th>
            <th>Cédula</th>
            <th>Programa</th>
            <th>Correo</th>
            <th>Hora</th>
            <th>Acciones</th>
          </tr>
        </thead>
        <tbody></tbody>
      </table>
    </div>
  </div>
</div>

<script>
const api = "api_graduandos.php";
const alertEl = document.getElementById("alert");

function showAlert(msg, type='success'){
  alertEl.innerHTML = 
    `<div class="alert alert-${type} alert-dismissible">
      ${msg}
      <button class="btn-close" data-bs-dismiss="alert"></button>
    </div>`;
}

async function fetchRecords(){
  try{
    const res = await fetch(api + "?action=list");
    const j = await res.json();
    return j.records || [];
  } catch(e){
    showAlert("Error al cargar datos", "danger");
    return [];
  }
}

function createInput(name, value){
  const td = document.createElement("td");
  const i = document.createElement("input");
  i.type = "text";
  i.name = name;
  i.className = "form-control form-control-sm";
  i.value = value ?? "";
  td.appendChild(i);
  return td;
}

function createActions(id){
  const td = document.createElement("td");

  const btnSem = document.createElement("button");
  btnSem.textContent = "Visto";
  btnSem.className = "btn btn-sm btn-success me-2 arrive-btn";
  btnSem.addEventListener("click", () => cambiarSemaforo(id));

  const save = document.createElement("button");
  save.textContent = "Guardar";
  save.className = "btn btn-sm btn-primary me-2";

  const del = document.createElement("button");
  del.textContent = "Eliminar";
  del.className = "btn btn-sm btn-danger";

  save.addEventListener("click", () => guardar(id, td));
  del.addEventListener("click", () => eliminar(id));

  td.append(btnSem, save, del);
  return td;
}

async function cambiarSemaforo(id){
  const data = new URLSearchParams({ action:"semaforo", id, estado:1 });

  const r = await fetch(api, { method:"POST", body:data });
  const j = await r.json();

  if(j.success){
    showAlert("Marcado como llegado");
    load();
  } else showAlert(j.message, "danger");
}

async function guardar(id, td){
  const tr = td.closest("tr");
  const inputs = tr.querySelectorAll("input");

  const data = new URLSearchParams({ action:'update', id });
  inputs.forEach(i => data.append(i.name, i.value));

  const r = await fetch(api, { method:"POST", body:data });
  const j = await r.json();

  if(j.success) showAlert("Guardado correctamente");
  else showAlert(j.message, "danger");
}

async function eliminar(id){
  if(!confirm("¿Eliminar registro?")) return;
  const r = await fetch(api + "?action=delete&id=" + id);
  const j = await r.json();
  if(j.success) load();
}

async function load(){
  const tbody = document.querySelector("#gradTable tbody");
  tbody.innerHTML = "";

  const records = await fetchRecords();
  records.forEach(r => {
    const tr = document.createElement("tr");
    tr.classList.add("semaforo-" + (r.semaforo ?? 0));

    tr.innerHTML = `<td>${r.id}</td>`;

    const s = document.createElement("td");
    const c = document.createElement("div");
    c.style.width="22px";
    c.style.height="22px";
    c.style.borderRadius="50%";
    c.style.background = r.semaforo == 1 ? "#198754" : "#6c757d";
    s.appendChild(c);
    tr.appendChild(s);

    tr.appendChild(createInput("nombre", r.nombre));
    tr.appendChild(createInput("apellido", r.apellido));
    tr.appendChild(createInput("cedula", r.cedula));
    tr.appendChild(createInput("programa", r.programa));
    tr.appendChild(createInput("correo", r.correo));
    tr.appendChild(createInput("hora", r.hora));

    tr.appendChild(createActions(r.id));

    tbody.appendChild(tr);
  });
}

document.getElementById("reload").addEventListener("click", load);

// ✔ Buscador dinámico: nombre, apellido o cédula
document.getElementById("search").addEventListener("input", e=>{
  const q = e.target.value.toLowerCase();
  document.querySelectorAll("#gradTable tbody tr").forEach(tr=>{
    const texto = tr.textContent.toLowerCase();
    tr.style.display = texto.includes(q) ? "" : "none";
  });
});

load();
</script>

</body>
</html>
