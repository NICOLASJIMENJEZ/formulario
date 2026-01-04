<?php
// Simple chooser con tres opciones: Formulario (public), Registros (protected), Registros por Programa (protected)
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Elegir acción</title>

    <!-- Bootstrap / Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">

    <style>
        :root{
            --cesmag-blue:#0b3c6f;
            --cesmag-light:#e9f1fb;
            --cesmag-accent:#1c6fcf;
            --text-dark:#1f2937;
            --muted:#6b7280;
        }

        body{
            font-family:Inter,system-ui,-apple-system,Segoe UI,Roboto,Arial;
            background: linear-gradient(180deg, #e6f0ff 0%, #f4f8ff 100%);
            color:var(--text-dark);
        }

        .wrap{
            position:relative;
            z-index:1;
        }

        /* Logo central fondo */
        .escudo-fondo{
            position:fixed;
            inset:0;
            display:flex;
            align-items:center;
            justify-content:center;
            background-image:url('/formulario/imagenes/logo.png');
            background-repeat:no-repeat;
            background-position:center;
            background-size:380px;
            opacity:0.12;
            pointer-events:none;
            z-index:0;
        }

        h3{
            color:var(--cesmag-blue);
            font-weight:700;
            margin-bottom:40px;
        }

        /* GRID DE TARJETAS */
        .cards-grid{
            display:grid;
            grid-template-columns:repeat(auto-fit,minmax(260px,1fr));
            gap:24px;
        }

        .card-choice{
            background:#fff;
            border-radius:16px;
            border:0;
            text-align:center;
            box-shadow:0 8px 22px rgba(11,60,111,0.12);
            cursor:pointer;
            transition:all .2s ease;
            height:100%;
        }

        .card-choice:hover{
            transform:translateY(-6px);
            box-shadow:0 16px 36px rgba(11,60,111,0.18);
        }

        .card-choice .card-body{
            padding:28px 24px;
        }

        .card-ico{
            font-size:34px;
            color:var(--cesmag-accent);
            margin-bottom:14px;
        }

        .card-choice h5{
            font-weight:700;
            color:var(--cesmag-blue);
            margin-bottom:10px;
        }

        .card-choice p{
            color:var(--muted);
            font-size:14px;
            margin:0;
        }

        @media(max-width:768px){
            .escudo-fondo{
                background-size:240px;
            }
        }
    </style>
</head>

<body>

<div class="escudo-fondo"></div>

<div class="container py-5 wrap">

    <h3 class="text-center">Selecciona una opción</h3>

    <div class="cards-grid">

        <!-- Formulario graduados -->
        <div class="card card-choice" onclick="location.href='index.php'">
            <div class="card-body">
                <div class="card-ico"><i class="fa-solid fa-pen-nib"></i></div>
                <h5>Formulario graduados</h5>
                <p>Inscripción oficial de graduados <br>11 de diciembre 2026</p>
            </div>
        </div>

        <!-- Invitados especiales -->
        <div class="card card-choice" onclick="location.href='invitados_especiales.php'">
            <div class="card-body">
                <div class="card-ico"><i class="fa-solid fa-user-star"></i></div>
                <h5>Invitados especiales</h5>
                <p>Registro exclusivo para invitados especiales</p>
            </div>
        </div>

        <!-- Registros -->
        <div class="card card-choice" onclick="location.href='registros.php'">
            <div class="card-body">
                <div class="card-ico"><i class="fa-solid fa-list-check"></i></div>
                <h5>Consulta de inscripciones</h5>
                <p>Visualiza todos los registros del sistema</p>
            </div>
        </div>

        <!-- Dashboard general -->
        <div class="card card-choice" onclick="location.href='graduados_contador.php'">
            <div class="card-body">
                <div class="card-ico"><i class="fa-solid fa-chart-column"></i></div>
                <h5>Dashboard general</h5>
                <p>Conteo total de graduados e invitados</p>
            </div>
        </div>

        <!-- Registros por programa -->
        <div class="card card-choice" onclick="location.href='por_programa.php'">
            <div class="card-body">
                <div class="card-ico"><i class="fa-solid fa-filter"></i></div>
                <h5>Registros por programa</h5>
                <p>Filtrar y consultar por programa académico</p>
            </div>
        </div>

    </div>
</div>

<script>
    async function clearAuthIfAny(){
        try{
            await fetch('clear_auth.php', {method:'POST'});
        }catch(e){}
    }
    window.addEventListener('pageshow', clearAuthIfAny);
    window.addEventListener('popstate', clearAuthIfAny);
    document.addEventListener('visibilitychange', function(){
        if(document.visibilityState==='visible') clearAuthIfAny();
    });
</script>

</body>
</html>

