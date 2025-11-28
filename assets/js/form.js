// Dynamic invitados (up to 3), disability toggle, and client-side validation
(function(){
  const maxInvitados = 3;
  let invitados = 0;

  const container = document.getElementById('invitadosContainer');
  const addBtn = document.getElementById('addInvitado');
  const removeBtn = document.getElementById('removeInvitado');
  const discapacidad = document.getElementById('discapacidad');
  const discapacidadWrap = document.getElementById('discapacidad_cual_wrap');
  const form = document.getElementById('regForm');

  var alertBox = document.getElementById('formAlert');
  if (!alertBox) {
    alertBox = document.createElement('div');
    alertBox.id = 'formAlert';
    alertBox.style.display = 'none';
    alertBox.className = 'mt-3';
    form.parentNode.insertBefore(alertBox, form.nextSibling);
  }

  const horaSelect = document.getElementById('hora');
  const programaWrap = document.getElementById('programa_wrap');
  const programaSelect = document.getElementById('programa');

  function renderInvitado(index) {
    const idPrefix = 'invitado' + index;
    const div = document.createElement('div');
    div.className = 'invitado border rounded p-3 mb-3';
    div.dataset.index = index;
    div.innerHTML = `
      <h6 class="mb-2">Invitado ${index}</h6>
      <div class="row">
        <div class="col-12 col-md-6 mb-2">
          <label class="form-label" for="${idPrefix}_nombre">Nombre</label>
          <input type="text" class="form-control" id="${idPrefix}_nombre" name="${idPrefix}_nombre" maxlength="100">
        </div>
        <div class="col-12 col-md-6 mb-2">
          <label class="form-label" for="${idPrefix}_apellidos">Apellidos</label>
          <input type="text" class="form-control" id="${idPrefix}_apellidos" name="${idPrefix}_apellidos" maxlength="100">
        </div>
      </div>
      <div class="mb-2">
        <label class="form-label" for="${idPrefix}_cc">Cédula (CC)</label>
        <input type="text" class="form-control" id="${idPrefix}_cc" name="${idPrefix}_cc" maxlength="20">
      </div>
    `;
    return div;
  }

  function addInvitado(){
    if(invitados >= maxInvitados) return;
    invitados++;
    container.appendChild(renderInvitado(invitados));
    removeBtn.disabled = false;
    if(invitados >= maxInvitados) addBtn.disabled = true;
  }

  function removeInvitado(){
    if(invitados <= 0) return;
    const last = container.querySelector('.invitado[data-index="' + invitados + '"]');
    if(last) container.removeChild(last);
    invitados--;
    addBtn.disabled = false;
    if(invitados <= 0) removeBtn.disabled = true;
  }

  addBtn.addEventListener('click', addInvitado);
  removeBtn.addEventListener('click', removeInvitado);

  discapacidad.addEventListener('change', function(){
    discapacidadWrap.style.display = this.checked ? 'block' : 'none';
  });

  // ======================================================
  // PROGRAMAS ASIGNADOS POR HORA
  // ======================================================
  const programsByTime = {
    '09:30': [
      {v: 'ESP_ARQ_URB_BIO', t: 'ESPECIALIZACIÓN DE ARQUITECTURA Y URBANISMO BIOCLIMÁTICO'},
      {v: 'ESP_DERECHO_EMPRESARIAL', t: 'ESPECIALIZACIÓN DE DERECHO EMPRESARIAL'},
      {v: 'ESP_GERENCIA_PROYECTOS', t: 'ESPECIALIZACIÓN DE GERENCIA DE PROYECTOS'},
      {v: 'ESP_INFANCIA_CULTURA_DESARROLLO', t: 'ESPECIALIZACIÓN EN INFANCIA Y CULTURA Y DESARROLLO'},
      {v: 'ESP_PEDAGOGIA_ENTRENAMIENTO_DEPORTIVO', t: 'ESPECIALIZACIÓN EN PEDAGOGÍA DEL ENTRENAMIENTO DEPORTIVO'}
    ],

    '02:00': [
      {v: 'DERECHO', t: 'DERECHO'}
    ],

    '16:30': [
      {v: 'ADMINISTRACION_DE_EMPRESAS', t: 'ADMINISTRACIÓN DE EMPRESAS'},
      {v: 'ARQUITECTURA', t: 'ARQUITECTURA'},
      {v: 'CONTADURIA_PUBLICA', t: 'CONTADURÍA PÚBLICA'},
      {v: 'DISENO_GRAFICO', t: 'DISEÑO GRÁFICO'},
      {v: 'INGENIERIA_DE_SISTEMAS', t: 'INGENIERÍA DE SISTEMAS'},
      {v: 'INGENIERIA_ELECTRONICA', t: 'INGENIERÍA ELECTRÓNICA'},
      {v: 'LIC_EDUCACION_FISICA', t: 'LICENCIATURA EN EDUCACIÓN FÍSICA'},
      {v: 'LIC_EDUCACION_INFANTIL', t: 'LICENCIATURA EN EDUCACIÓN INFANTIL'},
      {v: 'PSICOLOGIA', t: 'PSICOLOGÍA'}
    ]
  };

  // ======================
  // FIX REAL: COMPARACIÓN LIMPIA
  // ======================
  function normalizeHour(str){
    return str.trim().replace(/\s+/g, '');
  }

  function populateProgramaFor(time) {
    const clean = normalizeHour(time);
    const list = programsByTime[clean] || [];

    programaSelect.innerHTML = '';
    const ph = document.createElement('option');
    ph.value = '';
    ph.textContent = '-- Seleccione un programa --';
    programaSelect.appendChild(ph);

    list.forEach(p => {
      const opt = document.createElement('option');
      opt.value = p.v;
      opt.textContent = p.t;
      programaSelect.appendChild(opt);
    });
  }

  function updateProgramaVisibility() {
    const cleanValue = normalizeHour(horaSelect.value);
    const list = programsByTime[cleanValue];

    if (list && list.length > 0) {
      programaWrap.style.display = 'block';
      populateProgramaFor(cleanValue);
      programaSelect.required = true;
    } else {
      programaWrap.style.display = 'none';
      programaSelect.required = false;
      programaSelect.innerHTML = '<option value="">-- Seleccione un programa --</option>';
    }
  }

  if (horaSelect) horaSelect.addEventListener('change', updateProgramaVisibility);

  // Inicializar
  updateProgramaVisibility();

  // ======================================================
  // ENVÍO AJAX
  // ======================================================
  form.addEventListener('submit', function(e){
    e.preventDefault();

    alertBox.style.display = 'none';
    alertBox.className = '';

    if(!form.checkValidity()){
      form.classList.add('was-validated');
      return;
    }

    const data = new FormData(form);
    data.append('ajax','1');

    fetch(form.action, {
      method: 'POST',
      headers: {'X-Requested-With': 'XMLHttpRequest','Accept':'application/json'},
      body: data
    })
    .then(async(r)=>{
      const text = await r.text();
      try { return JSON.parse(text); }
      catch(e){ throw new Error('Respuesta inválida del servidor: ' + text); }
    })
    .then(resp=>{
      if(resp.success){
        alertBox.className = 'alert alert-success';
        alertBox.textContent = resp.message;
        alertBox.style.display = 'block';
        form.reset();
        container.innerHTML = '';
        invitados = 0;
        removeBtn.disabled = true;
        addBtn.disabled = false;
        discapacidadWrap.style.display='none';
        form.classList.remove('was-validated');
        setTimeout(()=>{ window.location.href='./index.php?msg='+encodeURIComponent(resp.message)+'&type=success'; },2000);
      } else {
        alertBox.className='alert alert-danger';
        alertBox.innerHTML=resp.message;
        alertBox.style.display='block';
      }
    })
    .catch(err=>{
      alertBox.className='alert alert-danger';
      alertBox.textContent=err.message;
      alertBox.style.display='block';
      console.error(err);
    });
  });

})();
