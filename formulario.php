<?php
// Simple chooser con tres opciones: Formulario (public), Registros (protected), Registros por Programa (protected)
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Elegir acción</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>
        :root{ --accent:#0d6efd; --card-bg:#ffffff; --muted:#6c757d }
        body{font-family:Inter,system-ui,-apple-system,Segoe UI,Roboto,"Helvetica Neue",Arial; background: linear-gradient(180deg,#f8f9fa 0%, #eef3ff 100%)}
        .wrap{ position:relative; padding-top:40px; }
        .escudo-center{ position:absolute; left:50%; top:48%; transform:translate(-50%,-50%); width:420px; height:420px; background-repeat:no-repeat; background-position:center; background-size:contain; border-radius:50%; opacity:0.12; pointer-events:none; z-index:0; mix-blend-mode:screen; box-shadow: inset 0 -18px 60px rgba(255,255,255,0.06), inset 0 10px 30px rgba(0,0,0,0.02); }
        .escudo-corner{ position:absolute; right:18px; top:12px; width:140px; height:140px; background-repeat:no-repeat; background-position:center; background-size:contain; opacity:1; pointer-events:none; filter:none; }
        .escudo-corner .fallback{ display:flex; align-items:center; justify-content:center; width:100%; height:100%; color:var(--muted); font-weight:700; font-size:12px }
        .choice{ display:flex; gap:18px; flex-wrap:wrap; justify-content:center; align-items:flex-start }
        .card-choice{ width:280px; text-align:center; cursor:pointer; border:0; border-radius:12px; box-shadow:0 6px 18px rgba(15,30,60,0.08); background:var(--card-bg); transition:transform .16s ease, box-shadow .16s ease; }
        .card-choice:hover{ transform:translateY(-6px); box-shadow:0 12px 26px rgba(15,30,60,0.12) }
        .card-choice .card-body{ padding:22px }
        .card-choice h5{ margin-bottom:8px; font-weight:700 }
        .card-choice p{ color:var(--muted); margin-bottom:0 }
        .card-ico{ font-size:28px; color:var(--accent); margin-bottom:10px }
        @media(max-width:720px){ .card-choice{ width:100% } .escudo-center{ width:260px; height:260px } .escudo-corner{ right:10px; top:6px; width:110px; height:110px } }
    </style>
</head>
<body>
    <div class="container py-5 wrap">
        <div class="escudo-center" aria-hidden="true"
            style="background-image: radial-gradient(circle at 35% 30%, rgba(255,255,255,0.85), rgba(255,255,255,0.35) 25%, rgba(255,255,255,0) 52%), url('escudo.png'); background-size: contain, contain; background-position: center, center; background-repeat: no-repeat, no-repeat; background-blend-mode: normal;">
        </div>

        <div class="escudo-corner" aria-hidden="true" style="background-image:url('Escudo.png')">
            <div class="fallback">ESCUDO</div>
            
        </div>
        <style>
          .escudo-corner{
        position: fixed;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%); /* ✅ Centra horizontal y verticalmente */
  width: 380px; /* ✅ Tamaño grande, puedes ajustar */
  height: 380px;
  background-image: url('/formulario/imagenes/logo.png'); /* ✅ Ruta de tu logo */
  background-repeat: no-repeat;
  background-position: center;
  background-size: contain; /* ✅ Ajusta proporciones del logo */
  opacity: 0.15; /* ✅ Semitransparente para no tapar el contenido */
  z-index: 0; /* ✅ Detrás del contenido */
  pointer-events: none; /* ✅ No interfiere con clics */
}
        </style>
        



        <h3 class="text-center mb-4">Selecciona una opción</h3>

        <div class="choice">
            <!-- Formulario -->
            <div class="card card-choice" role="button" onclick="location.href='index.php'">
                <div class="card-body">
                    <div class="card-ico"><i class="fa-solid fa-pen-nib"></i></div>
                    <h5>Formulario</h5>
                    <p>Ir al formulario de inscripción. GRADUADOS 11 de diciembre 2026</p>
                </div>
            </div>

                <div class="card card-choice" role="button" onclick="location.href='invitados_especiales.php'">
                <div class="card-body">
                    <div class="card-ico"><i class="fa-solid fa-pen-nib"></i></div>
                    <h5>Formulario</h5>
                    <p>Inscripción de invitados especiales
                    Formulario exclusivo para el registro de invitados especiales)</p>
                </div>
            </div>

            <!-- Registros -->
            <div class="card card-choice" role="button" onclick="location.href='registros.php'">
                <div class="card-body">
                    <div class="card-ico"><i class="fa-solid fa-list-check"></i></div>
                    <h5>Consulta de inscripciones
                    </h5>
                    <P>Visualiza todos los registros realizados en el sistema, incluyendo asistentes generales e invitados especiales.</P>
                
                </div>
            </div>

            <!-- dashboard -->
            <div class="card card-choice" role="button" onclick="location.href='graduados_contador.php'">
                <div class="card-body">
                    <div class="card-ico"><i class="fa-solid fa-filter"></i></div>
                    <h5>Registros por programa</h5>
                    <p> (DASH BOARD)</p>
                </div>
            </div>
        </div>
    </div>

        <!-- Registros por programa -->
            <div class="card card-choice" role="button" onclick="location.href='por_programa.php'">
                <div class="card-body">
                    <div class="card-ico"><i class="fa-solid fa-filter"></i></div>
                    <h5>Registros por programa</h5>
                    <p>Filtrar registros por programa (DASH BOARD)</p>
                </div>
            </div>
        </div>
    </div>

    <script>
        async function clearAuthIfAny(){
            try{
                await fetch('clear_auth.php', {method:'POST'});
            }catch(e){ /* ignore */ }
        }
        window.addEventListener('pageshow', function(e){ clearAuthIfAny(); });
        window.addEventListener('popstate', function(e){ clearAuthIfAny(); });
        document.addEventListener('visibilitychange', function(){ if(document.visibilityState==='visible') clearAuthIfAny(); });
    </script>
</body>
</html>
