<?php
require_once __DIR__ . '/db.php';

// Generar ID especial automático
$query = $pdo->query("SELECT COUNT(*) AS total FROM registros WHERE es_especial = TRUE");
$count = $query->fetch(PDO::FETCH_ASSOC)['total'] + 1;
$id_especial = "ESP-" . str_pad($count, 4, "0", STR_PAD_LEFT);
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Invitado Especial</title>

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

  <style>
    .wrap{ position:relative; overflow:hidden }
    .escudo-center{
      position:absolute; left:50%; top:50%; transform:translate(-50%,-50%);
      width:420px; height:420px; opacity:0.10; 
      background-image:url('/formulario/registros/escudo.png');
      background-size:contain; background-repeat:no-repeat;
      pointer-events:none; z-index:0;
    }
    .escudo-corner{
      position:absolute; right:18px; top:12px; width:130px; height:130px; 
      background-image:url('imagenes/logo.png');
      background-size:contain; background-repeat:no-repeat;
      pointer-events:none; z-index:1;
    }
    @media(max-width:720px){
      .escudo-center{ width:260px; height:260px }
      .escudo-corner{ width:110px; height:110px; right:10px; top:6px }
    }
    
    .badge-especial{
      background:#b88bff;
      color:#330075;
      padding:6px 14px;
      font-weight:bold;
      border-radius:8px;
      font-size:1rem;
    }
  </style>

</head>
<body class="bg-light">

<div class="container py-4 wrap">

  <div class="escudo-center"></div>
  <div class="escudo-corner"></div>

  <div class="row justify-content-center">
    <div class="col-md-10 col-lg-7">

      <div class="card shadow-sm border-0">
        <div class="card-body">

          <h3 class="mb-3">Registro Invitado Especial</h3>

          <div class="alert alert-info">
            <b>ID Especial asignado:</b>
            <span class="badge-especial"><?= $id_especial ?></span>
          </div>

          <form action="guardar_invitado_especial.php" method="POST">

            <input type="hidden" name="id_especial" value="<?= $id_especial ?>">
            <input type="hidden" name="es_especial" value="1">

            <div class="mb-3">
              <label class="form-label">Nombre *</label>
              <input type="text" name="nombre" class="form-control" required>
            </div>

            <div class="mb-3">
              <label class="form-label">Apellidos *</label>
              <input type="text" name="apellidos" class="form-control" required>
            </div>

            <div class="row mb-3">
              <div class="col-md-6">
                <label class="form-label">Cédula</label>
                <input type="text" name="cc" class="form-control">
              </div>

              <div class="col-md-6">
                <label class="form-label">Hora *</label>
                <select class="form-select" name="hora" required>
                  <option value="">Seleccione...</option>
                  <option value="08:00">08:00 AM</option>
                  <option value="10:30">10:30 AM</option>
                  <option value="14:00">02:00 PM</option>
                  <option value="16:30">04:30 PM</option>
                </select>
              </div>
            </div>

            <div class="mb-3">
              <label class="form-label">Programa / Evento *</label>
              <input type="text" name="programa" class="form-control" required>
            </div>

            <div class="d-grid">
              <button class="btn btn-dark">Guardar Invitado Especial</button>
            </div>

          </form>

          <a href="index.php" class="btn btn-outline-secondary w-100 mt-3">
            Volver al Inicio
          </a>

        </div>
      </div>

    </div>
  </div>

</div>

</body>
</html>

