@extends('layouts.main')

@section('contenido')
    <div class="container mt-4">
        <div class="row justify-content-center">
            <div class="col-10">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Calendario</h5>
                    </div>
                    <div class="card-body">
                        <div id="calendar"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal -->
    <div class="modal fade" id="dateModal" tabindex="-1" aria-labelledby="dateModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="{{ route('partes_cab.calendar.guardar') }}" method="POST">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title" id="dateModalLabel">Fecha seleccionada</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="user_id" value={{ Auth()->user()->id }}>
                        <input type="hidden" name="fecha" id="fecha">
                        La fecha seleccionada es: <span id="selectedDate"></span>
                    </div>
                    <div class="modal-body">
                        <label class="label-control">Observaciones</label>
                        <textarea rows="4" name="observaciones" class="form-control">  </textarea>
                    </div>
                    <div class="form-check form-switch pt-3" data-bs-toggle="tooltip" data-bs-placement="top"
                        title="Activa para cerrar la fecha con todos los partes del dia cargados o controlados">
                        <input class="form-check-input" type="checkbox" role="switch" name="cerrado" id="cerrado">
                        <label class="form-check-label" for="cerrado">Cerrar fecha</label>
                    </div>
                    <div class="form-check form-switch pt-3" data-bs-toggle="tooltip" data-bs-placement="top"
                        title="Cancelar la Toma de esta fecha (solo si no hay partes cargados)">
                        <input class="form-check-input" type="checkbox" role="switch" name="cancelar" id="cancelar">
                        <label class="form-check-label" for="cerrado">Cancelar toma de fecha</label>
                    </div>

                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary" data-bs-dismiss="modal">Guardar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.js"></script>
@endpush

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const calendarEl = document.getElementById('calendar');
        const calendar = new FullCalendar.Calendar(calendarEl, {
            locale: 'es',
            initialView: 'dayGridMonth',
            events: @json($calendar),
            eventContent: function(arg) {
                return {
                    html: `
                        <div style="white-space: normal; text-align: center;">
                            <strong>${arg.event.title}</strong>
                        </div>
                    `
                }
            },
            dateClick: function(info) {
                let modal = new bootstrap.Modal(document.getElementById('dateModal'));
                document.getElementById('selectedDate').textContent = info.dateStr;
                document.getElementById('fecha').value = info.dateStr;
                modal.show();
            }
        });
        calendar.render();
    });
</script>
