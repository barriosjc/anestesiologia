@extends('layouts.main-back')

@section('content')
    <!-- Orders list-->
    <!-- Light table with striped rows -->
    <div class="page-title-overlap bg-dark pt-4">
        <div class="container d-lg-flex justify-content-between py-2 py-lg-3">
            <div class="order-lg-1 pe-lg-4 text-center text-lg-start">
                <h1 class="h3 text-light mb-0">Tickets</h1>
            </div>
        </div>
    </div>
    <div class="container pb-5 mb-2 mb-md-4">
        <div class="row">
            <section class="col-lg-12">
                <div class="d-flex justify-content-between align-items-center pt-lg-2 pb-4 pb-lg-5 mb-lg-3">
                    <div class="d-flex order-lg-1 pe-lg-4 text-center text-lg-start">
                        <h4 class="text-light mb-0" style="margin-right: 20px;">Product: {{ $orden->categoria_desc }} /
                            {{ $orden->nombre }} | Order: {{ $orden->orden_id }} |
                            Client: {{ $orden->company }}</h4>
                        <a class="btn btn-primary btn-sm d-lg-inline-block align-content-center"
                            href="{{ route('back.orders.detail', [$orden->orden_id, $activo]) }}"><i
                                class="ci-arrow-left me-2"></i>Back</a>
                    </div>
                </div>
                <!-- Orders list-->
                <div class="card-body">
                    <div class="table-responsive">
                        @foreach ($tickets as $item)
                            @if ($item->cli_imp === 'i')
                            <div class="d-flex align-items-start border-bottom pt-4 mt-4 ps-5">
                            @else
                            <div class="d-flex align-items-start pt-4 mt-4 pb-4 border-bottom">
                            @endif
                                <div class="ps-3">
                                    <h6 class="fs-md mb-2">{{ $item->name }}</h6>
                                    <p class="fs-md mb-1">{{ $item->insidente }}</p><span class="fs-ms text-muted"><i
                                            class="ci-time align-middle me-2"></i>
                                        {{ $item->created_at }}</span>
                                    @if ($item->cut_path)
                                        <span>CUT FILE: </span>
                                        {{-- para crear el nombre de archivo en base a lo pedido                           --}}
                                        @php
                                            $cut_name_parts = ['cut', $item->cli_id, $item->cat_desc, $item->nombre, $item->width_feet > 0 ? $item->width_feet : '', $item->width_inches > 0 ? $item->width_inches : '', $item->height_feet > 0 ? $item->height_feet : '', $item->height_inches > 0 ? $item->height_inches : ''];

                                            $cut_name_parts = array_filter($cut_name_parts);
                                            $original_extension = explode('.', $item->cut_path);
                                            $ext = $original_extension[1];
                                            $cut_name = implode('_', $cut_name_parts);
                                            $cut_name = $cut_name . '.' . $ext;
                                        @endphp
                                        <a class="btn btn-sm-80 btn-secondary btn-icon"
                                            href="{{ asset('storage/clientes/' . $item->cut_path) }}"
                                            download="{{ $cut_name }}">
                                            <i class="text-body ci-download"></i>
                                        </a>
                                    @endif
                                    @if ($item->print_path)
                                        <span>PRINT FILE: </span>
                                        @php
                                            $print_name_parts = ['print', $item->cli_id, $item->cat_desc, $item->nombre, $item->width_feet > 0 ? $item->width_feet : '', $item->width_inches > 0 ? $item->width_inches : '', $item->height_feet > 0 ? $item->height_feet : '', $item->height_inches > 0 ? $item->height_inches : ''];

                                            $print_name_parts = array_filter($print_name_parts);
                                            $original_extension = explode('.', $item->print_path);
                                            $ext = $original_extension[1];
                                            $print_name = implode('_', $print_name_parts);
                                            $print_name = $print_name . '.' . $ext;
                                        @endphp
                                        <a class="btn btn-sm-80 btn-secondary btn-icon"
                                            href="{{ asset('storage/clientes/' . $item->print_path) }}"
                                            download="{{ $print_name }}">
                                            <i class="text-body ci-download"></i>
                                        </a>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                        @if ($tickets->count() == 0)
                            <h3 class="h5 mt-2 pt-4 pb-2">Aun no se han ingresado mensajes</h3>
                            <hr>
                        @endif
                    </div>
                    <!-- Leave message-->
                    <form method="POST" action="{{ route('back.tikets.save') }}" role="form"
                        enctype="multipart/form-data">
                        {{-- {{ method_field('PATCH') }} --}}
                        @csrf
                        <input type="hidden" name="ordenes_compras_det_id" value="{{ $detalle_id }}">
                        <h3 class="h5 mt-2 pt-4 pb-2">Leave a Message</h3>
                        <div class="mb-3">
                            <textarea class="form-control" rows="8" placeholder="Write your message here..." name='mensaje' required></textarea>
                            <div class="invalid-tooltip">Please write the message!</div>
                        </div>

                        <div class="d-flex flex-wrap justify-content-between align-items-center">
                            <button class="btn btn-primary my-2" type="submit">Submit message</button>
                        </div>
                    </form>
                </div>
            </section>
        </div>
    </div>
@endsection
