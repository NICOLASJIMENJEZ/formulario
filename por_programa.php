<?php
// Página: Filtrar registros por programa (responsive: tabla en escritorio, tarjetas en móvil)
require_once __DIR__ . '/auth.php';
require_login();
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Registros por Programa</title>
    <link href="/formulario/assets/css/styles.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { font-size: 16px; }
        .panel { background:#fff;padding:10px;border-radius:6px; }
        .controls { display:flex;gap:8px;flex-wrap:wrap;align-items:center;margin-bottom:10px }
        .controls .form-select, .controls .form-control { min-width:180px }
        #cardsContainer { display:none; }

        @media (max-width: 768px) {
            #tableContainer { display:none; }
            #cardsContainer { display:block; }
            .card { margin-bottom:10px }
        }

        .card-header { font-weight:700 }
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
        position: fixed;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        width: 380px;
        height: 380px;
        background-image: url('imagenes/logo.png');
        background-repeat: no-repeat;
        background-position: center;
        background-size: contain;
        opacity: 0.15;
        z-index: 0;
        pointer-events: none;
     }
        @media(max-width:720px){ 
          .escudo-center{ width:260px; height:260px } 
          .escudo-corner{ right:10px; top:6px; width:110px; height:110px }
        }

        .boton-salida {
            position: fixed;
            top: 20px;
            right: 20px;
            background-color: #dc3545;
            color: white;
            padding: 10px 18px;
            border-radius: 25px;
            text-decoration: none;
            font-weight: bold;
            box-shadow: 0 2px 6px rgba(0,0,0,0.2);
            transition: background 0.3s ease;
        }
        .boton-salida:hover {
            background-color: #c82333;
        }
    </style>
</head>
<body class="bg-light">
    <div class="container py-3 wrap">
        <div class="escudo-center" aria-hidden="true"></div>
        <div class="escudo-corner" aria-hidden="true"></div>
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h4>Registros por Programa</h4>
            <a href="index.php" class="boton-salida">Salir</a>

            <div class="d-flex gap-2 align-items-center controls">
                <select id="programFilter" class="form-select form-select-sm">
                    <option value="">Todos los programas</option>
                </select>
                <input id="q" class="form-control form-control-sm" placeholder="Buscar por nombre o cédula...">
                <button id="reload" class="btn btn-sm btn-outline-secondary">Recargar</button>
            </div>
        </div>

        <div id="alert"></div>

        <div class="panel shadow-sm">
            <div id="tableContainer" class="table-responsive">
                <table id="resultsTable" class="table table-sm table-bordered">
                    <thead class="table-dark text-white">
                        <tr>
                            <th>Titular</th>
                            <th>Cédula</th>
                            <th>Hora</th>
                            <th>Programa</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>

            <div id="cardsContainer"></div>
        </div>
    </div>

    <script>
    const api = 'api.php';  
    const alertEl = document.getElementById('alert');

    function showAlert(msg, type='success'){
        alertEl.innerHTML = `<div class="alert alert-${type} alert-dismissible" role="alert">${msg}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>`;
    }

    async function fetchPrograms(){
        const res = await fetch(api + '?action=programs');
        const j = await res.json();
        if(!j.success) return [];
        return j.programs || [];
    }

    async function fetchRecords(params = {}){
        const qs = new URLSearchParams(Object.assign({action:'list'}, params)).toString();
        const res = await fetch(api + '?' + qs);
        const j = await res.json();
        if(!j.success){ showAlert(j.message || 'Error al listar', 'danger'); return []; }
        return j.records || [];
    }

    function renderTable(records){
        const tbody = document.querySelector('#resultsTable tbody'); 
        tbody.innerHTML = '';
        records.forEach(item => {
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td>${(item.titular_nombre||'') + ' ' + (item.titular_apellidos||'')}</td>
                <td>${item.titular_cc||''}</td>
                <td>${item.hora||''}</td>
                <td>${item.programa||''}</td>
            `;
            tbody.appendChild(tr);
        });
    }

    function renderCards(records){
        const c = document.getElementById('cardsContainer'); 
        c.innerHTML = '';
        records.forEach(item => {
            const card = document.createElement('div'); 
            card.className = 'card';
            const header = document.createElement('div'); 
            header.className = 'card-header'; 
            header.textContent = (item.titular_nombre||'') + ' ' + (item.titular_apellidos||'');
            const body = document.createElement('div'); 
            body.className = 'card-body';
            body.innerHTML = `
                <p style="margin:0"><strong>Cédula:</strong> ${item.titular_cc||''}</p>
                <p style="margin:0"><strong>Hora:</strong> ${item.hora||''}</p>
                <p style="margin:0"><strong>Programa:</strong> ${item.programa||''}</p>
            `;
            card.appendChild(header); 
            card.appendChild(body); 
            c.appendChild(card);
        });
    }

    async function load(){
        const sel = document.getElementById('programFilter');
        const list = await fetchPrograms();
        sel.innerHTML = '<option value="">Todos los programas</option>' + list.map(p => {
            if(p === '__EMPTY__') return `<option value="__EMPTY__">(sin programa)</option>`;
            return `<option value="${p}">${p}</option>`;
        }).join('');
        applyFiltersServer();
    }

    async function applyFiltersServer(){
        const q = document.getElementById('q').value || '';
        const program = document.getElementById('programFilter').value || '';
        const params = {};
        if(program) params.programa = program;
        if(q) params.q = q;
        const records = await fetchRecords(params);
        renderTable(records);
        renderCards(records);
    }

    document.getElementById('reload').addEventListener('click', load);
    document.getElementById('programFilter').addEventListener('change', applyFiltersServer);
    document.getElementById('q').addEventListener('input', function(){
        clearTimeout(window.__pp_debounce);
        window.__pp_debounce = setTimeout(applyFiltersServer, 300);
    });

    load();
    </script>
</body>
</html>
