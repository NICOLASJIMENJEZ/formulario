<?php
require_once __DIR__ . '/db.php';

$dbConnected = (isset($pdo) && $pdo instanceof PDO);
$registrationsList = [];

if ($dbConnected) {
    try {
        $stmt = $pdo->query("SELECT * FROM registros ORDER BY id DESC LIMIT 50");
        $registrationsList = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        $dbConnected = false;
    }
}
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Registro de Personas e Invitados</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    /* Water drop and corner image styles */
    .wrap{ position:relative; overflow:hidden }
    .escudo-center{
      position:absolute; left:50%; top:50%; transform:translate(-50%,-50%);
      width:420px; height:420px; background-repeat:no-repeat; background-position:center; background-size:contain;
      border-radius:50%; opacity:0.12; pointer-events:none; z-index:0; mix-blend-mode:screen;
      box-shadow: inset 0 -18px 60px rgba(255,255,255,0.06), inset 0 10px 30px rgba(0,0,0,0.02);
      background-image: radial-gradient(circle at 35% 30%, rgba(255,255,255,0.85), rgba(255,255,255,0.35) 25%, rgba(255,255,255,0) 52%), url('/formulario/registros/escudo.png');
      background-size: contain, contain; background-position: center, center; background-repeat: no-repeat, no-repeat;
    }
    .escudo-corner{
      position:absolute; right:18px; top:12px; width:140px; height:140px; background-repeat:no-repeat; 
      background-position:center; background-size:contain; opacity:1; pointer-events:none; z-index:1;
      background-image:url('imagenes/logo.png');
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
    <div class="row justify-content-center">
      <div class="col-md-10 col-lg-8">
        <div class="card shadow-sm border-0">
          <div class="card-body">

            <!-- Mensaje -->
            <?php if (isset($_GET['msg']) && isset($_GET['type'])): ?>
              <div class="alert alert-<?php echo htmlspecialchars($_GET['type']); ?> alert-dismissible fade show" role="alert">
                <?php echo htmlspecialchars($_GET['msg']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
              </div>
            <?php endif; ?>

            <div class="d-flex justify-content-between align-items-center mb-3">
              <h3 class="mb-0">Formulario de Registro</h3>
              <?php if ($dbConnected): ?>
                <span class="badge bg-success">CONECTADA</span>
              <?php else: ?>
                <span class="badge bg-danger">NO CONECTADA</span>
              <?php endif; ?>
            </div>
            <!-- 🔘 Botón de salida -->



            <form id="regForm" action="register.php" method="post">
              <div class="mb-3">
                <label for="titular_nombre" class="form-label">Nombre del Graduado *</label>
                <input type="text" class="form-control" id="titular_nombre" name="titular_nombre" required>
              </div>

              <div class="mb-3">
                <label for="titular_apellidos" class="form-label">Apellidos del Graduado *</label>
                <input type="text" class="form-control" id="titular_apellidos" name="titular_apellidos" required>
              </div>

              <div class="row mb-3">
                <div class="col-md-4">
                  <label for="titular_cc" class="form-label">Cédula (CC)</label>
                  <input type="text" class="form-control" id="titular_cc" name="titular_cc">
                </div>
                <div class="col-md-4">
                  <label for="titular_celular" class="form-label">Celular</label>
                  <input type="text" class="form-control" id="titular_celular" name="titular_celular">
                </div>
                <div class="col-md-4">
                  <label for="titular_correo" class="form-label">Correo electrónico</label>
                  <input type="email" class="form-control" id="titular_correo" name="titular_correo">
                </div>
                <div class="col-md-4 mt-2">
                  <label for="hora" class="form-label">Hora</label>
                  <select class="form-select" id="hora" name="hora[]" multiple>
                  <option value="09:30">09:30 AM</option>
                  <option value="02:00">02:00 PM</option>
                  <option value="16:30">04:30 PM</option>
                    </select>

                </div>
                <div class="col-12 mt-2" id="programa_wrap" style="display:none;">
                  <label for="programa" class="form-label">Programa</label>
                  <select class="form-select" id="programa" name="programa">
                    <option value="">-- Seleccione un programa --</option>
                  </select>
                </div>
              </div>

              <hr>
              <h5>Invitados (opcionales)</h5>
              <div id="invitadosContainer"></div>
              <div class="d-flex gap-2 mb-3">
                <button type="button" id="addInvitado" class="btn btn-outline-secondary btn-sm">Agregar Invitado</button>
                <button type="button" id="removeInvitado" class="btn btn-outline-danger btn-sm" disabled>Eliminar Invitado</button>
              </div>

              <hr>

              <div class="mb-3 form-check">
                <input type="checkbox" class="form-check-input" id="discapacidad" name="discapacidad" value="si">
                <label class="form-check-label" for="discapacidad">¿Alguna discapacidad?</label>
              </div>

              <div class="mb-3" id="discapacidad_cual_wrap" style="display:none;">
                <label for="discapacidad_cual" class="form-label">¿Cuál?</label>
                <textarea class="form-control" id="discapacidad_cual" name="discapacidad_cual" rows="2"></textarea>
              </div>

              <div class="d-grid">
                <button type="submit" class="btn btn-dark">Enviar Registro</button>
              </div>
              <div id="formAlert" class="mt-3" style="display:none;"></div>
            </form>
          </div>
        </div>

        <!-- Lista de registros oculta por privacidad (graduados registren aquí y no se muestran publicamente) -->

      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <script src="assets/js/form.js"></script>
  <script>
    document.getElementById('discapacidad').addEventListener('change', function(){
      const wrap = document.getElementById('discapacidad_cual_wrap');
      wrap.style.display = this.checked ? 'block' : 'none';
    });
  </script>
</body>
</html>
