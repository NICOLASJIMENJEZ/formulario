<?php
// Simple chooser con tres opciones: Formulario (public), Registros (protected), Registros por Programa (protected)
?>
<!doctype html>
<html lang="es">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Panel de acceso</title>

<!-- Bootstrap / Icons -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">

<style>
:root{
    --blue:#2c5282;
    --blue-dark:#1a365d;
    --blue-light:#e6f0ff;
    --red:#c4161c;
    --white:#ffffff;
    --gray:#6b7280;
    --gray-light:#f7fafc;
}

body{
    font-family:Inter,system-ui,-apple-system,Segoe UI,Roboto,Arial;
    background:linear-gradient(135deg, #2c5282 0%, #4a7ba7 100%);
    min-height:100vh;
    overflow-x:hidden;
    margin:0;
    padding:0;
}

/* LOGO DE FONDO */
.logo-bg{
    position:fixed;
    inset:0;
    background:url('/imagenes/logo.png') no-repeat center;
    background-size:420px;
    opacity:0.05;
    pointer-events:none;
    z-index:0;
}

/* CONTENEDOR */
.wrap{
    position:relative;
    z-index:1;
    padding-top: 60px;
    padding-bottom: 60px;
}

h3{
    text-align:center;
    color:var(--white);
    font-weight:300;
    letter-spacing:0.5px;
    margin-bottom:50px;
    font-size: 1.8rem;
    text-transform: uppercase;
}

/* GRID */
.cards-grid{
    display:grid;
    grid-template-columns:repeat(auto-fit,minmax(220px,1fr));
    gap:24px;
    max-width: 1200px;
    margin: 0 auto;
}

/* TARJETAS */
.card-choice{
    background:var(--white);
    border-radius:16px;
    padding:0;
    text-align:center;
    cursor:pointer;
    border:none;
    box-shadow:0 8px 24px rgba(0,0,0,.15);
    transition:all .3s cubic-bezier(0.4, 0, 0.2, 1);
    opacity:0;
    transform:translateY(30px) scale(0.95);
    overflow:hidden;
    position:relative;
}

.card-choice:hover{
    transform:translateY(-8px) scale(1.02);
    box-shadow:0 16px 40px rgba(0,0,0,.25);
}

.card-choice.red-card:hover{
    background: var(--red);
}

.card-choice.red-card:hover .card-ico,
.card-choice.red-card:hover h5,
.card-choice.red-card:hover p{
    color: var(--white);
}

/* ICONOS CIRCULARES */
.card-ico-wrapper{
    width: 80px;
    height: 80px;
    margin: 30px auto 20px;
    background: var(--gray-light);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all .3s ease;
}

.card-choice:hover .card-ico-wrapper{
    transform: scale(1.1);
}

.red-card .card-ico-wrapper{
    background: rgba(196,22,28,.1);
}

.card-ico{
    font-size:32px;
    color:var(--blue-dark);
    transition: all .3s ease;
}

.red-card .card-ico{
    color: var(--red);
}

/* CONTENIDO DE TARJETA */
.card-content{
    padding: 0 24px 30px;
}

.card-choice h5{
    font-weight:600;
    color:var(--blue-dark);
    margin-bottom:12px;
    font-size: 1rem;
    line-height: 1.3;
}

.card-choice p{
    font-size:13px;
    color:var(--gray);
    margin:0;
    line-height: 1.5;
}

/* FLECHA INFERIOR */
.card-arrow{
    position: absolute;
    bottom: 12px;
    left: 50%;
    transform: translateX(-50%);
    font-size: 18px;
    color: var(--blue);
    opacity: 0.4;
    transition: all .3s ease;
}

.card-choice:hover .card-arrow{
    opacity: 1;
    transform: translateX(-50%) translateY(3px);
}

.red-card .card-arrow{
    color: var(--red);
}

/* EFECTO ESPECIAL TARJETA ROJA */
.red-card{
    background: var(--white);
}

.red-card::before{
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 4px;
    background: var(--red);
    opacity: 0;
    transition: opacity .3s ease;
}

.red-card:hover::before{
    opacity: 1;
}

/* ANIMACIÓN */
.card-choice.show{
    opacity:1;
    transform:translateY(0) scale(1);
}

/* INDICADORES DE NAVEGACIÓN */
.nav-dots{
    display: flex;
    justify-content: center;
    gap: 8px;
    margin-top: 40px;
}

.dot{
    width: 8px;
    height: 8px;
    border-radius: 50%;
    background: rgba(255,255,255,.3);
    transition: all .3s ease;
}

.dot.active{
    background: var(--white);
    width: 24px;
    border-radius: 4px;
}

/* RESPONSIVE */
@media(max-width:768px){
    .logo-bg{ background-size:260px; }
    .cards-grid{
        grid-template-columns: 1fr;
        gap: 20px;
        padding: 0 20px;
    }
    h3{
        font-size: 1.5rem;
        margin-bottom: 35px;
    }
    .wrap{
        padding-top: 40px;
        padding-bottom: 40px;
    }
}

@media(min-width:769px) and (max-width:1024px){
    .cards-grid{
        grid-template-columns:repeat(2,1fr);
    }
}
</style>
</head>

<body>

<div class="logo-bg"></div>

<div class="container py-5 wrap">

<h3>SELECCIONA UNA OPCIÓN</h3>

<div class="cards-grid">

    <!-- Formulario graduados -->
    <div class="card-choice" onclick="location.href='index.php'">
        <div class="card-ico-wrapper">
            <div class="card-ico"><i class="fa-solid fa-house"></i></div>
        </div>
        <div class="card-content">
            <h5>Formulario graduados</h5>
            <p>Explora los espacios experimentales y recursos académicos disponibles</p>
        </div>
        <div class="card-arrow"><i class="fa-solid fa-chevron-down"></i></div>
    </div>

    <!-- Invitados -->
    <div class="card-choice" onclick="location.href='invitados_especiales.php'">
        <div class="card-ico-wrapper">
            <div class="card-ico"><i class="fa-solid fa-magnifying-glass"></i></div>
        </div>
        <div class="card-content">
            <h5>Consultas y Solicitudes</h5>
            <p>Consulta y conoce cómo comunicarte con cada área</p>
        </div>
        <div class="card-arrow"><i class="fa-solid fa-chevron-down"></i></div>
    </div>

    <!-- Registros -->
    <div class="card-choice" onclick="location.href='registros.php'">
        <div class="card-ico-wrapper">
            <div class="card-ico"><i class="fa-solid fa-handshake"></i></div>
        </div>
        <div class="card-content">
            <h5>Coordinación de eventos y logística</h5>
            <p>Consulta las guías y lineamientos para la organización de eventos</p>
        </div>
        <div class="card-arrow"><i class="fa-solid fa-chevron-down"></i></div>
    </div>

    <!-- Dashboard - ROJA -->
    <div class="card-choice red-card" onclick="location.href='graduados_contador.php'">
        <div class="card-ico-wrapper">
            <div class="card-ico"><i class="fa-solid fa-book-open"></i></div>
        </div>
        <div class="card-content">
            <h5>Portafolio de servicios</h5>
            <p>Accede a requisitos, guías y manuales institucionales especializados</p>
        </div>
        <div class="card-arrow"><i class="fa-solid fa-chevron-down"></i></div>
    </div>

    <!-- Por programa -->
    <div class="card-choice" onclick="location.href='por_programa.php'">
        <div class="card-ico-wrapper">
            <div class="card-ico"><i class="fa-solid fa-clock"></i></div>
        </div>
        <div class="card-content">
            <h5>Horario de atención</h5>
            <p>Conoce los horarios de atención en cada campus universitario</p>
        </div>
        <div class="card-arrow"><i class="fa-solid fa-chevron-down"></i></div>
    </div>

</div>

<!-- Indicadores de navegación -->
<div class="nav-dots">
    <div class="dot active"></div>
    <div class="dot"></div>
    <div class="dot"></div>
    <div class="dot"></div>
</div>

</div>

<script>
/* Animación de entrada */
document.addEventListener("DOMContentLoaded", ()=>{
    document.querySelectorAll(".card-choice").forEach((card,i)=>{
        setTimeout(()=>card.classList.add("show"), i*100);
    });
});

/* Seguridad sesión */
async function clearAuthIfAny(){
    try{ await fetch('clear_auth.php',{method:'POST'}); }catch(e){}
}
window.addEventListener('pageshow',clearAuthIfAny);
window.addEventListener('popstate',clearAuthIfAny);
document.addEventListener('visibilitychange',()=>{
    if(document.visibilityState==='visible') clearAuthIfAny();
});
</script>

</body>
</html>


