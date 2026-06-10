@extends('layouts.app')

@section('title', 'CU16 Reportes Academicos y Administrativos | CUPCore')

@section('content')
    {{-- CU16 - Reportes: pantalla principal para consulta y exportación de reportes académicos.
        - Incluye: sección de filtros estructurados, controles de consulta por voz, interpretación con IA y exportación (CSV/Excel/PDF).
        - Caso de uso: generar reportes filtrados por gestión, carrera, materia, estado, etc., y exportarlos para análisis o impresión.
        - Nota: la lógica de voz/IA en el frontend sólo aplica filtros y solicita interpretación al backend; NO expone claves de API en el navegador.
    --}}
    <div class="flex flex-col gap-4 xl:flex-row xl:items-start xl:justify-between">
        <x-page-title title="CU16 Reportes Academicos y Administrativos" subtitle="Consulta reportes filtrables del proceso de admision." />
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('gestion-academica-cup.reportes.historial') }}" class="btn btn-outline w-full sm:w-auto">Historial de reportes</a>
            <a href="{{ route('gestion-academica-cup.reportes.dashboard') }}" class="btn btn-primary w-full sm:w-auto">KPIs academicos</a>
            <a href="{{ route('gestion-academica-cup.reportes.consulta') }}" class="btn btn-outline w-full sm:w-auto">Limpiar filtros</a>
        </div>
    </div>

    @if ($errors->any())
        <div class="alert alert-error">
            <div class="space-y-1">
                @foreach ($errors->all() as $error)
                    <p>{{ $error }}</p>
                @endforeach
            </div>
        </div>
    @endif

    {{-- Consulta por voz: usa Web Speech API del navegador para dictar comandos.
        - El audio NO se almacena; la transcripción local se transforma en filtros o búsqueda.
        - Botones: iniciar/detener/usar como búsqueda/aplicar a filtros/interpretar con IA/generar con IA.
        - "Interpretar con IA": envía texto al backend para recibir filtros sugeridos (no genera reportes automáticamente).
        - "Generar con IA": intenta interpretar Y ejecutar la generación automática del reporte (envía filtros y puede enviar formulario).
    --}}
    <x-card title="Consulta por voz">
        <div class="grid gap-6 2xl:grid-cols-[0.85fr_1.15fr]">
            <div class="space-y-4">
                <p class="text-sm text-base-content/80">Usa el reconocimiento de voz del navegador para dictar un comando y reutilizarlo en los filtros existentes del reporte.</p>
                <div class="flex flex-wrap gap-3">
                    <button type="button" id="voice-start-button" class="btn btn-primary w-full sm:w-auto">Iniciar voz</button>
                    <button type="button" id="voice-stop-button" class="btn btn-warning w-full sm:w-auto">Detener</button>
                    <button type="button" id="voice-apply-search-button" class="btn btn-outline w-full sm:w-auto">Usar comando como busqueda</button>
                    <button type="button" id="voice-apply-filters-button" class="btn btn-outline w-full sm:w-auto">Aplicar comando a filtros</button>
                    <button type="button" id="voice-ai-button" class="btn btn-secondary w-full sm:w-auto">Interpretar con IA</button>
                    <button type="button" id="voice-ai-generate-button" class="btn btn-info w-full sm:w-auto">Generar con IA</button>
                </div>
                <div id="voice-status" class="alert">
                    <span>Listo para escuchar</span>
                </div>
                <p class="text-xs text-base-content/70">La voz se procesa mediante el reconocimiento del navegador. No se guarda audio en el sistema.</p>
            </div>

            <div class="space-y-4">
                <label class="form-control">
                    <span class="label-text">Comando detectado</span>
                    <textarea id="voice-command-text" rows="5" class="textarea textarea-bordered w-full" placeholder="Puedes hablar o escribir manualmente un comando aqui."></textarea>
                </label>
                <div class="rounded-2xl border border-base-300/60 bg-base-200/50 p-4">
                    <p class="text-sm font-semibold text-white">Ejemplos</p>
                    <ul class="mt-3 space-y-2 break-words text-sm text-base-content/80">
                        <li>"mostrar resultados aprobados de la gestion 2-2028"</li>
                        <li>"buscar notas de computacion"</li>
                        <li>"reporte de cupos de la gestion 2-2028"</li>
                        <li>"pagos confirmados"</li>
                        <li>"asistencia docente de hoy"</li>
                    </ul>
                </div>
            </div>
        </div>
    </x-card>

    {{-- Sección de filtros estructurados:
        - Permite construir consultas precisas que luego pueden exportarse a CSV/Excel o abrir una vista imprimible.
        - Los filtros aplican sólo si son compatibles con el tipo de reporte seleccionado.
        - Exportaciones: CSV, Excel y vista imprimible/PDF; las rutas de exportación generan los archivos en el servidor y se registran en el historial.
        - Seguridad: el JS de voz/IA sólo envía texto al backend; las API keys (si existen) quedan en el servidor. El frontend no las contiene.
    --}}
    <x-card title="Configuracion del reporte">
        <form method="GET" action="{{ route('gestion-academica-cup.reportes.consulta') }}" class="grid grid-cols-1 gap-4 sm:grid-cols-2 2xl:grid-cols-4" id="form-reportes">
            <label class="form-control sm:col-span-2">
                <span class="label-text">Tipo de reporte</span>
                <select name="tipo_reporte" id="reportes-tipo-reporte" class="select select-bordered">
                    <option value="">Selecciona un reporte</option>
                    @foreach ($reportTypes as $key => $reportType)
                        <option value="{{ $key }}" @selected($filters['tipo_reporte'] === $key)>{{ $reportType['label'] }}</option>
                    @endforeach
                </select>
            </label>
            <label class="form-control">
                <span class="label-text">Gestion</span>
                <select name="gestion" id="reportes-gestion" class="select select-bordered">
                    <option value="">Todas</option>
                    @foreach ($formOptions['gestiones'] as $gestion)
                        <option value="{{ $gestion }}" @selected($filters['gestion'] === $gestion)>{{ $gestion }}</option>
                    @endforeach
                </select>
            </label>
            <label class="form-control">
                <span class="label-text">Carrera</span>
                <select name="carrera_id" id="reportes-carrera" class="select select-bordered">
                    <option value="">Todas</option>
                    @foreach ($formOptions['carreras'] as $carrera)
                        <option value="{{ $carrera->id }}" @selected($filters['carrera_id'] === (string) $carrera->id)>{{ $carrera->nombre }}</option>
                    @endforeach
                </select>
            </label>
            <label class="form-control">
                <span class="label-text">Grupo</span>
                <select name="grupo_id" id="reportes-grupo" class="select select-bordered">
                    <option value="">Todos</option>
                    @foreach ($formOptions['grupos'] as $grupo)
                        <option value="{{ $grupo->id }}" @selected($filters['grupo_id'] === (string) $grupo->id)>{{ $grupo->nombre }} - {{ $grupo->gestion }}</option>
                    @endforeach
                </select>
            </label>
            <label class="form-control">
                <span class="label-text">Materia</span>
                <select name="materia_id" id="reportes-materia" class="select select-bordered">
                    <option value="">Todas</option>
                    @foreach ($formOptions['materias'] as $materia)
                        <option value="{{ $materia->id }}" @selected($filters['materia_id'] === (string) $materia->id)>{{ $materia->nombre }}</option>
                    @endforeach
                </select>
            </label>
            <label class="form-control">
                <span class="label-text">Estado inscripcion</span>
                <select name="estado_inscripcion" id="reportes-estado-inscripcion" class="select select-bordered">
                    <option value="">Todos</option>
                    @foreach ($formOptions['estadosInscripcion'] as $estadoInscripcion)
                        <option value="{{ $estadoInscripcion }}" @selected($filters['estado_inscripcion'] === $estadoInscripcion)>{{ $estadoInscripcion }}</option>
                    @endforeach
                </select>
            </label>
            <label class="form-control">
                <span class="label-text">Estado pago</span>
                <select name="estado_pago" id="reportes-estado-pago" class="select select-bordered">
                    <option value="">Predeterminado</option>
                    @foreach ($formOptions['estadosPago'] as $estadoPago)
                        <option value="{{ $estadoPago }}" @selected($filters['estado_pago'] === $estadoPago)>{{ $estadoPago }}</option>
                    @endforeach
                </select>
            </label>
            <label class="form-control">
                <span class="label-text">Estado resultado</span>
                <select name="estado_resultado" id="reportes-estado-resultado" class="select select-bordered">
                    <option value="">Todos</option>
                    @foreach ($formOptions['estadosResultado'] as $estadoResultado)
                        <option value="{{ $estadoResultado }}" @selected($filters['estado_resultado'] === $estadoResultado)>{{ $estadoResultado }}</option>
                    @endforeach
                </select>
            </label>
            <label class="form-control">
                <span class="label-text">Estado asistencia</span>
                <select name="estado_asistencia" id="reportes-estado-asistencia" class="select select-bordered">
                    <option value="">Todos</option>
                    @foreach ($formOptions['estadosAsistencia'] as $estadoAsistencia)
                        <option value="{{ $estadoAsistencia }}" @selected($filters['estado_asistencia'] === $estadoAsistencia)>{{ $estadoAsistencia }}</option>
                    @endforeach
                </select>
            </label>
            <label class="form-control">
                <span class="label-text">Fecha desde</span>
                <input type="date" name="fecha_desde" id="reportes-fecha-desde" value="{{ $filters['fecha_desde'] }}" class="input input-bordered">
            </label>
            <label class="form-control">
                <span class="label-text">Fecha hasta</span>
                <input type="date" name="fecha_hasta" id="reportes-fecha-hasta" value="{{ $filters['fecha_hasta'] }}" class="input input-bordered">
            </label>
            <label class="form-control sm:col-span-2">
                <span class="label-text">Busqueda</span>
                <input type="text" name="busqueda" id="reportes-busqueda" value="{{ $filters['busqueda'] }}" class="input input-bordered" placeholder="CI, nombres, apellidos, correo, carrera o codigo segun el reporte">
            </label>
            <div class="sm:col-span-2 2xl:col-span-4">
                <div class="alert">
                    <span>Los filtros se aplican solo cuando tienen relacion con el tipo de reporte seleccionado.</span>
                </div>
            </div>
            <div class="sm:col-span-2 2xl:col-span-4 flex flex-wrap gap-3">
                <button type="submit" class="btn btn-primary w-full sm:w-auto">Generar reporte</button>
            </div>
        </form>
    </x-card>

    {{-- Resultado de CU16: resume la consulta, conserva los filtros aplicados y ofrece las mismas opciones de exportación para el conjunto obtenido. --}}
    @if ($filters['tipo_reporte'] === '')
        <div class="grid gap-4 lg:grid-cols-2 2xl:grid-cols-3">
            @foreach ($reportTypes as $reportType)
                <x-card :title="$reportType['label']">
                    <p class="text-sm text-base-content/80">{{ $reportType['description'] }}</p>
                </x-card>
            @endforeach
        </div>
    @elseif ($reportData !== null)
        <x-card title="Resumen del reporte">
            <div class="grid gap-4 lg:grid-cols-3">
                <div>
                    <p class="text-xs uppercase tracking-[0.25em] text-info/80">Tipo de reporte</p>
                    <p class="mt-2 text-lg font-semibold text-white">{{ $reportData['label'] }}</p>
                </div>
                <div>
                    <p class="text-xs uppercase tracking-[0.25em] text-info/80">Total de registros</p>
                    <p class="mt-2 text-lg font-semibold text-white">{{ $reportData['total'] }}</p>
                </div>
                <div>
                    <p class="text-xs uppercase tracking-[0.25em] text-info/80">Filtros aplicados</p>
                    <div class="mt-2 flex flex-wrap gap-2">
                        @forelse ($appliedFilters as $label => $value)
                            <span class="badge badge-outline">{{ $label }}: {{ $value }}</span>
                        @empty
                            <span class="badge badge-outline">Sin filtros adicionales</span>
                        @endforelse
                    </div>
                </div>
            </div>
            <div class="mt-4 flex flex-wrap gap-3">
                <a href="{{ route('gestion-academica-cup.reportes.exportar.csv', request()->query()) }}" class="btn btn-primary w-full sm:w-auto">Exportar CSV</a>
                <a href="{{ route('gestion-academica-cup.reportes.exportar.excel', request()->query()) }}" class="btn btn-outline w-full sm:w-auto">Exportar Excel</a>
                <a href="{{ route('gestion-academica-cup.reportes.imprimir', request()->query()) }}" class="btn btn-outline w-full sm:w-auto" target="_blank" rel="noopener">Vista imprimible / PDF</a>
            </div>
            <p class="mt-3 text-sm text-base-content/70">Puedes exportar el reporte en CSV, Excel o abrir una vista imprimible para guardar como PDF. Las exportaciones se registran en el historial de reportes.</p>
        </x-card>

        {{-- Tabla de resultados: sus columnas cambian según el tipo de reporte y la paginación evita cargar todos los registros en una sola vista. --}}
        @if ($reportData['results']->count() === 0)
            <div class="alert alert-info">
                <span>No existen datos para los filtros seleccionados.</span>
            </div>
        @else
            <x-card :title="$reportData['label']">
                <div class="overflow-x-auto">
                    <table class="table min-w-[900px] text-sm">
                        <thead>
                            <tr>
                                @foreach ($reportData['columns'] as $label)
                                    <th>{{ $label }}</th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($reportData['rows'] as $row)
                                <tr>
                                    @foreach (array_keys($reportData['columns']) as $columnKey)
                                        <td>{{ $row[$columnKey] ?? '' }}</td>
                                    @endforeach
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                @if (method_exists($reportData['results'], 'links'))
                    <div class="mt-4">
                        {{ $reportData['results']->links() }}
                    </div>
                @endif
            </x-card>
        @endif
    @endif

    {{-- Integración de voz e IA: el navegador transcribe o recibe el texto, mientras el backend interpreta con Groq y devuelve filtros; la API key nunca llega al frontend. --}}
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;
            const startButton = document.getElementById('voice-start-button');
            const stopButton = document.getElementById('voice-stop-button');
            const applySearchButton = document.getElementById('voice-apply-search-button');
            const applyFiltersButton = document.getElementById('voice-apply-filters-button');
            const aiInterpretButton = document.getElementById('voice-ai-button');
            const aiGenerateButton = document.getElementById('voice-ai-generate-button');
            const commandField = document.getElementById('voice-command-text');
            const statusBox = document.getElementById('voice-status');
            const reportForm = document.getElementById('form-reportes');
            const searchInput = document.getElementById('reportes-busqueda');
            const tipoReporteSelect = document.getElementById('reportes-tipo-reporte');
            const gestionSelect = document.getElementById('reportes-gestion');
            const carreraSelect = document.getElementById('reportes-carrera');
            const grupoSelect = document.getElementById('reportes-grupo');
            const materiaSelect = document.getElementById('reportes-materia');
            const estadoInscripcionSelect = document.getElementById('reportes-estado-inscripcion');
            const estadoResultadoSelect = document.getElementById('reportes-estado-resultado');
            const estadoPagoSelect = document.getElementById('reportes-estado-pago');
            const estadoAsistenciaSelect = document.getElementById('reportes-estado-asistencia');
            const fechaDesdeInput = document.getElementById('reportes-fecha-desde');
            const fechaHastaInput = document.getElementById('reportes-fecha-hasta');
            const aiInterpretUrl = @json(route('gestion-academica-cup.reportes.interpretar-comando'));
            const csrfToken = @json(csrf_token());

            const setStatus = (message, type = 'default') => {
                statusBox.className = 'alert';

                if (type === 'success') {
                    statusBox.classList.add('alert-success');
                } else if (type === 'warning') {
                    statusBox.classList.add('alert-warning');
                } else if (type === 'error') {
                    statusBox.classList.add('alert-error');
                } else if (type === 'info') {
                    statusBox.classList.add('alert-info');
                }

                statusBox.innerHTML = '<span>' + message + '</span>';
            };

            const normalizeText = (value) => value
                .toLowerCase()
                .normalize('NFD')
                .replace(/[\u0300-\u036f]/g, '');

            const todayString = () => {
                const now = new Date();
                const month = String(now.getMonth() + 1).padStart(2, '0');
                const day = String(now.getDate()).padStart(2, '0');
                return now.getFullYear() + '-' + month + '-' + day;
            };

            const selectOptionByVisibleText = (selectElement, command) => {
                if (!selectElement) {
                    return false;
                }

                const matchedOption = Array.from(selectElement.options).find((option) => {
                    const optionValue = String(option.value || '').trim();
                    const optionText = normalizeText(option.textContent || '');

                    return optionValue !== '' && optionText !== '' && command.includes(optionText);
                });

                if (!matchedOption) {
                    return false;
                }

                selectElement.value = matchedOption.value;
                return true;
            };

            const resetSelect = (selectElement) => {
                if (selectElement) {
                    selectElement.value = '';
                }
            };

            const resetInput = (inputElement) => {
                if (inputElement) {
                    inputElement.value = '';
                }
            };

            const resetFiltrosParaComando = () => {
                resetSelect(gestionSelect);
                resetSelect(carreraSelect);
                resetSelect(grupoSelect);
                resetSelect(materiaSelect);
                resetSelect(estadoInscripcionSelect);
                resetSelect(estadoPagoSelect);
                resetSelect(estadoResultadoSelect);
                resetSelect(estadoAsistenciaSelect);
                resetInput(fechaDesdeInput);
                resetInput(fechaHastaInput);
                resetInput(searchInput);
            };

            const applyFiltersFromBackend = (filters) => {
                const hasStructuredFilters = Boolean(
                    filters.tipo_reporte ||
                    filters.gestion ||
                    filters.carrera_id ||
                    filters.grupo_id ||
                    filters.materia_id ||
                    filters.estado_inscripcion ||
                    filters.estado_pago ||
                    filters.estado_resultado ||
                    filters.estado_asistencia ||
                    filters.fecha_desde ||
                    filters.fecha_hasta
                );

                resetFiltrosParaComando();

                if (filters.tipo_reporte) {
                    tipoReporteSelect.value = filters.tipo_reporte;
                }

                if (filters.gestion) {
                    gestionSelect.value = filters.gestion;
                }

                if (filters.carrera_id !== null && filters.carrera_id !== undefined) {
                    carreraSelect.value = String(filters.carrera_id);
                }

                if (filters.grupo_id !== null && filters.grupo_id !== undefined) {
                    grupoSelect.value = String(filters.grupo_id);
                }

                if (filters.materia_id !== null && filters.materia_id !== undefined) {
                    materiaSelect.value = String(filters.materia_id);
                }

                if (filters.estado_inscripcion) {
                    estadoInscripcionSelect.value = filters.estado_inscripcion;
                }

                if (filters.estado_pago) {
                    estadoPagoSelect.value = filters.estado_pago;
                }

                if (filters.estado_resultado) {
                    estadoResultadoSelect.value = filters.estado_resultado;
                }

                if (filters.estado_asistencia) {
                    estadoAsistenciaSelect.value = filters.estado_asistencia;
                }

                if (filters.fecha_desde) {
                    fechaDesdeInput.value = filters.fecha_desde;
                }

                if (filters.fecha_hasta) {
                    fechaHastaInput.value = filters.fecha_hasta;
                }

                if (filters.busqueda) {
                    searchInput.value = filters.busqueda;
                }
            };

            const normalizeGestionToken = (value) => normalizeText(value).replace(/[^0-9]/g, '');

            const detectGestionDesdeComando = (normalizedCommand, originalCommand) => {
                if (!gestionSelect) {
                    return null;
                }

                const opciones = Array.from(gestionSelect.options)
                    .map((option) => String(option.value || '').trim())
                    .filter((value) => value !== '');

                if (opciones.length === 0) {
                    return null;
                }

                const normalizedOriginal = normalizeText(originalCommand);
                const compactOriginal = normalizeGestionToken(originalCommand);
                const compactNormalized = normalizeGestionToken(normalizedCommand);

                const explicitPatterns = [
                    /([12])\s*[-/. ]\s*(20\d{2})/,
                    /([12])\s+(20\d{2})/,
                    /\b([12])(20\d{2})\b/,
                ];

                for (const pattern of explicitPatterns) {
                    const match = normalizedOriginal.match(pattern);

                    if (!match) {
                        continue;
                    }

                    const candidate = `${match[1]}-${match[2]}`;

                    if (opciones.includes(candidate)) {
                        return candidate;
                    }
                }

                for (const opcion of opciones) {
                    const normalizedOption = normalizeGestionToken(opcion);

                    if (
                        normalizedOption !== '' &&
                        (compactOriginal.includes(normalizedOption) || compactNormalized.includes(normalizedOption))
                    ) {
                        return opcion;
                    }
                }

                const shortMatch = normalizedOriginal.match(/\b([12])(\d{2})\b/);

                if (shortMatch) {
                    const candidate = `${shortMatch[1]}-20${shortMatch[2]}`;

                    if (opciones.includes(candidate)) {
                        return candidate;
                    }
                }

                const wordCandidates = [
                    { pattern: /\b(primer|primero|uno|1)\b.*\b(20\d{2})\b/, periodo: '1' },
                    { pattern: /\b(segundo|segunda|dos|2)\b.*\b(20\d{2})\b/, periodo: '2' },
                ];

                for (const wordCandidate of wordCandidates) {
                    const match = normalizedOriginal.match(wordCandidate.pattern);

                    if (!match) {
                        continue;
                    }

                    const candidate = `${wordCandidate.periodo}-${match[2]}`;

                    if (opciones.includes(candidate)) {
                        return candidate;
                    }
                }

                return null;
            };

            if (!SpeechRecognition) {
                startButton.disabled = true;
                stopButton.disabled = true;
                setStatus('Tu navegador no soporta reconocimiento de voz. Puedes escribir el comando manualmente.', 'warning');
            }

            let recognition = null;
            let finalTranscript = '';

            if (SpeechRecognition) {
                recognition = new SpeechRecognition();
                recognition.lang = 'es-BO';
                recognition.continuous = false;
                recognition.interimResults = true;

                recognition.onstart = () => {
                    finalTranscript = '';
                    setStatus('Escuchando...', 'info');
                };

                recognition.onresult = (event) => {
                    let interimTranscript = '';

                    for (let i = event.resultIndex; i < event.results.length; i += 1) {
                        const transcript = event.results[i][0].transcript;

                        if (event.results[i].isFinal) {
                            finalTranscript += transcript + ' ';
                        } else {
                            interimTranscript += transcript;
                        }
                    }

                    commandField.value = (finalTranscript + interimTranscript).trim();
                };

                recognition.onerror = (event) => {
                    if (event.error === 'not-allowed' || event.error === 'service-not-allowed') {
                        setStatus('Permiso de microfono denegado', 'error');
                    } else if (event.error === 'language-not-supported') {
                        recognition.lang = 'es-ES';
                        setStatus('Idioma es-BO no disponible. Se aplico es-ES como fallback.', 'warning');
                    } else {
                        setStatus('No se pudo completar el reconocimiento de voz.', 'error');
                    }
                };

                recognition.onend = () => {
                    const hasText = commandField.value.trim() !== '';
                    setStatus(hasText ? 'Voz detectada correctamente' : 'Listo para escuchar', hasText ? 'success' : 'default');
                };
            }

            startButton.addEventListener('click', () => {
                if (!recognition) {
                    return;
                }

                commandField.value = '';
                finalTranscript = '';
                recognition.start();
            });

            stopButton.addEventListener('click', () => {
                if (!recognition) {
                    return;
                }

                recognition.stop();
                setStatus('Listo para escuchar', 'default');
            });

            applySearchButton.addEventListener('click', () => {
                const command = commandField.value.trim();

                if (command === '') {
                    setStatus('No hay un comando detectado para copiar.', 'warning');
                    return;
                }

                searchInput.value = command;

                if (tipoReporteSelect.value === '') {
                    setStatus('Selecciona un tipo de reporte antes de aplicar el comando.', 'warning');
                    return;
                }

                setStatus('El comando se copio al campo de busqueda. Revisa los filtros y genera el reporte.', 'success');
            });

            applyFiltersButton.addEventListener('click', () => {
                const rawCommand = commandField.value.trim();

                if (rawCommand === '') {
                    setStatus('No hay comando para interpretar.', 'warning');
                    return;
                }

                const command = normalizeText(rawCommand);
                const tipoReporteAnterior = tipoReporteSelect.value;
                let detectedTipoReporte = '';
                let detectedEstadoResultado = '';
                let detectedEstadoPago = '';
                let detectedEstadoAsistencia = '';
                let detectedEstadoInscripcion = '';
                let detectedGestion = null;
                let detectedCarrera = false;
                let detectedGrupo = false;
                let detectedMateria = false;
                let detectedToday = false;

                const mentionsAttendanceState = ['presente', 'retraso', 'ausente', 'justificado']
                    .some((term) => command.includes(term));

                if (command.includes('resultado')) {
                    detectedTipoReporte = 'resultados_admision';
                } else if (command.includes('cupo')) {
                    detectedTipoReporte = 'cupos_carrera';
                } else if (command.includes('nota')) {
                    detectedTipoReporte = 'notas_por_materia';
                } else if (command.includes('pago')) {
                    detectedTipoReporte = 'pagos_confirmados';
                } else if (command.includes('asistencia') || (command.includes('docente') && mentionsAttendanceState)) {
                    detectedTipoReporte = 'asistencia_docente';
                } else if (command.includes('postulante') || command.includes('inscritos')) {
                    detectedTipoReporte = 'postulantes_inscritos';
                } else if (command.includes('docente')) {
                    detectedTipoReporte = 'docentes_asignaciones';
                } else if (command.includes('grupo')) {
                    detectedTipoReporte = 'grupos_academicos';
                }

                if (command.includes('aprobado')) {
                    detectedEstadoResultado = 'APROBADO';
                } else if (command.includes('reprobado')) {
                    detectedEstadoResultado = 'REPROBADO';
                }

                if (command.includes('confirmado')) {
                    detectedEstadoPago = 'CONFIRMADO';
                } else if (command.includes('pendiente') && (detectedTipoReporte === 'pagos_confirmados' || tipoReporteAnterior === 'pagos_confirmados')) {
                    detectedEstadoPago = 'PENDIENTE';
                }

                if (command.includes('pendiente') && (detectedTipoReporte === 'resultados_admision' || tipoReporteAnterior === 'resultados_admision')) {
                    detectedEstadoResultado = 'PENDIENTE';
                }

                if (command.includes('presente')) {
                    detectedEstadoAsistencia = 'PRESENTE';
                } else if (command.includes('retraso')) {
                    detectedEstadoAsistencia = 'RETRASO';
                } else if (command.includes('ausente')) {
                    detectedEstadoAsistencia = 'AUSENTE';
                } else if (command.includes('justificado')) {
                    detectedEstadoAsistencia = 'JUSTIFICADO';
                }

                if (command.includes('pre registrado') || command.includes('preregistrado')) {
                    detectedEstadoInscripcion = 'PRE_REGISTRADO';
                } else if (command.includes('requisitos aprobados')) {
                    detectedEstadoInscripcion = 'REQUISITOS_APROBADOS';
                } else if (command.includes('pago pendiente')) {
                    detectedEstadoInscripcion = 'PAGO_PENDIENTE';
                } else if (command.includes('inscrito')) {
                    detectedEstadoInscripcion = 'INSCRITO';
                } else if (command.includes('observado')) {
                    detectedEstadoInscripcion = 'OBSERVADO';
                }

                detectedGestion = detectGestionDesdeComando(command, rawCommand);
                detectedToday = command.includes('hoy');

                detectedCarrera = selectOptionByVisibleText(carreraSelect, command);
                detectedGrupo = selectOptionByVisibleText(grupoSelect, command);
                detectedMateria = selectOptionByVisibleText(materiaSelect, command);

                const aplicoFiltro = Boolean(
                    detectedTipoReporte ||
                    detectedGestion ||
                    detectedEstadoResultado ||
                    detectedEstadoPago ||
                    detectedEstadoAsistencia ||
                    detectedEstadoInscripcion ||
                    detectedCarrera ||
                    detectedGrupo ||
                    detectedMateria ||
                    detectedToday
                );

                if (!aplicoFiltro) {
                    setStatus('No se detectaron filtros estructurados. Puedes usar el comando como busqueda.', 'warning');
                    return;
                }

                resetFiltrosParaComando();
                tipoReporteSelect.value = detectedTipoReporte || tipoReporteAnterior;

                if (detectedGestion) {
                    gestionSelect.value = detectedGestion;
                }

                if (detectedEstadoResultado) {
                    estadoResultadoSelect.value = detectedEstadoResultado;
                }

                if (detectedEstadoPago) {
                    estadoPagoSelect.value = detectedEstadoPago;
                }

                if (detectedEstadoAsistencia) {
                    estadoAsistenciaSelect.value = detectedEstadoAsistencia;
                }

                if (detectedEstadoInscripcion) {
                    estadoInscripcionSelect.value = detectedEstadoInscripcion;
                }

                if (detectedToday) {
                    const today = todayString();
                    fechaDesdeInput.value = today;
                    fechaHastaInput.value = today;
                }

                selectOptionByVisibleText(carreraSelect, command);
                selectOptionByVisibleText(grupoSelect, command);
                selectOptionByVisibleText(materiaSelect, command);

                if (searchInput) {
                    searchInput.value = '';
                }

                setStatus('Filtros actualizados desde el comando. Se limpiaron filtros anteriores para evitar cruces.', 'success');
            });

            const setButtonLoading = (button, isLoading, loadingLabel, defaultLabel) => {
                if (!button) {
                    return;
                }

                button.disabled = isLoading;
                button.textContent = isLoading ? loadingLabel : defaultLabel;
            };

            const interpretarConIA = async ({ autoSubmit = false } = {}) => {
                const command = commandField.value.trim();

                if (command === '') {
                    setStatus(
                        autoSubmit
                            ? 'Escribe o dicta un comando antes de generar con IA.'
                            : 'Escribe o dicta un comando antes de usar IA.',
                        'warning'
                    );
                    return;
                }

                const activeButton = autoSubmit ? aiGenerateButton : aiInterpretButton;
                setButtonLoading(
                    activeButton,
                    true,
                    autoSubmit ? 'Generando...' : 'Interpretando...',
                    autoSubmit ? 'Generar con IA' : 'Interpretar con IA'
                );

                setStatus(
                    autoSubmit ? 'Interpretando y generando reporte con IA...' : 'Interpretando con IA...',
                    'info'
                );

                try {
                    const response = await fetch(aiInterpretUrl, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                        },
                        body: JSON.stringify({ comando: command }),
                    });

                    const payload = await response.json();

                    if (!response.ok || !payload.success) {
                        setStatus(
                            autoSubmit
                                ? 'No se pudo generar con IA. Puedes usar Interpretar con IA o aplicar filtros manualmente.'
                                : (payload.message || 'No se pudo interpretar con IA. Puedes usar la interpretacion local.'),
                            'warning'
                        );
                        return;
                    }

                    applyFiltersFromBackend(payload.filters || {});

                    if (autoSubmit) {
                        if (!tipoReporteSelect.value) {
                            setStatus('No se pudo generar automaticamente porque la IA no detecto el tipo de reporte.', 'warning');
                            return;
                        }

                        setStatus('Filtros aplicados. Generando reporte en pantalla...', 'success');
                        reportForm?.submit();
                        return;
                    }

                    setStatus(payload.message || 'Comando interpretado con IA.', 'success');
                } catch (error) {
                    setStatus(
                        autoSubmit
                            ? 'No se pudo generar con IA. Puedes usar Interpretar con IA o aplicar filtros manualmente.'
                            : 'No se pudo interpretar con IA. Puedes usar la interpretacion local.',
                        'warning'
                    );
                } finally {
                    setButtonLoading(
                        activeButton,
                        false,
                        autoSubmit ? 'Generando...' : 'Interpretando...',
                        autoSubmit ? 'Generar con IA' : 'Interpretar con IA'
                    );
                }
            };

            aiInterpretButton.addEventListener('click', () => {
                interpretarConIA({ autoSubmit: false });
            });

            aiGenerateButton.addEventListener('click', () => {
                interpretarConIA({ autoSubmit: true });
            });
        });
    </script>
@endsection
