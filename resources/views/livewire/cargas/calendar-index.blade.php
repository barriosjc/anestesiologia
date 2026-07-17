<div class="container mt-4"
    x-data="{
        calendarInstance: null,
        init() {
            this.calendarInstance = new FullCalendar.Calendar(this.$refs.calendarEl, {
                locale: 'es',
                initialView: 'dayGridMonth',
                events: @js($eventos),
                eventContent: function(arg) {
                    return {
                        html: `
                            <div style=\"white-space: normal; text-align: center;\">
                                <strong>${arg.event.title}</strong>
                            </div>
                        `
                    }
                },
                dateClick: (info) => {
                    $wire.set('fecha', info.dateStr);
                    new bootstrap.Modal(this.$refs.dateModal).show();
                }
            });
            this.calendarInstance.render();
        }
    }"
    x-on:calendario-guardado.window="
        calendarInstance.removeAllEvents();
        $event.detail.eventos.forEach(e => calendarInstance.addEvent(e));
        bootstrap.Modal.getInstance($refs.dateModal)?.hide();
    "
>
    <div class="row justify-content-center">
        <div class="col-10">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Calendario</h5>
                </div>
                <div class="card-body">
                    <div wire:ignore>
                        <div x-ref="calendarEl"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" x-ref="dateModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form wire:submit="guardar">
                    <div class="modal-header">
                        <h5 class="modal-title">Fecha seleccionada</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        La fecha seleccionada es: <span>{{ $fecha }}</span>
                    </div>
                    <div class="modal-body">
                        <label class="label-control">Observaciones</label>
                        <textarea rows="4" wire:model="observaciones" class="form-control"></textarea>
                    </div>
                    <div class="form-check form-switch pt-3" data-bs-toggle="tooltip" data-bs-placement="top"
                        title="Activa esta opción para cerrar la fecha con todos los partes del dia cargados o controlados">
                        <input class="form-check-input" type="checkbox" role="switch" wire:model="cerrado" id="cerrado">
                        <label class="form-check-label" for="cerrado">Cerrar fecha</label>
                    </div>
                    <div class="form-check form-switch pt-3" data-bs-toggle="tooltip" data-bs-placement="top"
                        title="Cancelar la Toma de esta fecha (solo si no hay partes cargados)">
                        <input class="form-check-input" type="checkbox" role="switch" wire:model="cancelar" id="cancelar">
                        <label class="form-check-label" for="cancelar">Cancelar toma de fecha</label>
                    </div>
                    @error('fecha')
                        <div class="text-danger small px-3 pt-2">{{ $message }}</div>
                    @enderror

                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary">Guardar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.js"></script>
@endpush
