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
  <title>Inscripción Ceremonia de Grado 22 mayo 2026</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">

  <style>
    :root {
      --primary: #2c5282;
      --secondary: #d92027;
      --light: #f8f9fa;
      --dark: #1a202c;
    }

    body {
      font-family: 'Inter', sans-serif;
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      min-height: 100vh;
      padding: 20px 0;
    }

    .wrap {
      position: relative;
      overflow: hidden;
    }

    .escudo-center {
      position: absolute;
      left: 50%;
      top: 50%;
      transform: translate(-50%, -50%);
      width: 500px;
      height: 500px;
      opacity: 0.05;
      background-repeat: no-repeat;
      background-size: contain;
      background-image: url('/formulario/registros/escudo.png');
      pointer-events: none;
      z-index: 0;
    }

    .escudo-corner {
      position: absolute;
      right: 20px;
      top: 20px;
      width: 100px;
      height: 100px;
      background-image: url('imagenes/logo.png');
      background-size: contain;
      background-repeat: no-repeat;
      pointer-events: none;
      z-index: 1;
    }

    .card {
      border: none;
      border-radius: 20px;
      box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
      backdrop-filter: blur(10px);
      background: rgba(255, 255, 255, 0.95);
    }

    .card-body {
      padding: 2.5rem;
    }

    h3 {
      color: var(--primary);
      font-weight: 700;
      margin-bottom: 1rem;
    }

    h3 small {
      color: var(--secondary);
      font-weight: 400;
      font-size: 0.9rem;
    }

    h5 {
      color: var(--primary);
      font-weight: 600;
      margin-top: 1.5rem;
      margin-bottom: 1rem;
      border-left: 4px solid var(--secondary);
      padding-left: 15px;
    }

    .form-label {
      font-weight: 600;
      color: var(--dark);
      margin-bottom: 0.5rem;
      font-size: 0.9rem;
    }

    .form-control,
    .form-select {
      border: 2px solid #e2e8f0;
      border-radius: 10px;
      padding: 0.75rem 1rem;
      transition: all 0.3s ease;
      font-size: 0.95rem;
    }

    .form-control:focus,
    .form-select:focus {
      border-color: var(--primary);
      box-shadow: 0 0 0 3px rgba(44, 82, 130, 0.1);
    }

    .btn {
      border-radius: 10px;
      padding: 0.75rem 2rem;
      font-weight: 600;
      transition: all 0.3s ease;
      border: none;
    }

    .btn-dark {
      background: var(--primary);
      color: white;
    }

    .btn-dark:hover {
      background: #1a365d;
      transform: translateY(-2px);
      box-shadow: 0 10px 20px rgba(44, 82, 130, 0.3);
    }

    .btn-secondary {
      background: var(--secondary);
      color: white;
    }

    .btn-secondary:hover {
      background: #b01920;
      transform: translateY(-2px);
      box-shadow: 0 10px 20px rgba(217, 32, 39, 0.3);
    }

    .alert {
      border-radius: 15px;
      border: none;
      padding: 1.5rem;
    }

    .alert-info {
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      color: white;
    }

    .alert-secondary {
      background: #f8f9fa;
      border: 2px solid #e2e8f0;
    }

    .invitado-card {
      background: #f8f9fa;
      border-radius: 15px;
      padding: 1.5rem;
      margin-bottom: 1rem;
      border-left: 4px solid var(--primary);
      transition: all 0.3s ease;
    }

    .invitado-card:hover {
      box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
      transform: translateX(5px);
    }

    .invitado-number {
      background: var(--primary);
      color: white;
      width: 35px;
      height: 35px;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      font-weight: bold;
      margin-bottom: 10px;
    }

    .badge {
      padding: 0.5rem 1rem;
      border-radius: 20px;
      font-weight: 600;
    }

    hr {
      margin: 2rem 0;
      opacity: 0.1;
    }

    /* Responsive */
    @media (max-width: 768px) {
      body {
        padding: 10px;
      }

      .card-body {
        padding: 1.5rem;
      }

      .escudo-center {
        width: 300px;
        height: 300px;
      }

      .escudo-corner {
        width: 70px;
        height: 70px;
        right: 10px;
        top: 10px;
      }

      h3 {
        font-size: 1.5rem;
        padding-top: 60px;
      }

      .btn {
        padding: 0.65rem 1.5rem;
      }
    }

    @media (max-width: 480px) {
      .escudo-corner {
        width: 60px;
        height: 60px;
      }

      h3 {
        font-size: 1.3rem;
      }
    }

    /* Animaciones */
    @keyframes fadeIn {
      from {
        opacity: 0;
        transform: translateY(20px);
      }
      to {
        opacity: 1;
        transform: translateY(0);
      }
    }

    .animate-in {
      animation: fadeIn 0.5s ease;
    }

    /* Estado de conexión */
    .connection-status {
      position: absolute;
      top: 20px;
      left: 20px;
      z-index: 10;
    }
  </style>
</head>

<body>
  <div class="container py-4 wrap">

    <div class="escudo-center"></div>
    <div class="escudo-corner"></div>

    <!-- Estado de conexión -->
    <div class="connection-status">
      <?php if ($dbConnected): ?>
        <span class="badge bg-success">
          <i class="fas fa-check-circle"></i> Conectado
        </span>
      <?php else: ?>
        <span class="badge bg-danger">
          <i class="fas fa-times-circle"></i> Sin conexión
        </span>
      <?php endif; ?>
    </div>

    <div class="row justify-content-center">
      <div class="col-12 col-lg-10 col-xl-9">

        <div class="card">
          <div class="card-body">

            <!-- TÍTULO -->
            <h3 class="text-center mb-4">
              <i class="fas fa-graduation-cap text-primary"></i><br>
              Inscripción Ceremonia de Grado<br>
              <small> 22 de mayo 2026</small>
            </h3>

            <!-- CONSENTIMIENTO INFORMADO -->
            <div id="consentimientoBox" class="animate-in">
              <div class="alert alert-secondary">
                <h5 class="mb-3">
                  <i class="fas fa-file-contract"></i> Consentimiento Informado
                </h5>

                <p style="text-align: justify;">
                  De acuerdo a la Ley 1581 de 2012 y Decreto 1377 de 2013, autorizo expresamente a la Universidad CESMAG para que mis datos sean utilizados conforme a la reglamentación vigente.
                </p>

                <div class="alert alert-info mt-4">
                  <h6 class="text-white mb-3">
                    <i class="fas fa-info-circle"></i> Información Importante
                  </h6>
                  <p class="mb-2"><strong>1.</strong> Ceremonia: Viernes 22 de mayo en Auditorio San Francisco, Universidad CESMAG.</p>
                  <p class="mb-2"><strong>2.</strong> Cierre de ingreso: 10 minutos antes del inicio.</p>
                  <p class="mb-2"><strong>3.</strong> Máximo 3 acompañantes por graduado.</p>
                  <p class="mb-0"><strong>4.</strong> <span class="text-warning fw-bold">Prohibido ingreso de menores de edad (solo mayores de 18 años).</span></p>
                </div>

                <div class="d-grid mt-4">
                  <button id="btnDeAcuerdo" class="btn btn-secondary btn-lg">
                    <i class="fas fa-check"></i> Estoy de acuerdo
                  </button>
                </div>
              </div>
            </div>

            <!-- FORMULARIO COMPLETO -->
            <div id="formularioCompleto" style="display:none;" class="animate-in">

              <!-- Mensaje dinámico -->
              <div id="mensajeDinamico" style="display:none;"></div>

              <form id="regForm" action="register.php" method="post">
                
                <h5><i class="fas fa-user-graduate"></i> Datos del Graduado</h5>

                <div class="row mb-3">
                  <div class="col-md-6">
                    <label for="titular_nombre" class="form-label">
                      <i class="fas fa-user"></i> Nombre *
                    </label>
                    <input type="text" class="form-control" id="titular_nombre" name="titular_nombre" required>
                  </div>

                  <div class="col-md-6">
                    <label for="titular_apellidos" class="form-label">
                      <i class="fas fa-user"></i> Apellidos *
                    </label>
                    <input type="text" class="form-control" id="titular_apellidos" name="titular_apellidos" required>
                  </div>
                </div>

                <div class="row mb-3">
                  <div class="col-md-4">
                    <label for="titular_cc" class="form-label">
                      <i class="fas fa-id-card"></i> Cédula
                    </label>
                    <input type="text" class="form-control" id="titular_cc" name="titular_cc">
                  </div>

                  <div class="col-md-4">
                    <label for="titular_celular" class="form-label">
                      <i class="fas fa-mobile-alt"></i> Celular
                    </label>
                    <input type="tel" class="form-control" id="titular_celular" name="titular_celular">
                  </div>

                  <div class="col-md-4">
                    <label for="titular_correo" class="form-label">
                      <i class="fas fa-envelope"></i> Correo electrónico
                    </label>
                    <input type="email" class="form-control" id="titular_correo" name="titular_correo">
                  </div>
                </div>

                <div class="row mb-3">
                  <div class="col-md-6">
                    <label for="hora" class="form-label">
                      <i class="fas fa-clock"></i> Hora de ceremonia *
                    </label>
                    <select class="form-select" id="hora" name="hora" required>
                      <option value="">Seleccione una hora</option>
                      <option value="09:00">09:00 AM</option>
                      <option value="14:00">02:00 PM</option>
                      <option value="16:30">05:30 PM</option>
                    </select>
                  </div>

                  <div class="col-md-6">
                    <label for="programa" class="form-label">
                      <i class="fas fa-graduation-cap"></i> Programa *
                    </label>
                    <select class="form-select" id="programa" name="programa" required disabled>
                      <option value="">Primero seleccione una hora</option>
                    </select>
                  </div>
                </div>

                <hr>

                <h5><i class="fas fa-users"></i> Invitados (Máx. 3 - Solo mayores de 18 años)</h5>
                
                <div id="invitadosContainer">
                  <?php for ($i = 1; $i <= 3; $i++): ?>
                    <div class="invitado-card">
                      <div class="invitado-number"><?php echo $i; ?></div>
                      
                      <div class="row mb-2">
                        <div class="col-md-6">
                          <label class="form-label">Nombre completo</label>
                          <input type="text" class="form-control" name="invitado<?php echo $i; ?>_nombre" placeholder="Nombre del invitado">
                        </div>
                        <div class="col-md-6">
                          <label class="form-label">Apellidos</label>
                          <input type="text" class="form-control" name="invitado<?php echo $i; ?>_apellidos" placeholder="Apellidos del invitado">
                        </div>
                      </div>

                      <div class="row">
                        <div class="col-md-4">
                          <label class="form-label">Fecha de nacimiento</label>
                          <input type="date" class="form-control invitado-fecha" name="invitado<?php echo $i; ?>_fecha_nacimiento" data-invitado="<?php echo $i; ?>">
                          <small class="text-muted">Debe ser mayor de 18 años</small>
                          <div class="edad-error-<?php echo $i; ?> text-danger fw-bold mt-1" style="display:none; font-size:0.85rem;">
                            <i class="fas fa-exclamation-circle"></i> SOLO MAYORES DE 18 AÑOS
                          </div>
                        </div>
                        <div class="col-md-4">
                          <label class="form-label">Fecha de expedición CC</label>
                          <input type="date" class="form-control" name="invitado<?php echo $i; ?>_fecha_expedicion">
                        </div>
                        <div class="col-md-4">
                          <label class="form-label">Cédula</label>
                          <input type="text" class="form-control invitado-cc" name="invitado<?php echo $i; ?>_cc" id="invitado<?php echo $i; ?>_cc" placeholder="Solo si es mayor de 18">
                        </div>
                      </div>
                    </div>
                  <?php endfor; ?>
                </div>

                <hr>

                <div class="mb-3 form-check">
                  <input type="checkbox" class="form-check-input" id="discapacidad" name="discapacidad" value="si">
                  <label class="form-check-label" for="discapacidad">
                    <i class="fas fa-wheelchair"></i> ¿Alguna persona tiene alguna discapacidad?
                  </label>
                </div>

                <div class="mb-3" id="discapacidad_cual_wrap" style="display:none;">
                  <label for="discapacidad_cual" class="form-label">Especifique la discapacidad</label>
                  <textarea class="form-control" id="discapacidad_cual" name="discapacidad_cual" rows="2" placeholder="Describa brevemente la discapacidad"></textarea>
                </div>

                <div class="d-grid">
                  <button type="submit" class="btn btn-dark btn-lg" id="btnSubmit">
                    <i class="fas fa-paper-plane"></i> Enviar Registro
                  </button>
                </div>

              </form>
            </div>

          </div>
        </div>

      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    // Programas por hora
    const programasPorHora = {
    '09:00': [
  'Arquitectura',
  'Diseño Gráfico',
  'Especialización en Arquitectura y Urbanismo Bioclimático',
  'Especialización en Derecho Empresarial',
  'Especialización en Gerencia de Proyectos',
  'Especialización en Infancia, Cultura y Desarrollo',
  'Especialización en Pedagogía del Entrenamiento Deportivo'
],

'14:00': [
  'Abogados',
  'Psicología'
],

'16:30': [
  'Administración de Empresas',
  'Contaduría Pública',
  'Ingeniería de Sistemas',
  'Ingeniería Electrónica',
  'Licenciatura en Educación Física',
  'Licenciatura en Educación Infantil',
  'Tecnología en Contabilidad y Finanzas',
  'Tecnología en Gestión Financiera'
]
    };

    // Mostrar formulario al aceptar
    document.getElementById('btnDeAcuerdo').addEventListener('click', function () {
      document.getElementById('consentimientoBox').style.display = 'none';
      document.getElementById('formularioCompleto').style.display = 'block';
    });

    // Cambiar programas según la hora
    document.getElementById('hora').addEventListener('change', function () {
      const programaSelect = document.getElementById('programa');
      const hora = this.value;

      programaSelect.innerHTML = '<option value="">Seleccione un programa</option>';

      if (hora && programasPorHora[hora]) {
        programaSelect.disabled = false;
        programasPorHora[hora].forEach(programa => {
          const option = document.createElement('option');
          option.value = programa;
          option.textContent = programa;
          programaSelect.appendChild(option);
        });
      } else {
        programaSelect.disabled = true;
        programaSelect.innerHTML = '<option value="">Primero seleccione una hora</option>';
      }
    });

    // Mostrar/ocultar campo discapacidad
    document.getElementById('discapacidad').addEventListener('change', function () {
      document.getElementById('discapacidad_cual_wrap').style.display = this.checked ? 'block' : 'none';
    });

    // Validación de edad - Permite menores pero bloquea cédula si es menor de 18
    document.querySelectorAll('.invitado-fecha').forEach(input => {
      input.addEventListener('change', function () {
        const numeroInvitado = this.dataset.invitado;
        const cedulaInput = document.getElementById(`invitado${numeroInvitado}_cc`);
        const errorMsg = document.querySelector(`.edad-error-${numeroInvitado}`);
        
        if (!this.value) return;
        
        const fechaNac = new Date(this.value);
        const hoy = new Date();
        
        let edad = hoy.getFullYear() - fechaNac.getFullYear();
        const mes = hoy.getMonth() - fechaNac.getMonth();
        const dia = hoy.getDate() - fechaNac.getDate();
        
        if (mes < 0 || (mes === 0 && dia < 0)) {
          edad--;
        }

        if (edad < 18) {
          // MENOR DE 18: Permitir registro pero NO cédula
          errorMsg.style.display = 'block';
          this.style.borderColor = '#ffc107';
          
          // Limpiar y bloquear SOLO el campo de cédula
          cedulaInput.value = '';
          cedulaInput.disabled = true;
          cedulaInput.placeholder = 'No aplica - Menor de 18';
          cedulaInput.style.backgroundColor = '#f8f9fa';
          
        } else {
          // MAYOR DE 18: Permitir todo incluida la cédula
          errorMsg.style.display = 'none';
          this.style.borderColor = '#198754';
          cedulaInput.disabled = false;
          cedulaInput.placeholder = 'Número de cédula';
          cedulaInput.style.backgroundColor = '';
        }
      });
    });

    // ENVÍO DEL FORMULARIO POR AJAX
    document.getElementById('regForm').addEventListener('submit', function (e) {
      e.preventDefault();
      
      const hora = document.getElementById('hora').value;
      const programa = document.getElementById('programa').value;

      if (!hora || !programa) {
        mostrarMensaje('⚠️ Por favor seleccione la hora y el programa de ceremonia.', 'warning');
        return false;
      }

      const formData = new FormData(this);
      const submitBtn = document.getElementById('btnSubmit');
      const mensajeDiv = document.getElementById('mensajeDinamico');
      
      // Deshabilitar botón
      submitBtn.disabled = true;
      submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Enviando...';

      fetch('register.php', {
        method: 'POST',
        body: formData
      })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          // ✅ ÉXITO - Mostrar mensaje grande
          mostrarMensaje(
            '<div class="text-center"><i class="fas fa-check-circle fa-3x mb-3"></i><h4>¡REGISTRO EXITOSO!</h4><p>' + data.message + '</p></div>',
            'success'
          );
          
          // Limpiar después de 3 segundos
          setTimeout(() => {
            this.reset();
            document.getElementById('programa').disabled = true;
            document.getElementById('programa').innerHTML = '<option value="">Primero seleccione una hora</option>';
            mensajeDiv.style.display = 'none';
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<i class="fas fa-paper-plane"></i> Enviar Registro';
            
            // Resetear estados
            document.querySelectorAll('.invitado-cc').forEach(input => {
              input.disabled = false;
              input.style.backgroundColor = '';
              input.placeholder = 'Solo si es mayor de 18';
            });
            document.querySelectorAll('.invitado-fecha').forEach(input => {
              input.style.borderColor = '';
            });
            document.querySelectorAll('[class*="edad-error-"]').forEach(error => {
              error.style.display = 'none';
            });
          }, 3000);
          
        } else {
          // ❌ ERROR
          mostrarMensaje(
            '<i class="fas fa-exclamation-triangle"></i> <strong>' + data.message + '</strong>',
            data.type || 'danger'
          );
          submitBtn.disabled = false;
          submitBtn.innerHTML = '<i class="fas fa-paper-plane"></i> Enviar Registro';
        }
      })
      .catch(error => {
        console.error('Error:', error);
        mostrarMensaje(
          '<i class="fas fa-times-circle"></i> <strong>Error de conexión. Por favor, intenta nuevamente.</strong>',
          'danger'
        );
        submitBtn.disabled = false;
        submitBtn.innerHTML = '<i class="fas fa-paper-plane"></i> Enviar Registro';
      });
    });

    // Función para mostrar mensajes
    function mostrarMensaje(mensaje, tipo) {
      const mensajeDiv = document.getElementById('mensajeDinamico');
      mensajeDiv.className = 'alert alert-' + tipo + ' alert-dismissible fade show';
      mensajeDiv.innerHTML = mensaje + '<button type="button" class="btn-close" onclick="this.parentElement.style.display=\'none\'"></button>';
      mensajeDiv.style.display = 'block';
      
      // Scroll suave al mensaje
      mensajeDiv.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    }
  </script>

</body>
</html>
