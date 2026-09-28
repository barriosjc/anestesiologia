<div class="container mt-4"
    x-data="{
        init() {
            window.addEventListener('confirmar-feriado', (e) => this.confirmarFeriado(e.detail));
            window.addEventListener('aviso-feriado', (e) => this.aviso(e.detail.mensaje));
        },
        aviso(mensaje) {
            Swal.fire({
                title: 'No se puede marcar esa celda',
                text: mensaje,
                icon: 'warning',
                confirmButtonColor: '#d97706',
                confirmButtonText: 'Entendido',
            });
        },
        confirmarFeriado({ fecha, nombre, fechaLegible, yaExiste }) {
            Swal.fire({
                title: yaExiste ? '¿Confirmar feriado?' : '¿Confirmar alta de feriado?',
                text: yaExiste
                    ? nombre + ' — ' + fechaLegible + '. Esa fecha ya está cargada como feriado.'
                    : nombre + ' — ' + fechaLegible + '. Solo se puede marcar 1 celda por feriado.',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#1782e0',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Sí, confirmar',
                cancelButtonText: 'Cancelar',
            }).then((resultado) => {
                if (resultado.isConfirmed) {
                    $wire.confirmarFeriado(fecha);
                } else {
                    $wire.descartarFeriado();
                }
            });
        },
    }">
    <div class="card">
        <div class="card-header d-flex align-items-center justify-content-between gap-2 bg-white flex-wrap">
            <h5 class="card-title mb-0"><i class="fa-solid fa-calendar-check me-2 text-primary"></i>Guardias de Médicos</h5>
        </div>

        <div class="card-body">
            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show py-2" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif
            @if (session('info'))
                <div class="alert alert-info alert-dismissible fade show py-2" role="alert">
                    {{ session('info') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif
            @if (session('warning'))
                <div class="alert alert-warning alert-dismissible fade show py-2" role="alert">
                    {{ session('warning') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-2">
                <div class="d-flex align-items-center gap-2">
                    <div class="btn-group">
                        <button type="button" class="btn btn-outline-primary btn-sm" wire:click="cambiarMes(-1)" title="Mes anterior">
                            <i class="fa-solid fa-chevron-left"></i>
                        </button>
                        <button type="button" class="btn btn-outline-primary btn-sm" wire:click="cambiarMes(1)"
                            title="Mes siguiente">
                            <i class="fa-solid fa-chevron-right"></i>
                        </button>
                    </div>
                    <h6 class="mb-0 text-capitalize">{{ $tituloMes }}</h6>
                    <button type="button" class="btn btn-link btn-sm px-1" wire:click="irAlMesActual">Hoy</button>
                </div>
                <div class="d-flex align-items-center gap-3">
                    <div class="form-check mb-0">
                        <input class="form-check-input" type="checkbox" id="incluirFeriados"
                            wire:model="incluirFeriados">
                        <label class="form-check-label small" for="incluirFeriados">Incluir feriados y findes</label>
                    </div>
                    <a class="btn btn-outline-dark btn-sm" target="_blank"
                        href="{{ route('guardias.pdf', ['anio' => $anio, 'mes' => $mes, 'incluir' => $incluirFeriados ? 1 : 0]) }}"
                        x-on:click.prevent="
                            const chk = document.getElementById('incluirFeriados');
                            const url = new URL(@js(route('guardias.pdf', ['anio' => $anio, 'mes' => $mes])));
                            url.searchParams.set('incluir', chk && chk.checked ? '1' : '0');
                            $el.href = url.href;
                            window.open(url.href, '_blank');
                        "
                        title="Generar PDF del mes">
                        <i class="fa-solid fa-file-pdf text-danger me-1"></i>PDF
                    </a>
                </div>
            </div>

            <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 p-2 mb-3 rounded border bg-light">
                <div class="d-flex flex-wrap align-items-center gap-2 flex-grow-1">
                    <div style="min-width: 240px; max-width: 360px; flex: 1 1 240px;" wire:ignore
                        x-data="{
                            choices: null,
                            init() {
                                this.choices = initChoices(this.$refs.medicoSelect, {
                                    onChange: (value) => { $wire.set('profesionalId', value) },
                                });
                            }
                        }">
                        <select x-ref="medicoSelect" class="form-select form-select-sm">
                            <option value="">-- Médico a asignar --</option>
                            @foreach ($medicos as $medico)
                                <option value="{{ $medico->id }}" @selected($profesionalId == $medico->id)>{{ $medico->nombre }}</option>
                            @endforeach
                        </select>
                    </div>
                    <button type="button" class="btn btn-primary btn-sm" wire:click="asignar"
                        @if (!$profesionalId) disabled @endif>
                        <i class="fa-solid fa-user-plus me-1"></i>Asignar
                    </button>
                    <div class="input-group input-group-sm" style="min-width: 200px; max-width: 260px;">
                        <input type="text" class="form-control" aria-label="Nombre del feriado"
                            wire:model.defer="nombreFeriado" placeholder="Nombre del feriado (al marcar)">
                        <button type="button" class="btn btn-outline-secondary" wire:click="descartarFeriado"
                            x-on:click="$el.closest('.input-group').querySelector('input').value = ''"
                            wire:loading.attr="disabled" title="Descartar el feriado marcado">
                            <i class="fa-solid fa-xmark"></i>
                        </button>
                    </div>
                    @if ($feriadoFecha)
                        <span class="badge text-bg-warning">
                            <i class="fa-solid fa-star me-1"></i>{{ $feriadoConfirmado ? 'Feriado cargado' : 'Feriado por confirmar' }}: {{ $feriadoNombre }} ({{ $feriadoFechaLegible }})
                        </span>
                    @endif
                </div>
                <button type="button" class="btn btn-success btn-sm" wire:click="confirmar">
                    <i class="fa-solid fa-check-double me-1"></i>Confirmar
                </button>
            </div>

            <div class="table-responsive guardias-cal">
                <table class="table table-bordered align-top text-center mb-0">
                    <thead>
                        <tr>
                            @foreach (['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado', 'Domingo'] as $i => $nombreDia)
                                <th class="guardias-cal-head {{ $i >= 5 ? 'text-danger' : '' }}">{{ $nombreDia }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($semanas as $semana)
                            <tr>
                                @foreach ($semana as $celda)
                                    @if (!$celda['enMes'])
                                        <td class="guardia-dia dia-fuera"></td>
                                    @else
                                        <td
                                            wire:key="g-{{ $celda['fecha'] }}"
                                            class="guardia-dia
                                                @if ($celda['esHoy']) dia-hoy @endif
                                                @if ($celda['marcado']) dia-marcado @endif
                                                @if ($celda['esFeriadoPendiente']) dia-feriado-pendiente @endif
                                                @if ($celda['desasignado']) dia-desasignado @endif
                                                @if ($celda['esFeriado'] && !$celda['color'] && !$celda['marcado'] && !$celda['desasignado']) dia-feriado @endif
                                                @if ($celda['esSabado'] || $celda['esDomingo']) dia-fin-semana @endif"
                                            style="@if ($celda['color'] && !$celda['marcado'] && !$celda['desasignado']) background-color: {{ $celda['color'] }}; @endif"
                                            role="button"
                                            wire:click="marcaDia('{{ $celda['fecha'] }}')">
                                            <div class="d-flex justify-content-between align-items-start pb-1">
                                                <span class="fw-semibold">{{ $celda['dia'] }}</span>
                                                @if ($celda['esHoy'])
                                                    <span class="small fw-bold text-primary">Hoy</span>
                                                @endif
                                            </div>
                                            @if ($celda['medicoNombre'] && !$celda['marcado'] && !$celda['desasignado'])
                                                <div class="fw-bold small text-break"
                                                    style="color: {{ $celda['letraColor'] }}; text-shadow: 0 1px 2px rgba(0,0,0,.35);">
                                                    {{ $celda['medicoNombre'] }}
                                                </div>
                                            @else
                                                @if ($celda['marcado'])
                                                    <div class="small fw-bold text-secondary">
                                                        @if ($celda['esFeriadoPendiente'])
                                                            <i class="fa-solid fa-star me-1"></i>{{ $celda['feriadoConfirmado'] ? 'Feriado' : 'Feriado?' }}
                                                        @else
                                                            <i class="fa-solid fa-pen me-1"></i>Marcado
                                                        @endif
                                                    </div>
                                                @elseif ($celda['desasignado'])
                                                    <div class="small fw-bold text-secondary"><i class="fa-solid fa-eraser me-1"></i>Sin asignar</div>
                                                @endif
                                                @if ($celda['feriadoNombre'])
                                                    <div class="small fw-semibold text-secondary">{{ $celda['feriadoNombre'] }}</div>
                                                @endif
                                            @endif
                                        </td>
                                    @endif
                                @endforeach
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>