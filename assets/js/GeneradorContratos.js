const { useState, useRef } = React;

const GeneradorContratos = () => {
  const [paso, setPaso] = useState(1);
  const [mostrarContrato, setMostrarContrato] = useState(false);
  const contratoRef = useRef();

  const [empresa, setEmpresa] = useState({
    razonSocial: '',
    cuit: '',
    domicilio: '',
    localidad: '',
    provincia: '',
    representanteLegal: '',
    dniRepresentante: '',
    actividad: ''
  });

  const [empleado, setEmpleado] = useState({
    apellido: '',
    nombres: '',
    dni: '',
    cuil: '',
    estadoCivil: 'soltero',
    nacionalidad: 'argentina',
    fechaNacimiento: '',
    domicilio: '',
    localidad: '',
    provincia: '',
    telefono: '',
    email: ''
  });

  const [contrato, setContrato] = useState({
    tipoContrato: 'indeterminado',
    fechaInicio: '',
    fechaFin: '',
    puesto: '',
    categoria: '',
    convenio: '',
    sueldo: '',
    horario: '',
    diasTrabajo: '',
    lugarTrabajo: '',
    periodoVacaciones: '14',
    clausulasEspeciales: [],
    observaciones: ''
  });

  const tiposContrato = {
    indeterminado: {
      nombre: 'Plazo Indeterminado',
      descripcion: 'Contrato sin fecha de finalización específica',
      requiereFin: false
    },
    determinado: {
      nombre: 'Plazo Fijo',
      descripcion: 'Contrato con fecha de finalización específica (máx. 5 años)',
      requiereFin: true
    },
    eventual: {
      nombre: 'Eventual',
      descripcion: 'Para reemplazo temporal o aumento de actividad',
      requiereFin: true
    },
    temporada: {
      nombre: 'Temporada',
      descripcion: 'Para actividades cíclicas o estacionales',
      requiereFin: true
    },
    prueba: {
      nombre: 'Período de Prueba',
      descripcion: 'Período inicial de evaluación (máx. 3 meses)',
      requiereFin: true
    }
  };

  const clausulasDisponibles = [
    { id: 'confidencialidad', nombre: 'Confidencialidad', descripcion: 'Protección de información empresarial' },
    { id: 'exclusividad', nombre: 'Exclusividad', descripcion: 'Dedicación exclusiva a la empresa' },
    { id: 'movilidad', nombre: 'Movilidad', descripcion: 'Posibilidad de traslado geográfico' },
    { id: 'capacitacion', nombre: 'Capacitación', descripcion: 'Obligación de participar en cursos' },
    { id: 'herramientas', nombre: 'Herramientas', descripcion: 'Responsabilidad por equipos asignados' },
    { id: 'remotework', nombre: 'Trabajo Remoto', descripcion: 'Modalidad de teletrabajo' }
  ];

  const estadosCiviles = [
    { value: 'soltero', label: 'Soltero/a' },
    { value: 'casado', label: 'Casado/a' },
    { value: 'divorciado', label: 'Divorciado/a' },
    { value: 'viudo', label: 'Viudo/a' },
    { value: 'union_convivencial', label: 'Unión Convivencial' }
  ];

  const manejarClausulaEspecial = (clausulaId) => {
    const nuevasClausulas = contrato.clausulasEspeciales.includes(clausulaId)
      ? contrato.clausulasEspeciales.filter(id => id !== clausulaId)
      : [...contrato.clausulasEspeciales, clausulaId];
    
    setContrato({ ...contrato, clausulasEspeciales: nuevasClausulas });
  };

  const validarPaso = (pasoActual) => {
    switch (pasoActual) {
      case 1:
        return empresa.razonSocial && empresa.cuit && empresa.representanteLegal;
      case 2:
        return empleado.apellido && empleado.nombres && empleado.dni && empleado.cuil;
      case 3:
        return contrato.puesto && contrato.fechaInicio && contrato.sueldo;
      default:
        return true;
    }
  };

  const calcularEdad = (fechaNacimiento) => {
    if (!fechaNacimiento) return '';
    const hoy = new Date();
    const nacimiento = new Date(fechaNacimiento);
    let edad = hoy.getFullYear() - nacimiento.getFullYear();
    const mesActual = hoy.getMonth();
    const mesNacimiento = nacimiento.getMonth();
    
    if (mesActual < mesNacimiento || (mesActual === mesNacimiento && hoy.getDate() < nacimiento.getDate())) {
      edad--;
    }
    return edad;
  };

  const formatearFecha = (fecha) => {
    if (!fecha) return '';
    const fechaObj = new Date(fecha + 'T00:00:00');
    return fechaObj.toLocaleDateString('es-AR', { 
      year: 'numeric', 
      month: 'long', 
      day: 'numeric' 
    });
  };

  const ContratoGenerado = () => (
    <div ref={contratoRef} className="bg-white p-5 mx-auto border" style={{maxWidth: '8.5in', fontSize: '12pt', lineHeight: '1.5'}}>
      <div className="text-center mb-5">
        <h1 className="h2 fw-bold mb-2">CONTRATO DE TRABAJO</h1>
        <h2 className="h4 fw-semibold">
          {tiposContrato[contrato.tipoContrato] && tiposContrato[contrato.tipoContrato].nombre.toUpperCase()}
        </h2>
      </div>

      <div className="mb-4">
        <p className="mb-4 text-justify">
          En la Ciudad de {empresa.localidad || '(completar localidad)'}, Provincia de {empresa.provincia || '(completar provincia)'}, 
          a los {new Date().getDate()} días del mes de {new Date().toLocaleDateString('es-AR', { month: 'long' })} 
          de {new Date().getFullYear()}, entre:
        </p>

        <p className="mb-2">
          <strong>EMPLEADOR:</strong> {empresa.razonSocial}, CUIT N° {empresa.cuit}, 
          con domicilio en {empresa.domicilio}, {empresa.localidad}, Provincia de {empresa.provincia}, 
          representada en este acto por el Sr./Sra. {empresa.representanteLegal}, 
          DNI N° {empresa.dniRepresentante}, en adelante "EL EMPLEADOR";
        </p>

        <p className="mb-4">
          <strong>EMPLEADO:</strong> {empleado.apellido}, {empleado.nombres}, 
          DNI N° {empleado.dni}, CUIL N° {empleado.cuil}, de {calcularEdad(empleado.fechaNacimiento)} años de edad, 
          estado civil {empleado.estadoCivil}, nacionalidad {empleado.nacionalidad}, 
          con domicilio en {empleado.domicilio}, {empleado.localidad}, Provincia de {empleado.provincia}, 
          en adelante "EL EMPLEADO";
        </p>

        <p className="mb-4 text-justify">
          Se celebra el presente contrato de trabajo sujeto a las disposiciones de la Ley N° 20.744 
          (Ley de Contrato de Trabajo) y sus modificatorias, bajo las siguientes condiciones:
        </p>
      </div>

      <div className="d-grid gap-3">
        <div>
          <h3 className="fw-bold fs-6 mb-2">PRIMERA - OBJETO:</h3>
          <p className="text-justify">
            EL EMPLEADOR contrata los servicios de EL EMPLEADO para desempeñarse en el puesto de {contrato.puesto}, 
            categoría {contrato.categoria}, {contrato.convenio && `bajo el Convenio Colectivo de Trabajo ${contrato.convenio}`}.
          </p>
        </div>

        <div>
          <h3 className="fw-bold fs-6 mb-2">SEGUNDA - PLAZO:</h3>
          <p className="text-justify">
            {contrato.tipoContrato === 'indeterminado' ? (
              `El presente contrato es por tiempo indeterminado y comenzará a regir el día ${formatearFecha(contrato.fechaInicio)}.`
            ) : (
              `El presente contrato es por plazo determinado, comenzará a regir el día ${formatearFecha(contrato.fechaInicio)} y finalizará el ${formatearFecha(contrato.fechaFin)}.`
            )}
          </p>
        </div>

        <div>
          <h3 className="fw-bold fs-6 mb-2">TERCERA - REMUNERACIÓN:</h3>
          <p className="text-justify">
            EL EMPLEADOR abonará a EL EMPLEADO la suma de PESOS ${new Intl.NumberFormat('es-AR').format(contrato.sueldo || 0)} ({contrato.sueldo} PESOS) 
            mensuales, pagaderos en forma mensual y dentro de los cuatro (4) primeros días hábiles del mes siguiente.
          </p>
        </div>

        <div>
          <h3 className="fw-bold fs-6 mb-2">CUARTA - HORARIO Y JORNADA:</h3>
          <p className="text-justify">
            EL EMPLEADO cumplirá una jornada de trabajo de {contrato.horario}, 
            {contrato.diasTrabajo && ` trabajando ${contrato.diasTrabajo}`}. 
            El lugar de trabajo será en {contrato.lugarTrabajo}.
          </p>
        </div>

        <div>
          <h3 className="fw-bold fs-6 mb-2">QUINTA - VACACIONES:</h3>
          <p className="text-justify">
            EL EMPLEADO tendrá derecho a {contrato.periodoVacaciones} días corridos de vacaciones anuales remuneradas, 
            de acuerdo a lo establecido en el Art. 150 de la Ley de Contrato de Trabajo, 
            las que se otorgarán preferentemente en período estival.
          </p>
        </div>

        {contrato.clausulasEspeciales.length > 0 && (
          <div>
            <h3 className="fw-bold fs-6 mb-2">SEXTA - CLÁUSULAS ESPECIALES:</h3>
            {contrato.clausulasEspeciales.map((clausulaId, index) => {
              const clausula = clausulasDisponibles.find(c => c.id === clausulaId) || {};
              return (
                <p key={clausulaId} className="text-justify mb-2">
                  <strong>{index + 1}.</strong> {clausula.descripcion}
                </p>
              );
            })}
          </div>
        )}

        <div>
          <h3 className="fw-bold fs-6 mb-2">SÉPTIMA - OBLIGACIONES:</h3>
          <p className="text-justify">
            EL EMPLEADO se compromete a cumplir con las tareas asignadas, respetar el reglamento interno, 
            mantener la confidencialidad de la información empresarial y cumplir con los horarios establecidos. 
            EL EMPLEADOR se compromete a proporcionar las herramientas de trabajo necesarias y mantener 
            condiciones de higiene y seguridad adecuadas.
          </p>
        </div>

        <div>
          <h3 className="fw-bold fs-6 mb-2">OCTAVA - JURISDICCIÓN:</h3>
          <p className="text-justify">
            Para todos los efectos legales derivados del presente contrato, las partes se someten 
            a la jurisdicción de los Tribunales del Trabajo de {empresa.provincia}.
          </p>
        </div>

        {contrato.observaciones && (
          <div>
            <h3 className="fw-bold fs-6 mb-2">NOVENA - OBSERVACIONES:</h3>
            <p className="text-justify">{contrato.observaciones}</p>
          </div>
        )}

        <div>
          <p className="text-justify mt-4">
            En prueba de conformidad se firman dos (2) ejemplares de un mismo tenor y a un solo efecto 
            en el lugar y fecha ut supra mencionados.
          </p>
        </div>
      </div>

      <div className="row mt-5 pt-5">
        <div className="col-6 text-center">
          <div className="border-top border-dark pt-2 mt-5">
            <p className="fw-bold mb-0">{empresa.representanteLegal}</p>
            <p className="small text-muted mb-0">Por EL EMPLEADOR</p>
            <p className="small text-muted mb-0">{empresa.razonSocial}</p>
          </div>
        </div>
        <div className="col-6 text-center">
          <div className="border-top border-dark pt-2 mt-5">
            <p className="fw-bold mb-0">{empleado.apellido}, {empleado.nombres}</p>
            <p className="small text-muted mb-0">EL EMPLEADO</p>
            <p className="small text-muted mb-0">DNI: {empleado.dni}</p>
          </div>
        </div>
      </div>

      <div className="small text-muted mt-5 text-center">
        <p>Contrato generado digitalmente el {new Date().toLocaleDateString('es-AR')}</p>
        <p>Este documento debe ser registrado ante la autoridad laboral competente</p>
      </div>
    </div>
  );

  if (mostrarContrato) {
    return (
      <div className="bg-light min-vh-100">
        <div className="no-print bg-body shadow-sm p-3 mb-4">
          <div className="container-xl d-flex justify-content-between align-items-center">
            <button
              onClick={() => setMostrarContrato(false)}
              className="btn btn-secondary d-flex align-items-center"
            >
              <i className="bi bi-arrow-left me-2"></i>
              Volver a editar
            </button>
            <div className="d-flex">
              <button
                onClick={() => window.print()}
                className="btn btn-primary d-flex align-items-center"
              >
                <i className="bi bi-printer-fill me-2"></i>
                Imprimir / Guardar PDF
              </button>
            </div>
          </div>
        </div>
        <div className="contract-print-area">
            <ContratoGenerado />
        </div>
      </div>
    );
  }

  return (
    <div className="card shadow-sm p-4">
      <div className="text-center mb-4">
        <div className="d-flex justify-content-center align-items-center mb-2">
          <i className="bi bi-file-earmark-text-fill text-primary fs-2 me-2"></i>
          <h1 className="h3 fw-bold text-body-emphasis">Generador de Contratos Laborales</h1>
        </div>
        <p className="text-muted">Crea contratos conformes a la legislación argentina</p>
      </div>

      <div className="d-flex justify-content-between align-items-center mb-5 px-md-5">
        {[
          {num: 1, label: 'Empleador'}, 
          {num: 2, label: 'Empleado'}, 
          {num: 3, label: 'Contrato'}, 
          {num: 4, label: 'Cláusulas'}
        ].map(({num, label}, index, arr) => (
          <React.Fragment key={num}>
            <div className="d-flex flex-column align-items-center text-center" style={{width: '80px'}}>
              <div className={`progress-step-number ${
                paso >= num ? 'bg-primary text-white' : 'bg-body-secondary text-body-tertiary'
              }`}>
                {paso > num ? <i className="bi bi-check-lg"></i> : num}
              </div>
              <small className={`mt-2 ${paso >= num ? 'text-primary' : 'text-muted'}`}>{label}</small>
            </div>
            {index < arr.length - 1 && (
              <div className={`progress-step-line mx-2 ${
                paso > num ? 'bg-primary' : 'bg-body-secondary'
              }`} />
            )}
          </React.Fragment>
        ))}
      </div>

      <div className="my-4">
        {paso === 1 && (
          <div>
            <div className="text-center mb-4">
              <i className="bi bi-building-fill text-primary fs-1 mx-auto mb-2"></i>
              <h2 className="h4 fw-bold">Datos del Empleador</h2>
              <p className="text-muted">Información de la empresa contratante</p>
            </div>
            <div className="row g-3">
              <div className="col-md-6">
                <label className="form-label">Razón Social *</label>
                <input type="text" value={empresa.razonSocial} onChange={(e) => setEmpresa({...empresa, razonSocial: e.target.value})} className="form-control" placeholder="Ej: Empresa S.A." />
              </div>
              <div className="col-md-6">
                <label className="form-label">CUIT *</label>
                <input type="text" value={empresa.cuit} onChange={(e) => setEmpresa({...empresa, cuit: e.target.value})} className="form-control" placeholder="XX-XXXXXXXX-X" />
              </div>
              <div className="col-12">
                <label className="form-label">Domicilio</label>
                <input type="text" value={empresa.domicilio} onChange={(e) => setEmpresa({...empresa, domicilio: e.target.value})} className="form-control" placeholder="Dirección completa" />
              </div>
              <div className="col-md-6">
                <label className="form-label">Localidad</label>
                <input type="text" value={empresa.localidad} onChange={(e) => setEmpresa({...empresa, localidad: e.target.value})} className="form-control" placeholder="Ciudad" />
              </div>
              <div className="col-md-6">
                <label className="form-label">Provincia</label>
                <input type="text" value={empresa.provincia} onChange={(e) => setEmpresa({...empresa, provincia: e.target.value})} className="form-control" placeholder="Provincia" />
              </div>
              <div className="col-md-6">
                <label className="form-label">Representante Legal *</label>
                <input type="text" value={empresa.representanteLegal} onChange={(e) => setEmpresa({...empresa, representanteLegal: e.target.value})} className="form-control" placeholder="Nombre completo" />
              </div>
              <div className="col-md-6">
                <label className="form-label">DNI Representante</label>
                <input type="text" value={empresa.dniRepresentante} onChange={(e) => setEmpresa({...empresa, dniRepresentante: e.target.value})} className="form-control" placeholder="DNI sin puntos" />
              </div>
              <div className="col-12">
                <label className="form-label">Actividad Principal</label>
                <input type="text" value={empresa.actividad} onChange={(e) => setEmpresa({...empresa, actividad: e.target.value})} className="form-control" placeholder="Descripción de la actividad" />
              </div>
            </div>
          </div>
        )}

        {paso === 2 && (
          <div>
            <div className="text-center mb-4">
              <i className="bi bi-person-vcard-fill text-primary fs-1 mx-auto mb-2"></i>
              <h2 className="h4 fw-bold">Datos del Empleado</h2>
              <p className="text-muted">Información personal del trabajador</p>
            </div>
            <div className="row g-3">
              <div className="col-md-6"><label className="form-label">Apellido *</label><input type="text" value={empleado.apellido} onChange={(e) => setEmpleado({...empleado, apellido: e.target.value})} className="form-control" /></div>
              <div className="col-md-6"><label className="form-label">Nombres *</label><input type="text" value={empleado.nombres} onChange={(e) => setEmpleado({...empleado, nombres: e.target.value})} className="form-control" /></div>
              <div className="col-md-6"><label className="form-label">DNI *</label><input type="text" value={empleado.dni} onChange={(e) => setEmpleado({...empleado, dni: e.target.value})} className="form-control" placeholder="Sin puntos" /></div>
              <div className="col-md-6"><label className="form-label">CUIL *</label><input type="text" value={empleado.cuil} onChange={(e) => setEmpleado({...empleado, cuil: e.target.value})} className="form-control" placeholder="XX-XXXXXXXX-X" /></div>
              <div className="col-md-6">
                <label className="form-label">Estado Civil</label>
                <select value={empleado.estadoCivil} onChange={(e) => setEmpleado({...empleado, estadoCivil: e.target.value})} className="form-select">
                  {estadosCiviles.map(estado => (<option key={estado.value} value={estado.value}>{estado.label}</option>))}
                </select>
              </div>
              <div className="col-md-6"><label className="form-label">Nacionalidad</label><input type="text" value={empleado.nacionalidad} onChange={(e) => setEmpleado({...empleado, nacionalidad: e.target.value})} className="form-control" /></div>
              <div className="col-md-6"><label className="form-label">Fecha de Nacimiento</label><input type="date" value={empleado.fechaNacimiento} onChange={(e) => setEmpleado({...empleado, fechaNacimiento: e.target.value})} className="form-control" /></div>
              <div className="col-md-6"><label className="form-label">Teléfono</label><input type="text" value={empleado.telefono} onChange={(e) => setEmpleado({...empleado, telefono: e.target.value})} className="form-control" /></div>
              <div className="col-12"><label className="form-label">Domicilio</label><input type="text" value={empleado.domicilio} onChange={(e) => setEmpleado({...empleado, domicilio: e.target.value})} className="form-control" placeholder="Dirección completa" /></div>
              <div className="col-md-6"><label className="form-label">Localidad</label><input type="text" value={empleado.localidad} onChange={(e) => setEmpleado({...empleado, localidad: e.target.value})} className="form-control" /></div>
              <div className="col-md-6"><label className="form-label">Provincia</label><input type="text" value={empleado.provincia} onChange={(e) => setEmpleado({...empleado, provincia: e.target.value})} className="form-control" /></div>
              <div className="col-md-6"><label className="form-label">Email</label><input type="email" value={empleado.email} onChange={(e) => setEmpleado({...empleado, email: e.target.value})} className="form-control" /></div>
            </div>
          </div>
        )}

        {paso === 3 && (
          <div>
            <div className="text-center mb-4">
              <i className="bi bi-briefcase-fill text-primary fs-1 mx-auto mb-2"></i>
              <h2 className="h4 fw-bold">Condiciones del Contrato</h2>
              <p className="text-muted">Detalles laborales y remuneración</p>
            </div>
            <div className="row g-3">
              <div className="col-12">
                <label className="form-label">Tipo de Contrato *</label>
                <div className="row g-3">
                  {Object.entries(tiposContrato).map(([key, tipo]) => (
                    <div className="col-md-6" key={key}>
                      <div onClick={() => setContrato({...contrato, tipoContrato: key})} className={`card card-body clickable-card h-100 ${contrato.tipoContrato === key ? 'active' : ''}`}>
                        <h4 className="card-title h6">{tipo.nombre}</h4>
                        <p className="card-text small text-muted mb-0">{tipo.descripcion}</p>
                      </div>
                    </div>
                  ))}
                </div>
              </div>
              <div className="col-md-6"><label className="form-label">Fecha de Inicio *</label><input type="date" value={contrato.fechaInicio} onChange={(e) => setContrato({...contrato, fechaInicio: e.target.value})} className="form-control" /></div>
              {tiposContrato[contrato.tipoContrato] && tiposContrato[contrato.tipoContrato].requiereFin && (<div className="col-md-6"><label className="form-label">Fecha de Fin *</label><input type="date" value={contrato.fechaFin} onChange={(e) => setContrato({...contrato, fechaFin: e.target.value})} className="form-control" /></div>)}
              <div className="col-md-6"><label className="form-label">Puesto de Trabajo *</label><input type="text" value={contrato.puesto} onChange={(e) => setContrato({...contrato, puesto: e.target.value})} className="form-control" placeholder="Ej: Desarrollador Senior" /></div>
              <div className="col-md-6"><label className="form-label">Categoría</label><input type="text" value={contrato.categoria} onChange={(e) => setContrato({...contrato, categoria: e.target.value})} className="form-control" placeholder="Ej: Profesional" /></div>
              <div className="col-md-6">
                <label className="form-label">Sueldo Básico *</label>
                <div className="input-group"><span className="input-group-text">$</span><input type="number" value={contrato.sueldo} onChange={(e) => setContrato({...contrato, sueldo: e.target.value})} className="form-control" placeholder="Monto en pesos" /></div>
              </div>
              <div className="col-md-6"><label className="form-label">Convenio Colectivo</label><input type="text" value={contrato.convenio} onChange={(e) => setContrato({...contrato, convenio: e.target.value})} className="form-control" placeholder="Ej: CCT 130/75" /></div>
              <div className="col-md-6"><label className="form-label">Horario de Trabajo</label><input type="text" value={contrato.horario} onChange={(e) => setContrato({...contrato, horario: e.target.value})} className="form-control" placeholder="Ej: 9:00 a 18:00 hs" /></div>
              <div className="col-md-6"><label className="form-label">Días de Trabajo</label><input type="text" value={contrato.diasTrabajo} onChange={(e) => setContrato({...contrato, diasTrabajo: e.target.value})} className="form-control" placeholder="Ej: Lunes a Viernes" /></div>
              <div className="col-12"><label className="form-label">Lugar de Trabajo</label><input type="text" value={contrato.lugarTrabajo} onChange={(e) => setContrato({...contrato, lugarTrabajo: e.target.value})} className="form-control" placeholder="Dirección del lugar de trabajo" /></div>
              <div className="col-md-6">
                <label className="form-label">Días de Vacaciones Anuales</label>
                <select value={contrato.periodoVacaciones} onChange={(e) => setContrato({...contrato, periodoVacaciones: e.target.value})} className="form-select">
                  <option value="14">14 días (menos de 5 años)</option>
                  <option value="21">21 días (5 a 10 años)</option>
                  <option value="28">28 días (10 a 20 años)</option>
                  <option value="35">35 días (más de 20 años)</option>
                </select>
              </div>
            </div>
          </div>
        )}

        {paso === 4 && (
          <div>
            <div className="text-center mb-4">
              <i className="bi bi-gear-wide-connected text-primary fs-1 mx-auto mb-2"></i>
              <h2 className="h4 fw-bold">Cláusulas Especiales</h2>
              <p className="text-muted">Condiciones adicionales del contrato</p>
            </div>
            <div>
              <label className="form-label mb-3">Seleccionar cláusulas aplicables:</label>
              <div className="row g-3">
                {clausulasDisponibles.map(clausula => (
                  <div className="col-md-6" key={clausula.id}>
                    <div onClick={() => manejarClausulaEspecial(clausula.id)} className={`card card-body clickable-card h-100 ${contrato.clausulasEspeciales.includes(clausula.id) ? 'active' : ''}`}>
                      <div className="d-flex align-items-center">
                        <i className={`bi ${contrato.clausulasEspeciales.includes(clausula.id) ? 'bi-check-square-fill text-primary' : 'bi-square'} fs-4 me-3`}></i>
                        <div>
                          <h4 className="card-title h6 mb-1">{clausula.nombre}</h4>
                          <p className="card-text small text-muted mb-0">{clausula.descripcion}</p>
                        </div>
                      </div>
                    </div>
                  </div>
                ))}
              </div>
            </div>
            <div className="mt-4">
              <label className="form-label">Observaciones Adicionales</label>
              <textarea value={contrato.observaciones} onChange={(e) => setContrato({...contrato, observaciones: e.target.value})} className="form-control" rows={4} placeholder="Condiciones especiales, aclaraciones u observaciones adicionales..." />
            </div>
            <div className="alert alert-warning d-flex align-items-center mt-4">
              <i className="bi bi-exclamation-triangle-fill flex-shrink-0 me-2"></i>
              <div>
                <h5 className="alert-heading h6">Importante</h5>
                Este contrato debe ser registrado ante la autoridad laboral competente. Verifique que todos los datos sean correctos antes de generar el documento final.
              </div>
            </div>
          </div>
        )}
      </div>

      <div className="d-flex justify-content-between pt-4 border-top">
        <button onClick={() => setPaso(Math.max(1, paso - 1))} disabled={paso === 1} className="btn btn-secondary d-flex align-items-center">
          <i className="bi bi-arrow-left me-2"></i>
          Anterior
        </button>

        {paso < 4 ? (
          <button onClick={() => setPaso(paso + 1)} disabled={!validarPaso(paso)} className="btn btn-primary d-flex align-items-center">
            Siguiente
            <i className="bi bi-arrow-right ms-2"></i>
          </button>
        ) : (
          <button onClick={() => setMostrarContrato(true)} disabled={!validarPaso(paso)} className="btn btn-success btn-lg d-flex align-items-center">
            <i className="bi bi-eye-fill me-2"></i>
            Generar Contrato
          </button>
        )}
      </div>
    </div>
  );
};