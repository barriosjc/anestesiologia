<div class="container-fluid">
    <div class="row">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-header">
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <span id="card_title">
                            {{ __('Generar rendiciones') }}
                        </span>
                    </div>
                </div>
                <div class="card-body">
                    <livewire:consumos.rendiciones.rendiciones-filtro />
                    <livewire:consumos.rendiciones.rendiciones-tabla />
                </div>
            </div>
        </div>
    </div>
</div>
