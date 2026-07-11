<div class="container-fluid">
    <div class="row">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-header">
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <span id="card_title">
                            {{ __('Auditoria Carga') }}
                        </span>
                    </div>
                </div>
                <div class="card-body">
                    <livewire:consumos.partes.consumo-partes-filtro />
                    <livewire:consumos.partes.consumo-partes-tabla />
                </div>
            </div>
        </div>
    </div>
</div>
