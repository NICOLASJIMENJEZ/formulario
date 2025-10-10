<?php
// Página de administración de registros. La lógica de API está en registros/api.php
require_once __DIR__ . '/auth.php';
require_login();
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Registros - Administración</title>
        <link href="/formulario/assets/css/styles.css" rel="stylesheet">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
        <style>
            /* Larger typography for readability */
            body { font-size: 18px; }
            table { font-size: 18px; }
            .form-control-sm, .form-select-sm { font-size: 16px; padding: .35rem .5rem; }

            /* Empty cell styling */
            .empty-cell { background: #f0f0f0 !important; color: #6c757d !important; }
            #recordsTable td { background: #fff; color: #000; vertical-align: middle; }
            #recordsTable td input.empty-cell { background: #f0f0f0; color: #6c757d; }
            /* Semaforo row colors */
            .semaforo-0 td, .semaforo-0 input, .semaforo-0 select { background: #f8f9fa !important; color: #212529 !important; }
            .semaforo-1 td, .semaforo-1 input, .semaforo-1 select { background: #dc3545 !important; color: #fff !important; }
            .semaforo-2 td, .semaforo-2 input, .semaforo-2 select { background: #fd7e14 !important; color: #fff !important; }
            .semaforo-3 td, .semaforo-3 input, .semaforo-3 select { background: #198754 !important; color: #fff !important; }
            /* Ensure borders remain visible */
            .semaforo-1 td, .semaforo-2 td, .semaforo-3 td { border-color: rgba(255,255,255,0.1); }

            /* Arrival buttons */
            .arrive-btn { width: 38px; height: 32px; padding: 0; border-radius: 6px; font-weight: 700; }
            td > .arrive-btn { margin-right: 8px; }

                /* Make the table container a scrollable panel so scrollbars appear inside it
                    (horizontal scrollbar will be visible without scrolling the whole page) */
                .table-responsive { overflow: auto; max-height: 62vh; padding: 8px; }
            #recordsTable thead th, #recordsTable tbody td { white-space: nowrap; }

            /* Give inputs a sensible min-width so content is visible */
            #recordsTable input.form-control, #recordsTable select.form-select { min-width: 160px; }
            #recordsTable td:nth-child(1) { min-width: 60px; }
            #recordsTable td:nth-child(2) { min-width: 48px; }
            #recordsTable td:nth-child(20) { min-width: 180px; }

            /* On small screens allow table to scale but still scroll horizontally */
            @media (max-width: 768px) {
                body { font-size: 16px; }
                table { font-size: 16px; }
                #recordsTable input.form-control, #recordsTable select.form-select { min-width: 120px; }
            }
        </style>
</head>
<body class="bg-light">
    <div class="container py-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                    <h3>Registros guardados</h3>
                    <div class="d-flex gap-2 align-items-center">
                        <input id="searchCedula" class="form-control form-control-sm" placeholder="Buscar por cédula..." style="min-width:200px;">
                        <button id="reload" class="btn btn-sm btn-outline-secondary">Recargar</button>
                    </div>
                </div>

                <div id="alert"></div>

                <div class="shadow-sm rounded" style="background:#fff;padding:6px;">
                    <div style="font-size:14px;color:#6c757d;margin-bottom:6px;">Desplaza horizontalmente dentro de esta ventana para ver todas las columnas ⇄</div>
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered" id="recordsTable">
                        <thead class="table-dark text-white">
                            <tr>
                                <th style="width:60px">ID</th>
                                <th>Semáforo</th>
                                <th>Titular Nombre</th>
                                <th>Titular Apellidos</th>
                                <th>Cédula</th>
                                <th>Celular</th>
                                <th>Correo</th>
                                <th>Hora</th>
                                <th>Programa</th>
                                <th>Discapacidad</th>
                                <th>Invitado 1</th>
                                <th>CC 1</th>
                                <th>Disc. 1</th>
                                <th>Invitado 2</th>
                                <th>CC 2</th>
                                <th>Disc. 2</th>
                                <th>Invitado 3</th>
                                <th>CC 3</th>
                                <th>Disc. 3</th>
                                <th style="width:140px">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
                        </table>
                    </div>
                </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const api = '/formulario/registros/api.php';
        const alertEl = document.getElementById('alert');

        function showAlert(msg, type='success'){
            alertEl.innerHTML = `<div class="alert alert-${type} alert-dismissible" role="alert">${msg}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>`;
        }

        async function fetchRecords(){
            const res = await fetch(api + '?action=list');
            const j = await res.json();
            if(!j.success) { showAlert(j.message || 'Error al listar', 'danger'); return []; }
            return j.records || [];
        }

        function createCellInput(name, value){
            const td = document.createElement('td');
            const input = document.createElement('input');
            input.type = 'text';
            input.className = 'form-control form-control-sm';
            input.name = name;
            input.value = value === null || value === undefined ? '' : value;
            // empty styling
            if(!input.value) input.classList.add('empty-cell');
            input.addEventListener('input', function(){
                if(!this.value) this.classList.add('empty-cell'); else this.classList.remove('empty-cell');
            });
            td.appendChild(input);
            return td;
        }

        function createGuestInput(index, combinedValue){
            const td = document.createElement('td');
            const input = document.createElement('input');
            input.type = 'text';
            input.className = 'form-control form-control-sm';
            input.name = 'invitado' + index + '_nombre';
            input.value = combinedValue || '';
            if(!input.value) input.classList.add('empty-cell');
            input.addEventListener('input', function(){
                if(!this.value) this.classList.add('empty-cell'); else this.classList.remove('empty-cell');
            });
            td.appendChild(input);
            return td;
        }

        function createActionsCell(id){
            const td = document.createElement('td');
            const save = document.createElement('button');
            save.className = 'btn btn-sm btn-primary me-2';
            save.textContent = 'Guardar';
            const del = document.createElement('button');
            del.className = 'btn btn-sm btn-danger';
            del.textContent = 'Eliminar';
            // arrival buttons
            const arrive0 = document.createElement('button'); arrive0.className='btn btn-sm arrive-btn me-1'; arrive0.style.background='#6c757d'; arrive0.style.color='#fff'; arrive0.textContent='0'; arrive0.setAttribute('aria-label','Marcar 0 llegadas');
            const arrive1 = document.createElement('button'); arrive1.className='btn btn-sm arrive-btn me-1'; arrive1.style.background='#dc3545'; arrive1.style.color='#fff'; arrive1.textContent='1'; arrive1.setAttribute('aria-label','Marcar 1 llegada');
            const arrive2 = document.createElement('button'); arrive2.className='btn btn-sm arrive-btn me-1'; arrive2.style.background='#fd7e14'; arrive2.style.color='#fff'; arrive2.textContent='2'; arrive2.setAttribute('aria-label','Marcar 2 llegadas');
            const arrive3 = document.createElement('button'); arrive3.className='btn btn-sm arrive-btn me-1'; arrive3.style.background='#198754'; arrive3.style.color='#fff'; arrive3.textContent='3'; arrive3.setAttribute('aria-label','Marcar 3 llegadas');

            save.addEventListener('click', async function(){
                const tr = this.closest('tr');
                const inputs = tr.querySelectorAll('input, select, textarea');
                const data = new URLSearchParams();
                data.append('action','update');
                data.append('id', id);
                inputs.forEach(i => data.append(i.name, i.value));
                try{
                    const r = await fetch(api, {method:'POST', body: data});
                    const j = await r.json();
                    if(j.success) showAlert('Registro actualizado', 'success'); else showAlert(j.message || 'Error', 'danger');
                }catch(e){ showAlert('Error de red', 'danger'); }
            });

            // arrival handlers
            async function setArrived(n){
                const data = new URLSearchParams(); data.append('action','update'); data.append('id', id); data.append('arrived_count', String(n));
                try{
                    const r = await fetch(api, {method:'POST', body: data});
                    const j = await r.json();
                    if(j.success){
                        showAlert('Llegada actualizada', 'success');
                        // Update only this row visually without reloading the whole table
                        const tr = td.closest('tr');
                        if(tr){
                            // remove existing semaforo-* classes
                            tr.classList.remove('semaforo-0','semaforo-1','semaforo-2','semaforo-3');
                            const cl = 'semaforo-' + Math.min(3, Math.max(0, n));
                            tr.classList.add(cl);
                            // update the small colored dot in second cell
                            const span = tr.querySelector('td:nth-child(2) span');
                            if(span){
                                span.title = 'Marcado: ' + n + ' llegadas';
                                if(n === 0) span.style.background = '#6c757d';
                                else if(n === 1) span.style.background = '#dc3545';
                                else if(n === 2) span.style.background = '#fd7e14';
                                else span.style.background = '#198754';
                            }
                        }
                    } else {
                        showAlert(j.message || 'Error', 'danger');
                    }
                } catch(e){ showAlert('Error de red', 'danger'); }
            }
            // No confirmation prompts: direct actions (user requested no authorization prompts)
            arrive0.addEventListener('click', function(e){ e.stopPropagation(); setArrived(0); });
            arrive1.addEventListener('click', function(e){ e.stopPropagation(); setArrived(1); });
            arrive2.addEventListener('click', function(e){ e.stopPropagation(); setArrived(2); });
            arrive3.addEventListener('click', function(e){ e.stopPropagation(); setArrived(3); });

            del.addEventListener('click', async function(){
                if(!confirm('¿Eliminar este registro?')) return;
                try{
                    const r = await fetch(api + '?action=delete&id=' + encodeURIComponent(id));
                    const j = await r.json();
                    if(j.success){ showAlert('Registro eliminado', 'success'); load(); } else showAlert(j.message || 'Error', 'danger');
                }catch(e){ showAlert('Error de red', 'danger'); }
            });

            td.appendChild(save);
            td.appendChild(del);
            td.appendChild(document.createElement('br'));
            td.appendChild(arrive0);
            td.appendChild(arrive1);
            td.appendChild(arrive2);
            td.appendChild(arrive3);
            return td;
        }

        function invitadosSummary(item){
            const parts = [];
            for(let i=1;i<=3;i++){
                const n = item[`invitado${i}_nombre`] || '';
                const a = item[`invitado${i}_apellidos`] || '';
                if(n || a) parts.push(n + ' ' + a);
            }
            return parts.join('; ');
        }

        async function load(){
            const tbody = document.querySelector('#recordsTable tbody');
            tbody.innerHTML = '';
            const records = await fetchRecords();
            records.forEach(item => {
                const tr = document.createElement('tr');
                tr.appendChild((() => { const td=document.createElement('td'); td.textContent = item.id; return td; })());
                // Semaforo: prefer manual arrived_count when present
                let count = null;
                if(item.hasOwnProperty('arrived_count') && item.arrived_count !== null && item.arrived_count !== ''){
                    const n = parseInt(item.arrived_count);
                    if(!isNaN(n)) count = Math.max(0, Math.min(3, n));
                }
                if(count === null){
                    count = [1,2,3].reduce((acc,i)=> acc + ((item[`invitado${i}_nombre`]||'') ? 1 : 0), 0);
                }
                tr.classList.add('semaforo-' + Math.min(count,3));
                tr.appendChild((() => {
                    const td = document.createElement('td');
                    const span = document.createElement('span');
                    span.style.display = 'inline-block';
                    span.style.width = '18px';
                    span.style.height = '18px';
                    span.style.borderRadius = '50%';
                    span.title = (item.hasOwnProperty('arrived_count') ? ('Marcado: ' + count + ' llegadas') : (count + ' invitados'));
                    if(count === 0){ span.style.background = '#6c757d'; } // gris
                    else if(count === 1){ span.style.background = '#dc3545'; } // rojo
                    else if(count === 2){ span.style.background = '#fd7e14'; } // naranja
                    else { span.style.background = '#198754'; } // verde
                    td.appendChild(span);
                    return td;
                })());
                tr.appendChild(createCellInput('titular_nombre', item.titular_nombre));
                tr.appendChild(createCellInput('titular_apellidos', item.titular_apellidos));
                tr.appendChild(createCellInput('titular_cc', item.titular_cc));
                tr.appendChild(createCellInput('titular_celular', item.titular_celular));
                tr.appendChild(createCellInput('titular_correo', item.titular_correo));
                // hora as select
                const tdHora = document.createElement('td');
                const selHora = document.createElement('select');
                selHora.name = 'hora';
                ['08:00','10:00','14:00','16:30'].forEach(h=>{ const o=document.createElement('option'); o.value=h; o.textContent=h; if(item.hora==h) o.selected=true; selHora.appendChild(o); });
                selHora.className='form-select form-select-sm';
                tdHora.appendChild(selHora);
                tr.appendChild(tdHora);
                tr.appendChild(createCellInput('programa', item.programa));
                tr.appendChild(createCellInput('discapacidad', item.discapacidad));
                // Guest combined inputs
                const g1 = (item.invitado1_nombre || '') + (item.invitado1_apellidos ? ' ' + item.invitado1_apellidos : '');
                const g2 = (item.invitado2_nombre || '') + (item.invitado2_apellidos ? ' ' + item.invitado2_apellidos : '');
                const g3 = (item.invitado3_nombre || '') + (item.invitado3_apellidos ? ' ' + item.invitado3_apellidos : '');
                // Invitado 1: nombre combinado, cc, discapacidad
                tr.appendChild(createGuestInput(1, g1));
                const td1cc = document.createElement('td');
                const in1cc = document.createElement('input'); in1cc.type='text'; in1cc.className='form-control form-control-sm'; in1cc.name='invitado1_cc'; in1cc.value = item.invitado1_cc || ''; if(!in1cc.value) in1cc.classList.add('empty-cell'); in1cc.addEventListener('input', function(){ this.classList.toggle('empty-cell', !this.value); }); td1cc.appendChild(in1cc); tr.appendChild(td1cc);
                const td1dis = document.createElement('td');
                const sel1d = document.createElement('select'); sel1d.className='form-select form-select-sm'; sel1d.name='invitado1_discapacidad'; ['','si','no'].forEach(v=>{ const o=document.createElement('option'); o.value=v; o.textContent = v ? v : '--'; if((item.invitado1_discapacidad||'')==v) o.selected=true; sel1d.appendChild(o); }); td1dis.appendChild(sel1d); tr.appendChild(td1dis);
                // Invitado 2
                tr.appendChild(createGuestInput(2, g2));
                const td2cc = document.createElement('td');
                const in2cc = document.createElement('input'); in2cc.type='text'; in2cc.className='form-control form-control-sm'; in2cc.name='invitado2_cc'; in2cc.value = item.invitado2_cc || ''; if(!in2cc.value) in2cc.classList.add('empty-cell'); in2cc.addEventListener('input', function(){ this.classList.toggle('empty-cell', !this.value); }); td2cc.appendChild(in2cc); tr.appendChild(td2cc);
                const td2dis = document.createElement('td');
                const sel2d = document.createElement('select'); sel2d.className='form-select form-select-sm'; sel2d.name='invitado2_discapacidad'; ['','si','no'].forEach(v=>{ const o=document.createElement('option'); o.value=v; o.textContent = v ? v : '--'; if((item.invitado2_discapacidad||'')==v) o.selected=true; sel2d.appendChild(o); }); td2dis.appendChild(sel2d); tr.appendChild(td2dis);
                // Invitado 3
                tr.appendChild(createGuestInput(3, g3));
                const td3cc = document.createElement('td');
                const in3cc = document.createElement('input'); in3cc.type='text'; in3cc.className='form-control form-control-sm'; in3cc.name='invitado3_cc'; in3cc.value = item.invitado3_cc || ''; if(!in3cc.value) in3cc.classList.add('empty-cell'); in3cc.addEventListener('input', function(){ this.classList.toggle('empty-cell', !this.value); }); td3cc.appendChild(in3cc); tr.appendChild(td3cc);
                const td3dis = document.createElement('td');
                const sel3d = document.createElement('select'); sel3d.className='form-select form-select-sm'; sel3d.name='invitado3_discapacidad'; ['','si','no'].forEach(v=>{ const o=document.createElement('option'); o.value=v; o.textContent = v ? v : '--'; if((item.invitado3_discapacidad||'')==v) o.selected=true; sel3d.appendChild(o); }); td3dis.appendChild(sel3d); tr.appendChild(td3dis);
                tr.appendChild(createActionsCell(item.id));
                // store cedula value as data attribute for filtering
                tr.dataset.cedula = (item.titular_cc || '').toString();
                tr.dataset.name = ((item.titular_nombre || '') + ' ' + (item.titular_apellidos || '')).toLowerCase();
                tbody.appendChild(tr);
            });
        }

        document.getElementById('reload').addEventListener('click', load);
        document.getElementById('searchCedula').addEventListener('input', function(){
            const q = this.value.trim().toLowerCase();
            document.querySelectorAll('#recordsTable tbody tr').forEach(tr => {
                if(!q) { tr.style.display = ''; return; }
                const ced = (tr.dataset.cedula||'').toLowerCase();
                const name = (tr.dataset.name||'').toLowerCase();
                if(ced.indexOf(q) !== -1 || name.indexOf(q) !== -1) tr.style.display = '';
                else tr.style.display = 'none';
            });
        });
        load();
    </script>
</body>
</html>