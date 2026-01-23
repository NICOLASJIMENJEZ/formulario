<?php
require_once __DIR__ . '/db.php';

// Generar ID especial automático
$query = $pdo->query("SELECT COUNT(*) AS total FROM registros WHERE es_especial = TRUE");
$count = $query->fetch(PDO::FETCH_ASSOC)['total'] + 1;
$id_especial = "ESP-" . str_pad($count, 4, "0", STR_PAD_LEFT);

// Verificar si hay mensaje de éxito o error
$success = isset($_GET['success']) ? true : false;
$error = isset($_GET['error']) ? $_GET['error'] : null;
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

    .hidden{ display:none }
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

          <?php if ($success): ?>
          <div class="alert alert-success alert-dismissible fade show" role="alert">
            <strong>¡Éxito!</strong> El invitado especial ha sido registrado correctamente.
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
          </div>
          <?php endif; ?>

          <?php if ($error): ?>
          <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <strong>Error:</strong> <?= htmlspecialchars($error) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
          </div>
          <?php endif; ?>

          <div class="alert alert-info">
            <b>ID Especial asignado:</b>
            <span class="badge-especial"><?= $id_especial ?></span>
          </div>

          <form action="guardar_invitado_especial.php" method="POST">

            <input type="hidden" name="id_especial" value="<?= $id_especial ?>">
            <input type="hidden" name="es_especial" value="1">

            <!-- TITULAR -->
            <div class="mb-3">
              <label class="form-label">Nombre *</label>
              <input type="text" name="nombre" class="form-control" required>
            </div>

            <div class="mb-3">
              <label class="form-label">Apellidos *</label>
              <input type="text" name="apellidos" class="form-control" required>
            </div>

            <div class="mb-3">
              <label class="form-label">Cédula (Opcional - se usará ID especial si no se ingresa)</label>
              <input type="text" name="cc" class="form-control" placeholder="Dejar vacío para usar ID especial">
            </div>

            <!-- DISCAPACIDAD TITULAR -->
            <div class="mb-3">
              <label class="form-label">¿Tiene discapacidad?</label>
              <select class="form-select" id="discapacidad_titular" name="discapacidad">
                <option value="no">No</option>
                <option value="si">Sí</option>
              </select>
            </div>

            <div class="mb-3 hidden" id="discapacidad_cual_wrap">
              <label class="form-label">¿Cuál?</label>
              <input type="text" class="form-control" name="discapacidad_cual">
            </div>

            <hr>

            <!-- HORA / PROGRAMA -->
            <div class="row mb-3">
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

              <div class="col-md-6">
                <label class="form-label">Programa / Evento *</label>
                <input type="text" name="programa" class="form-control" required placeholder="Ej: Ceremonia Especial">
              </div>
            </div>

            <hr>

            <!-- INVITADOS -->
            <h5 class="mb-3">Invitados del Especial (Opcional)</h5>

            <!-- INVITADO 1 -->
            <div class="row mb-3">
              <div class="col-md-6">
                <input type="text" class="form-control" name="invitado1_nombre" placeholder="Invitado 1 Nombre">
              </div>
              <div class="col-md-6">
                <input type="text" class="form-control" name="invitado1_apellidos" placeholder="Invitado 1 Apellidos">
              </div>
            </div>

            <div class="row mb-3">
              <div class="col-md-6">
                <input type="text" class="form-control" name="invitado1_cc" placeholder="Cédula">
              </div>
              <div class="col-md-6">
                <select class="form-select" name="invitado1_discapacidad">
                  <option value="">¿Discapacidad?</option>
                  <option value="no">No</option>
                  <option value="si">Sí</option>
                </select>
              </div>
            </div>

            <!-- INVITADO 2 -->
            <div class="row mb-3">
              <div class="col-md-6">
                <input type="text" class="form-control" name="invitado2_nombre" placeholder="Invitado 2 Nombre">
              </div>
              <div class="col-md-6">
                <input type="text" class="form-control" name="invitado2_apellidos" placeholder="Invitado 2 Apellidos">
              </div>
            </div>

            <div class="row mb-3">
              <div class="col-md-6">
                <input type="text" class="form-control" name="invitado2_cc" placeholder="Cédula">
              </div>
              <div class="col-md-6">
                <select class="form-select" name="invitado2_discapacidad">
                  <option value="">¿Discapacidad?</option>
                  <option value="no">No</option>
                  <option value="si">Sí</option>
                </select>
              </div>
            </div>

            <!-- INVITADO 3 -->
            <div class="row mb-3">
              <div class="col-md-6">
                <input type="text" class="form-control" name="invitado3_nombre" placeholder="Invitado 3 Nombre">
              </div>
              <div class="col-md-6">
                <input type="text" class="form-control" name="invitado3_apellidos" placeholder="Invitado 3 Apellidos">
              </div>
            </div>

            <div class="row mb-3">
              <div class="col-md-6">
                <input type="text" class="form-control" name="invitado3_cc" placeholder="Cédula">
              </div>
              <div class="col-md-6">
                <select class="form-select" name="invitado3_discapacidad">
                  <option value="">¿Discapacidad?</option>
                  <option value="no">No</option>
                  <option value="si">Sí</option>
                </select>
              </div>
            </div>

            <hr>

            <div class="d-grid">
              <button type="submit" class="btn btn-dark btn-lg">
                <i class="bi bi-save"></i> Guardar Invitado Especial
              </button>
            </div>

          </form>

          <a href="formulario.php" class="btn btn-outline-secondary w-100 mt-3">
            <i class="bi bi-arrow-left"></i> Volver al Menú Principal
          </a>

        </div>
      </div>

    </div>
  </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.getElementById("discapacidad_titular").addEventListener("change", function() {
    if (this.value === "si") {
        document.getElementById("discapacidad_cual_wrap").classList.remove("hidden");
    } else {
        document.getElementById("discapacidad_cual_wrap").classList.add("hidden");
    }
});

// Limpiar URL después de mostrar mensaje
if (window.location.search.includes('success') || window.location.search.includes('error')) {
    setTimeout(() => {
        const url = window.location.href.split('?')[0];
        window.history.replaceState({}, document.title, url);
    }, 3000);
}
</script>

</body>
</html>
