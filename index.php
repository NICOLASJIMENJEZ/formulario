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
  <title>Inscripción Acompañante</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

  <style>
    .wrap{ position:relative; overflow:hidden }

    .escudo-center{
      position:absolute; left:50%; top:50%; transform:translate(-50%,-50%);
      width:420px; height:420px; opacity:0.12;
      background-repeat:no-repeat; background-size:contain;
      background-image:url('/formulario/registros/escudo.png');
      pointer-events:none; z-index:0;
    }

    .escudo-corner{
      position:absolute; right:18px; top:12px; width:140px; height:140px;
      background-image:url('imagenes/logo.png');
      background-size:contain; background-repeat:no-repeat;
      pointer-events:none; z-index:1;
    }

    @media(max-width:720px){
      .escudo-center{ width:260px; height:260px }
      .escudo-corner{ width:100px; height:100px; top:2px; right:4px; }
      h3{ padding-top: 90px; }
    }

    @media(max-width:480px){
      .escudo-corner{ width:85px; height:85px; top:0; right:2px; }
      h3{ padding-top: 95px; }
    }
  </style>
</head>

<body class="bg-light">
  <div class="container py-4 wrap">

    <div class="escudo-center"></div>
    <div class="escudo-corner"></div>

    <div class="row justify-content-center">
      <div class="col-md-10 col-lg-8">

        <div class="card shadow-sm border-0">
          <div class="card-body">

            <h3 class="mb-3 text-center">
              Inscripción acompañante a ceremonia de grado<br>
              <small class="text-muted">11 de diciembre del 2025</small>
            </h3>

            <div id="consentimientoBox" class="alert alert-secondary p-4">
              <h5 class="mb-3"><b>Consentimiento informado</b></h5>
              <p style="text-align: justify;">
                De acuerdo a la Ley 1581 de 2012 y Decreto 1377 de 2013, autorizo a la Universidad CESMAG para el tratamiento de mis datos.
              </p>

              <div class="alert alert-info mt-4" style="text-align: justify;">
                <b>Información importante para la ceremonia:</b><br><br>
                1. Ceremonia: jueves 11 de diciembre de 2025 en Auditorio San Francisco, Universidad CESMAG.<br>
                2. Cierre de ingreso: 10 minutos antes del inicio.<br>
                3. Máximo 3 acompañantes por graduado.<br>
                4. Prohibido ingreso de menores de edad.
              </div>

              <div class="d-grid mt-4">
                <button id="btnDeAcuerdo" class="btn btn-dark btn-lg">
                  De acuerdo
                </button>
              </div>
            </div>

            <!-- FORMULARIO COMPLETO -->
            <div id="formularioCompleto" style="display:none;">
              <?php if (isset($_GET['msg']) && isset($_GET['type'])): ?>
                <div class="alert alert-<?php echo htmlspecialchars($_GET['type']); ?> alert-dismissible fade show" role="alert">
                  <?php echo htmlspecialchars($_GET['msg']); ?>
                  <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                <?php if ($_GET['type'] === 'success'): ?>
                  <script>
                    // Redirige al index automáticamente 3 segundos después
                    setTimeout(() => { window.location.href = 'index.php'; }, 3000);
                  </script>
                <?php endif; ?>
              <?php endif; ?>

              <div class="d-flex justify-content-between align-items-center mb-3">
                <h4 class="mb-0">Formulario de Inscripción</h4>
                <?php if ($dbConnected): ?>
                  <span class="badge bg-success">CONECTADA</span>
                <?php else: ?>
                  <span class="badge bg-danger">NO CONECTADA</span>
                <?php endif; ?>
              </div>

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
                    <label for="fecha_nacimiento" class="form-label">Fecha de Nacimiento *</label>
                    <input type="date" class="form-control" id="fecha_nacimiento" name="fecha_nacimiento" required>
                  </div>
                  <div class="col-md-4">
                    <label for="fecha_expedicion" class="form-label">Fecha de Expedición CC *</label>
                    <input type="date" class="form-control" id="fecha_expedicion" name="fecha_expedicion" required>
                  </div>
                  <div class="col-md-4">
                    <label for="titular_cc" class="form-label">Cédula (CC)</label>
                    <input type="text" class="form-control" id="titular_cc" name="titular_cc">
                    <small class="text-muted">Solo mayores de 18 años</small>
                  </div>

                  <div class="col-md-4 mt-2">
                    <label for="titular_celular" class="form-label">Celular</label>
                    <input type="text" class="form-control" id="titular_celular" name="titular_celular">
                  </div>

                  <div class="col-md-4 mt-2">
                    <label for="titular_correo" class="form-label">Correo electrónico</label>
                    <input type="email" class="form-control" id="titular_correo" name="titular_correo">
                  </div>

                  <div class="col-md-4 mt-2">
                    <label for="hora" class="form-label">Hora</label>
                    <select class="form-select" id="hora" name="hora">
                      <option value="09:30">09:30 AM</option>
                      <option value="14:00">02:00 PM</option>
                      <option value="16:30">04:30 PM</option>
                    </select>
                  </div>
                </div>

                <hr>
                <h5>Invitados (máx. 3)</h5>
                <div id="invitadosContainer">
                  <?php for($i=1;$i<=3;$i++): ?>
                    <div class="row mb-2 invitadoRow">
                      <div class="col-md-6">
                        <label for="invitado<?php echo $i; ?>_nombre" class="form-label">Nombre Invitado <?php echo $i; ?></label>
                        <input type="text" class="form-control" id="invitado<?php echo $i; ?>_nombre" name="invitado<?php echo $i; ?>_nombre">
                      </div>
                      <div class="col-md-6">
                        <label for="invitado<?php echo $i; ?>_cc" class="form-label">Cédula Invitado <?php echo $i; ?></label>
                        <input type="text" class="form-control invitadoCC" id="invitado<?php echo $i; ?>_cc" name="invitado<?php echo $i; ?>_cc">
                      </div>
                    </div>
                  <?php endfor; ?>
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
              </form>
            </div>

          </div>
        </div>

      </div>
    </div>
  </div>

  <script>
    // Mostrar formulario completo al aceptar
    document.getElementById('btnDeAcuerdo').addEventListener('click', function(){
      document.getElementById('consentimientoBox').style.display = 'none';
      document.getElementById('formularioCompleto').style.display = 'block';
    });

    // Mostrar/ocultar campo discapacidad
    document.getElementById('discapacidad').addEventListener('change', function(){
      const wrap = document.getElementById('discapacidad_cual_wrap');
      wrap.style.display = this.checked ? 'block' : 'none';
    });

    // Validación de edad mayor de 18 años para cédula
    const fechaNacimiento = document.getElementById('fecha_nacimiento');
    const titularCC = document.getElementById('titular_cc');

    fechaNacimiento.addEventListener('change', function(){
      const hoy = new Date();
      const fn = new Date(this.value);
      const edad = hoy.getFullYear() - fn.getFullYear();
      const mes = hoy.getMonth() - fn.getMonth();
      const dia = hoy.getDate() - fn.getDate();
      const edadReal = (mes < 0 || (mes === 0 && dia < 0)) ? edad-1 : edad;
      if(edadReal < 18){
        titularCC.value = '';
        titularCC.disabled = true;
        alert('Solo se puede registrar cédula para mayores de 18 años.');
      } else {
        titularCC.disabled = false;
      }
    });
  </script>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>

