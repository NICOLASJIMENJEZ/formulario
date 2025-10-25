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

    .wrap { position: relative; overflow: hidden; }

    .escudo-center {
      position: absolute;
      left: 50%;
      top: 50%;
      transform: translate(-50%, -50%);
      width: 420px;
      height: 420px;
      background-repeat: no-repeat;
      background-position: center;
      background-size: contain;
      border-radius: 50%;
      opacity: 0.12;
      pointer-events: none;
      z-index: 0;
      mix-blend-mode: screen;
      box-shadow: inset 0 -18px 60px rgba(255,255,255,0.06),
                  inset 0 10px 30px rgba(0,0,0,0.02);
      background-image: radial-gradient(circle at 35% 30%, rgba(255,255,255,0.85),
                      rgba(255,255,255,0.35) 25%, rgba(255,255,255,0) 52%),
                      url('/formulario/registros/escudo.png');
      background-size: contain, contain;
      background-position: center, center;
      background-repeat: no-repeat, no-repeat;
    }

    /* ✅ Escudo transparente adaptativo */
    .escudo-corner {
      position: fixed;
      top: 50%;
      left: 50%;
      transform: translate(-50%, -50%);
      width: 35vw;  /* se adapta al ancho de la pantalla */
      height: 35vw; /* proporcional al ancho */
      max-width: 380px;
      max-height: 380px;
      background-image: url('/formulario/imagenes/logo.png');
      background-repeat: no-repeat;
      background-position: center;
      background-size: contain;
      opacity: 0.15;
      z-index: 0;
      pointer-events: none;
    }

    @media(max-width:720px){ 
      .escudo-center { width: 260px; height: 260px; } 
      .escudo-corner {
        width: 50vw;
        height: 50vw;
        max-width: 220px;
        max-height: 220px;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
      }
    }

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
</head>

<body class="bg-light">
  <div class="container py-4 wrap">
    <div class="escudo-center" aria-hidden="true"></div>
    <div class="escudo-corner" aria-hidden="true"></div>

    <div class="d-flex justify-content-between align-items-center mb-3">
      <h3>Registros guardados</h3>
      <a href="index.php" class="boton-salida">Salir</a>

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
  <!-- ⚙️ JavaScript original (sin tocar la lógica) -->
  <script>
  // --- (tu lógica JavaScript original, intacta) ---
  </script>
</body>
</html>
