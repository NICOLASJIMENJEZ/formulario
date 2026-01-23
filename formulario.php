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
    --blue-dark:#1e3a5f;
    --red:#d92027;
    --white:#ffffff;
    --gray:#2d3748;
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
    opacity:0.08;
    pointer-events:none;
    z-index:0;
}

/* CONTENEDOR */
.wrap{
    position:relative;
    z-index:1;
    padding-top: 80px;
    padding-bottom: 80px;
}

h3{
    text-align:center;
    color:var(--white);
    font-weight:300;
    letter-spacing:0.5px;
    margin-bottom:0;
    font-size: 1.8rem;
    opacity: 0;
}

/* GRID */
.cards-grid{
    display:grid;
    grid-template-columns:repeat(5, 1fr);
    gap:20px;
    max-width: 1400px;
    margin: 0 auto;
}

/* TARJETAS */
.card-choice{
    background:var(--white);
    border-radius:20px;
    padding:0;
    text-align:center;
    cursor:pointer;
    border:none;
    box-shadow:0 8px 24px rgba(0,0,0,.2);
    transition:all .3s cubic-bezier(0.4, 0, 0.2, 1);
    opacity:0;
    transform:translateY(40px) scale(0.9);
    overflow:hidden;
    position:relative;
    display: flex;
    flex-direction: column;
}

.card-choice:hover{
    transform:translateY(-8px) scale(1.03);
    box-shadow:0 16px 40px rgba(0,0,0,.3);
    background: var(--red);
}

.card-choice:hover .card-ico-wrapper{
    background: rgba(255,255,255,.2);
}

.card-choice:hover .card-ico{
    color: var(--white);
}

.card-choice:hover h5,
.card-choice:hover p{
    color: var(--white);
}

.card-choice:hover .card-arrow{
    color: var(--white);
}

/* ICONOS CIRCULARES */
.card-ico-wrapper{
    width: 90px;
    height: 90px;
    margin: 35px auto 20px;
    background: #e8edf3;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all .3s ease;
}

.card-ico{
    font-size:38px;
    color:var(--blue-dark);
    transition: all .3s ease;
}

/* CONTENIDO DE TARJETA */
.card-content{
    padding: 0 20px;
    flex: 1;
    display: flex;
    flex-direction: column;
}

.card-choice h5{
    font-weight:600;
    color:var(--red);
    margin-bottom:12px;
    font-size: 1rem;
    line-height: 1.3;
    transition: all .3s ease;
}

.card-choice p{
    font-size:12px;
    color:var(--gray);
    margin:0;
    line-height: 1.5;
    flex: 1;
    transition: all .3s ease;
}

/* FLECHA INFERIOR */
.card-arrow{
    margin: 20px 0;
    font-size: 20px;
    color: var(--blue-dark);
    transition: all .3s ease;
}

.card-choice:hover .card-arrow{
    transform: translateY(3px);
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
    gap: 6px;
    margin-top: 50px;
}

.dot{
    width: 10px;
    height: 10px;
    border-radius: 50%;
    background: rgba(255,255,255,.4);
    transition: all .3s ease;
    cursor: pointer;
}

.dot:hover{
    background: rgba(255,255,255,.7);
}

.dot.active{
    background: var(--red);
    width: 30px;
    border-radius: 5px;
}

/* RESPONSIVE */
@media(max-width:1200px){
    .cards-grid{
        grid-template-columns:repeat(3, 1fr);
    }
}

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
    .card-ico-wrapper{
        width: 70px;
        height: 70px;
        margin: 25px auto 15px;
    }
    .card-ico{
        font-size: 28px;
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

<div class="container-fluid py-5 wrap">

<h3>SELECCIONA UNA OPCIÓN</h3>

<div class="cards-grid">

    <!-- Formulario graduados -->
    <div class="card-choice" onclick="location.href='index.php'">
        <div class="card-ico-wrapper">
            <div class="card-ico"><i class="fa-solid fa-pen-nib"></i></div>
        </div>
        <div class="card-content">
            <h5>Formulario graduados</h5>
            <p>Inscripción oficial de graduados<br>11 de diciembre 2026</p>
        </div>
        <div class="card-arrow"><i class="fa-solid fa-chevron-down"></i></div>
    </div>

    <!-- Invitados -->
    <div class="card-choice" onclick="location.href='invitados_especiales.php'">
        <div class="card-ico-wrapper">
            <div class="card-ico"><i class="fa-solid fa-user-star"></i></div>
        </div>
        <div class="card-content">
            <h5>Invitados especiales</h5>
            <p>Registro exclusivo para invitados especiales</p>
        </div>
        <div class="card-arrow"><i class="fa-solid fa-chevron-down"></i></div>
    </div>

    <!-- Registros -->
    <div class="card-choice" onclick="location.href='registros.php'">
        <div class="card-ico-wrapper">
            <div class="card-ico"><i class="fa-solid fa-list-check"></i></div>
        </div>
        <div class="card-content">
            <h5>Consulta de inscripciones</h5>
            <p>Visualiza todos los registros del sistema</p>
        </div>
        <div class="card-arrow"><i class="fa-solid fa-chevron-down"></i></div>
    </div>

    <!-- Dashboard -->
    <div class="card-choice" onclick="location.href='graduados_contador.php'">
        <div class="card-ico-wrapper">
            <div class="card-ico"><i class="fa-solid fa-chart-column"></i></div>
        </div>
        <div class="card-content">
            <h5>Dashboard general</h5>
            <p>Conteo total de graduados e invitados</p>
        </div>
        <div class="card-arrow"><i class="fa-solid fa-chevron-down"></i></div>
    </div>

    <!-- Por programa -->
    <div class="card-choice" onclick="location.href='por_programa.php'">
        <div class="card-ico-wrapper">
            <div class="card-ico"><i class="fa-solid fa-filter"></i></div>
        </div>
        <div class="card-content">
            <h5>Registros por programa</h5>
            <p>Filtrar y consultar por programa académico</p>
        </div>
        <div class="card-arrow"><i class="fa-solid fa-chevron-down"></i></div>
    </div>

</div>

<!-- Indicadores de navegación -->
<div class="nav-dots">
    <div class="dot"></div>
    <div class="dot active"></div>
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


