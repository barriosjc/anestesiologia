<div class="container-fluid">
    <div class="row">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-header">
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <span id="card_title">
                            {{ __('Cargas') }}
                        </span>
                        <div class="float-right">
                            <a href="{{ route('partes_cab.create') }}" class="btn btn-primary btn-sm float-right"
                                data-placement="left">
                                {{ __('Nuevo') }}
                            </a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <livewire:partes.parte-filtro />
                    <livewire:partes.parte-tabla />
                </div>
            </div>
        </div>
    </div>
</div>
