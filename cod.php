<?php
// Mantenemos la estructura básica pero sin necesidad de consultas a la base de datos
$dbConnected = true; // Solo para mantener el indicador visual si lo deseas
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Formulario Cerrado - Inscripción Ceremonia de Grado</title>
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
      display: flex;
      align-items: center; /* Centrado vertical para el mensaje de cierre */
      padding: 20px 0;
    }

    .card {
      border: none;
      border-radius: 20px;
      box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
      backdrop-filter: blur(10px);
      background: rgba(255, 255, 255, 0.95);
      text-align: center;
    }

    .card-body { padding: 3rem; }

    .icon-closed {
      font-size: 4rem;
      color: var(--secondary);
      margin-bottom: 1.5rem;
    }

    .btn-contact {
      background-color: var(--primary);
      color: white;
      border-radius: 10px;
      padding: 12px 25px;
      text-decoration: none;
      display: inline-block;
      margin-top: 20px;
      transition: 0.3s;
    }

    .btn-contact:hover {
      background-color: #1a365d;
      color: white;
    }
  </style>
</head>

<body>
  <div class="container">
    <div class="row justify-content-center">
      <div class="col-12 col-md-8 col-lg-6">
        
        <div class="card animate-in">
          <div class="card-body">
            <div class="icon-closed">
              <i class="fas fa-calendar-times"></i>
            </div>
            
            <h2 class="fw-bold mb-3" style="color: var(--primary);">Formulario Cerrado</h2>
            
            <p class="lead mb-4">
              El proceso de inscripción para la Ceremonia de Grado ha finalizado.
            </p>
            
            <div class="alert alert-secondary py-4">
              <p class="mb-1">Para más información, por favor comuníquese con:</p>
              <h5 class="fw-bold text-dark">Oficina de Medios Educativos</h5>
              <div class="badge bg-primary fs-6 mt-2">
                <i class="fas fa-phone-alt"></i> Extensión: 1312
              </div>
            </div>

            <a href="https://www.unicesmag.edu.co" class="btn-contact">
              <i class="fas fa-home"></i> Volver al Inicio
            </a>
          </div>
        </div>

      </div>
    </div>
  </div>
</body>
</html>
