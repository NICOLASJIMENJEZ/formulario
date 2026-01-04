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
    --blue:#0b3c6f;
    --blue-light:#e6f0ff;
    --red:#c4161c;
    --white:#ffffff;
    --gray:#6b7280;
}

body{
    font-family:Inter,system-ui,-apple-system,Segoe UI,Roboto,Arial;
    background:linear-gradient(180deg,var(--blue-light),#ffffff);
    min-height:100vh;
    overflow-x:hidden;
}

/* LOGO DE FONDO */
.logo-bg{
    position:fixed;
    inset:0;
    background:url('/formulario/imagenes/logo.png') no-repeat center;
    background-size:420px;
    opacity:0.10;
    pointer-events:none;
    z-index:0;
}

/* CONTENEDOR */
.wrap{
    position:relative;
    z-index:1;
}

h3{
    text-align:center;
    color:var(--blue);
    font-weight:800;
    letter-spacing:1px;
    margin-bottom:50px;
}

/* GRID */
.cards-grid{
    display:grid;
    grid-template-columns:repeat(auto-fit,minmax(260px,1fr));
    gap:28px;
}

/* TARJETAS */
.card-choice{
    background:var(--white);
    border-radius:18px;
    padding:28px 22px;
    text-align:center;
    cursor:pointer;
    border:3px solid transparent;
    box-shadow:0 12px 30px rgba(11,60,111,.15);
    transition:all .35s ease;
    opacity:0;
    transform:translateY(30px);
}

.card-choice:hover{
    border-color:var(--red);
    transform:translateY(-10px) scale(1.02);
    box-shadow:0 20px 45px rgba(196,22,28,.25);
}

/* ICONOS */
.card-ico{
    font-size:36px;
    color:var(--red);
    margin-bottom:15px;
}

/* TEXTO */
.card-choice h5{
    font-weight:700;
    color:var(--blue);
    margin-bottom:10px;
}

.card-choice p{
    font-size:14px;
    color:var(--gray);
    margin:0;
}

/* ANIMACIÓN */
.card-choice.show{
    opacity:1;
    transform:translateY(0);
}

/* RESPONSIVE */
@media(max-width:768px){
    .logo-bg{ background-size:260px; }
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
        <div class="card-ico"><i class="fa-solid fa-pen-nib"></i></div>
        <h5>Formulario graduados</h5>
        <p>Inscripción oficial de graduados<br>11 de diciembre 2026</p>
    </div>

    <!-- Invitados -->
    <div class="card-choice" onclick="location.href='invitados_especiales.php'">
        <div class="card-ico"><i class="fa-solid fa-user-star"></i></div>
        <h5>Invitados especiales</h5>
        <p>Registro exclusivo para invitados especiales</p>
    </div>

    <!-- Registros -->
    <div class="card-choice" onclick="location.href='registros.php'">
        <div class="card-ico"><i class="fa-solid fa-list-check"></i></div>
        <h5>Consulta de inscripciones</h5>
        <p>Visualiza todos los registros del sistema</p>
    </div>

    <!-- Dashboard -->
    <div class="card-choice" onclick="location.href='graduados_contador.php'">
        <div class="card-ico"><i class="fa-solid fa-chart-column"></i></div>
        <h5>Dashboard general</h5>
        <p>Conteo total de graduados e invitados</p>
    </div>

    <!-- Por programa -->
    <div class="card-choice" onclick="location.href='por_programa.php'">
        <div class="card-ico"><i class="fa-solid fa-filter"></i></div>
        <h5>Registros por programa</h5>
        <p>Filtrar y consultar por programa académico</p>
    </div>

</div>
</div>

<script>
/* Animación de entrada */
document.addEventListener("DOMContentLoaded", ()=>{
    document.querySelectorAll(".card-choice").forEach((card,i)=>{
        setTimeout(()=>card.classList.add("show"), i*120);
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


