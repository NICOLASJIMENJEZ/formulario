<?php
// Página de administración de registros sin autenticación.
?>
<!doctype html>
<html lang="es">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Registros - Universidad CESMAG</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

<style>
:root{
  --azul:#195AA2;
  --rojo:#E00C38;
  --blanco:#ffffff;
  --gris:#f4f6f9;
  --texto:#1f2937;
}

/* ---------------- BASE ---------------- */
body{
  font-family: system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI";
  background: var(--gris);
  color: var(--texto);
  font-size:16px;
}

h3{
  color: var(--azul);
  font-weight:700;
}

/* ---------------- CONTENEDOR ---------------- */
.wrap{
  position:relative;
  overflow:hidden;
}

/* ---------------- BOTÓN SALIR ---------------- */
.boton-salida{
  position: fixed;
  top: 16px;
  right: 16px;
  background: var(--rojo);
  color: #fff;
  padding: 10px 18px;
  border-radius: 999px;
  text-decoration: none;
  font-weight: 600;
  box-shadow: 0 6px 20px rgba(224,12,56,.35);
  transition: transform .2s ease, box-shadow .2s ease;
  z-index:999;
}
.boton-salida:hover{
  transform: translateY(-2px);
  box-shadow: 0 10px 25px rgba(224,12,56,.45);
}

/* ---------------- TARJETA ---------------- */
.card-admin{
  background:#fff;
  border-radius:18px;
  box-shadow:0 10px 30px rgba(0,0,0,.08);
  padding:14px;
}

/* ---------------- TABLA ---------------- */
.table-responsive{
  max-height:65vh;
  overflow:auto;
}

table{
  font-size:15px;
}

thead{
  background:var(--azul);
}
thead th{
  color:#fff !important;
  font-weight:600;
  white-space:nowrap;
}

tbody td{
  vertical-align:middle;
  background:#fff;
  white-space:nowrap;
}

/* ---------------- INPUTS ---------------- */
.form-control-sm{
  border-radius:8px;
}
.empty-cell{
  background:#f0f0f0 !important;
  color:#6b7280;
}

/* ---------------- SEMÁFORO ---------------- */
.semaforo-0 td{ background:#f9fafb !important; }
.semaforo-1 td{ background:#ffe5ea !important; }
.semaforo-2 td{ background:#fff3e0 !important; }
.semaforo-3 td{ background:#e6f6ef !important; }

.arrive-btn{
  width:34px;
  height:32px;
  border-radius:8px;
  border:none;
  font-weight:700;
}

/* ---------------- BUSCADOR ---------------- */
#searchCedula{
  border-radius:999px;
  padding-left:16px;
}

/* ---------------- CONTADOR ---------------- */
#contadorInvitados{
  color:var(--azul);
  font-size:20px;
}

/* ---------------- FONDOS DECORATIVOS ---------------- */
.escudo-center{
  position:absolute;
  inset:0;
  margin:auto;
  width:380px;
  height:380px;
  background:url('/formulario/registros/escudo.png') no-repeat center/contain;
  opacity:.08;
  pointer-events:none;
}

/* ---------------- RESPONSIVE ---------------- */
@media(max-width:992px){
  table{ font-size:14px; }
}
@media(max-width:768px){
  h3{ font-size:18px; }
  .table-responsive{ max-height:60vh; }
  .boton-salida{
    padding:8px 14px;
    font-size:14px;
  }
}
</style>
</head>

<body>

<div class="container py-4 wrap">

  <div class="escudo-center"></div>

  <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
    <h3>Registros – Universidad CESMAG</h3>

    <div class="d-flex gap-2">
      <input id="searchCedula" class="form-control form-control-sm" placeholder="Buscar nombre o cédula…">
      <button id="reload" class="btn btn-sm btn-outline-primary">Recargar</button>
    </div>
  </div>

  <a href="index.php" class="boton-salida">Salir</a>

  <div id="alert"></div>

  <div class="card-admin">
    <div class="text-muted small mb-2">
      Desliza horizontalmente para ver todas las columnas ⇄
    </div>

    <div class="table-responsive">
      <table class="table table-sm table-bordered align-middle" id="recordsTable">
        <thead>
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
  </div>

  <div class="fw-bold mt-4 text-center">
    Invitados que han llegado:
    <span id="contadorInvitados">0</span>
  </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<script>
/* 🔒 TODA LA LÓGICA ORIGINAL SE MANTIENE INTACTA 🔒 */
/* NO SE TOCÓ API, CONTADOR, SEMÁFORO, FILTRO NI CRUD */

const api = 'api.php';
const alertEl = document.getElementById('alert');

function showAlert(msg, type='success'){
  alertEl.innerHTML = `<div class="alert alert-${type} alert-dismissible">
  ${msg}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>`;
}

/* ---------- TODO TU JS ORIGINAL SIGUE IGUAL ---------- */
/* (No lo repito aquí para no alargar más el mensaje,
   pero debes mantener EXACTAMENTE el mismo JS
   que ya tienes desde fetchRecords() hasta load();) */
</script>

</body>
</html>

