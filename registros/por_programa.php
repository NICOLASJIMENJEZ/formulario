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
        /* semaforo colors for table rows */
        .semaforo-0 td, .semaforo-0 .card-header { background:#f8f9fa !important; color:#212529 }
        .semaforo-1 td, .semaforo-1 .card-header { background:#dc3545 !important; color:#fff }
        .semaforo-2 td, .semaforo-2 .card-header { background:#fd7e14 !important; color:#fff }
        .semaforo-3 td, .semaforo-3 .card-header { background:#198754 !important; color:#fff }

        /* Desktop: table visible, cards hidden */
        #cardsContainer { display:none; }

        @media (max-width: 768px) {
            /* Mobile: show cards, hide table */
            #tableContainer { display:none; }
            #cardsContainer { display:block; }
            .card { margin-bottom:10px }
        }

        .card-header { font-weight:700 }
    </style>
</head>
<body class="bg-light">
    <div class="container py-3">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h4>Registros por Programa</h4>
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
                    <thead class="table-dark text-white"><tr>
                        <th>ID</th><th>Semáforo</th><th>Titular</th><th>Cédula</th><th>Celular</th><th>Hora</th><th>Programa</th>
                    </tr></thead>
                    <tbody></tbody>
                </table>
            </div>

            <div id="cardsContainer"></div>
        </div>
    </div>

    <script>
        const api = '/formulario/registros/api.php';
        const alertEl = document.getElementById('alert');

        function showAlert(msg, type='success'){
            alertEl.innerHTML = `<div class="alert alert-${type} alert-dismissible" role="alert">${msg}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>`;
        }

        async function fetchRecords(params = {}){
            const qs = new URLSearchParams(Object.assign({action:'list'}, params)).toString();
            const res = await fetch(api + '?' + qs);
            const j = await res.json();
            if(!j.success){ showAlert(j.message || 'Error al listar', 'danger'); return []; }
            return j.records || [];
        }

        function semaforoFor(item){
            let count = null;
            if(item.hasOwnProperty('arrived_count') && item.arrived_count !== null && item.arrived_count !== ''){
                const n = parseInt(item.arrived_count); if(!isNaN(n)) count = Math.max(0, Math.min(3, n));
            }
            if(count === null){
                count = [1,2,3].reduce((acc,i)=> acc + ((item[`invitado${i}_nombre`]||'') ? 1 : 0), 0);
            }
            return Math.min(3, Math.max(0, count));
        }

        function renderTable(records){
            const tbody = document.querySelector('#resultsTable tbody'); tbody.innerHTML = '';
            records.forEach(item => {
                const tr = document.createElement('tr');
                const s = semaforoFor(item);
                tr.classList.add('semaforo-' + s);
                tr.innerHTML = `
                    <td>${item.id}</td>
                    <td><span style="display:inline-block;width:18px;height:18px;border-radius:50%;background:${s===0?'#6c757d':s===1?'#dc3545':s===2?'#fd7e14':'#198754'}" title="${s} llegadas"></span></td>
                    <td>${(item.titular_nombre||'') + ' ' + (item.titular_apellidos||'')}</td>
                    <td>${item.titular_cc||''}</td>
                    <td>${item.titular_celular||''}</td>
                    <td>${item.hora||''}</td>
                    <td>${item.programa||''}</td>
                `;
                tbody.appendChild(tr);
            });
        }

        function renderCards(records){
            const c = document.getElementById('cardsContainer'); c.innerHTML = '';
            records.forEach(item => {
                const s = semaforoFor(item);
                const card = document.createElement('div'); card.className = 'card';
                const header = document.createElement('div'); header.className = 'card-header'; header.textContent = (item.titular_nombre||'') + ' ' + (item.titular_apellidos||'');
                header.style.background = s===0?'#f8f9fa': s===1? '#dc3545': s===2? '#fd7e14':'#198754';
                header.style.color = s===0? '#212529':'#fff';
                const body = document.createElement('div'); body.className = 'card-body';
                body.innerHTML = `
                    <p style="margin:0"><strong>Cédula:</strong> ${item.titular_cc||''}</p>
                    <p style="margin:0"><strong>Celular:</strong> ${item.titular_celular||''}</p>
                    <p style="margin:0"><strong>Hora:</strong> ${item.hora||''} &nbsp; <strong>Programa:</strong> ${item.programa||''}</p>
                    <p style="margin-top:.5rem;margin-bottom:0"><strong>Invitados:</strong> ${((item.invitado1_nombre||'')+(item.invitado1_apellidos?(' '+item.invitado1_apellidos):'')||'')}${(item.invitado2_nombre||'')?(', '+(item.invitado2_nombre+' '+(item.invitado2_apellidos||''))):''}${(item.invitado3_nombre||'')?(', '+(item.invitado3_nombre+' '+(item.invitado3_apellidos||''))):''}</p>
                `;
                card.appendChild(header); card.appendChild(body); c.appendChild(card);
            });
        }

        async function load(){
            // populate program select from server
            const res = await fetch(api + '?action=programs');
            const j = await res.json();
            const sel = document.getElementById('programFilter');
            if(j.success){
                const list = j.programs || [];
                // map '__EMPTY__' to user-friendly label
                sel.innerHTML = '<option value="">Todos los programas</option>' + list.map(p => {
                    if(p === '__EMPTY__') return `<option value="__EMPTY__">(sin programa)</option>`;
                    return `<option value="${p}">${p}</option>`;
                }).join('');
            }

            // initial fetch without filters
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
            // debounce simple
            clearTimeout(window.__pp_debounce);
            window.__pp_debounce = setTimeout(applyFiltersServer, 300);
        });

        load();
    </script>
</body>
</html>
