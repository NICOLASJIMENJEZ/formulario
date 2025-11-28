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

  // Program lists for different times
  const programsByTime = {
    '09:30': [
      {v: 'EAUB', t: 'ESPECICALIZACION EN ARQUITECTURA Y URBANISMO BIOCLIMATICO'},
      {v: 'Administrador_de_Empresas', t: 'Administrador de Empresas'},
      {v: 'Tecnologia_en_Gestion_Financiera', t: 'Tecnología en Gestión Financiera'}
    ],
    '10:00': [
      {v: 'Contaduria_Publica', t: 'Contaduría Pública'},
      {v: 'Licenciatura_Educacion_Fisica', t: 'Licenciatura en Educación Física'},
      {v: 'Licenciatura_Educacion_Infantil', t: 'Licenciatura en Educación Infantil'},
      {v: 'Licenciatura_Quimica', t: 'Licenciatura en Química'},
      {v: 'Tecnologia_Contabilidad_Finanzas', t: 'Tecnología en Contabilidad y Finanzas'}
    ]
    ,
    '14:00': [
      {v: 'Arquitectura', t: 'Arquitectura'},
      {v: 'Diseno_Grafico', t: 'Diseño Gráfico'},
      {v: 'Especializacion_Arquitectura_Urbanismo_Bioclimatico', t: 'Especialización en Arquitectura, Urbanismo y Bioclimático'},
      {v: 'Especializacion_Derecho_Empresarial', t: 'Especialización en Derecho Empresarial'},
      {v: 'Especializacion_Gerencia_Proyectos', t: 'Especialización en Gerencia de Proyectos'},
      {v: 'Especialista_Infancia_Cultura_Desarrollo', t: 'Especialista en Infancia, Cultura y Desarrollo'},
      {v: 'Especialista_Pedagogia_Entrenamiento_Deportivo', t: 'Especialista en Pedagogía del Entrenamiento Deportivo'}
    ]
    ,
    '16:30': [
      {v: 'Ingenieria_de_Sistemas', t: 'Ingeniería de Sistemas'},
      {v: 'Ingenieria_Electronica', t: 'Ingeniería Electrónica'},
      {v: 'Psicologia', t: 'Psicología'}
    ]
  };

  function populateProgramaFor(time) {
    if (!programaSelect) return;
    // Clear
    programaSelect.innerHTML = '';
    const placeholder = document.createElement('option');
    placeholder.value = '';
    placeholder.textContent = '-- Seleccione un programa --';
    programaSelect.appendChild(placeholder);

    const list = programsByTime[time] || [];
    list.forEach(p => {
      const opt = document.createElement('option');
      opt.value = p.v;
      opt.textContent = p.t;
      programaSelect.appendChild(opt);
    });
  }

  if (horaSelect) {
    function updateProgramaVisibility() {
      const val = horaSelect.value;
      if (programsByTime[val] && programsByTime[val].length > 0) {
        if (programaWrap) programaWrap.style.display = 'block';
        populateProgramaFor(val);
        if (programaSelect) programaSelect.required = true;
      } else {
        if (programaWrap) programaWrap.style.display = 'none';
        if (programaSelect) {
          programaSelect.required = false;
          programaSelect.value = '';
          programaSelect.innerHTML = '<option value="">-- Seleccione un programa --</option>';
        }
      }
    }
    horaSelect.addEventListener('change', updateProgramaVisibility);
    // inicializar
    updateProgramaVisibility();
  }

  // simple Bootstrap validation and AJAX submit to improve UX
  form.addEventListener('submit', function(e){
    e.preventDefault();
    // Reset alert
    alertBox.style.display = 'none';
    alertBox.className = '';
    // Validate required fields
    if(!form.checkValidity()){
      form.classList.add('was-validated');
      return;
    }

  const data = new FormData(form);
  // marcar petición como AJAX para que el servidor devuelva JSON en lugar de redirecciones HTML
  data.append('ajax','1');
    // send via fetch
    fetch(form.action, {
      method: 'POST',
      headers: {
        'X-Requested-With': 'XMLHttpRequest',
        'Accept': 'application/json'
      },
      body: data
    }).then(async (r) => {
      const text = await r.text();
      // Try to parse JSON; if fails, show raw response for debugging
      let parsed;
      try {
        parsed = JSON.parse(text);
      } catch (e) {
        throw new Error('Respuesta inválida del servidor: ' + text);
      }
      return parsed;
    }).then(resp => {
      if(resp.success){
        alertBox.className = 'alert alert-success';
        alertBox.textContent = resp.message || 'Registro guardado.';
        alertBox.style.display = 'block';
        form.reset();
        // clear dynamic invitados
        container.innerHTML = '';
        invitados = 0;
        removeBtn.disabled = true;
        addBtn.disabled = false;
        discapacidadWrap.style.display = 'none';
        form.classList.remove('was-validated');
        // redirigir a index con mensaje después de 2 segundos
        setTimeout(() => {
          // Redirigir a index.php relativo a la carpeta actual
          const params = '?msg=' + encodeURIComponent(resp.message || 'Guardado correctamente') + '&type=success';
          window.location.href = './index.php' + params;
        }, 2000);
      } else {
        alertBox.className = 'alert alert-danger';
        alertBox.innerHTML = resp.message || 'Error al guardar. Intente de nuevo.';
        alertBox.style.display = 'block';
      }
    }).catch(err => {
      alertBox.className = 'alert alert-danger';
      alertBox.textContent = err.message || 'Error de red o servidor.';
      alertBox.style.display = 'block';
      console.error(err);
    });
  });

})();
