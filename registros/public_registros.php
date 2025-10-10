<?php
// Public listing of registros (no login required). Read-only, responsive cards/table.
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Registros públicos</title>
    <link href="/formulario/assets/css/styles.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body{font-size:16px}
        .card-header{font-weight:700}
        .panel{background:#fff;padding:10px;border-radius:6px}
        @media(min-width:769px){ #cards{display:none} }
        @media(max-width:768px){ #table{display:none} }
    </style>
</head>
<body class="bg-light">
    <div class="container py-3">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h4>Registros</h4>
            <div class="d-flex gap-2">
                <a href="/formulario/registros/login.php" class="btn btn-sm btn-outline-primary">Iniciar sesión</a>
            </div>
        </div>
        <div id="alert"></div>
        <div class="panel shadow-sm">
            <div id="table" class="table-responsive">
                <table class="table table-sm table-bordered" id="pubTable">
                    <thead class="table-dark text-white"><tr><th>ID</th><th>Nombre</th><th>Cédula</th><th>Programa</th><th>Hora</th></tr></thead>
                    <tbody></tbody>
                </table>
            </div>
            <div id="cards"></div>
        </div>
    </div>
    <script>
        const api = '/formulario/registros/api.php';
        async function fetchRecords(){
            const res = await fetch(api + '?action=list');
            const j = await res.json(); if(!j.success) return [];
            return j.records || [];
        }
        function render(records){
            const tb = document.querySelector('#pubTable tbody'); tb.innerHTML='';
            const cards = document.getElementById('cards'); cards.innerHTML='';
            records.forEach(item=>{
                const tr = document.createElement('tr');
                tr.innerHTML = `<td>${item.id}</td><td>${(item.titular_nombre||'') + ' ' + (item.titular_apellidos||'')}</td><td>${item.titular_cc||''}</td><td>${item.programa||''}</td><td>${item.hora||''}</td>`;
                tb.appendChild(tr);
                const card = document.createElement('div'); card.className='card mb-2';
                const head = document.createElement('div'); head.className='card-header'; head.textContent = (item.titular_nombre||'') + ' ' + (item.titular_apellidos||'');
                const body = document.createElement('div'); body.className='card-body'; body.innerHTML = `<p style="margin:0"><strong>Cédula:</strong> ${item.titular_cc||''}</p><p style="margin:0"><strong>Programa:</strong> ${item.programa||''} &nbsp; <strong>Hora:</strong> ${item.hora||''}</p>`;
                card.appendChild(head); card.appendChild(body); cards.appendChild(card);
            });
        }
        (async()=>{ const r = await fetchRecords(); render(r); })();
    </script>
</body>
</html>
