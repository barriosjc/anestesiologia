let token = document.querySelector('input[name="_token"]').value;

$("#estadoCambio").on('change', function() {
    let value = $(this).val();
    // si se selecciona aRefactuar
    if (value === '7') {
        $("#div_refac").css('display', 'block');
        $("#div_refac2").css('display', 'block');
        $("#div_esconde").css('display', 'none');
    } else {
        // Si no, mantener el display none
        $("#div_refac").css('display', 'none');
        $("#div_refac2").css('display', 'none');
        $("#div_esconde").css('display', 'block');
        // $(this).prop('selectedIndex', 0);
    }
})

function revalorizarPartesJS() {
    let selectedItems = [];
    $('.ck_item:checked').each(function() {
        let consumo_det_id = $(this).val();
        let parte_id = $(this).data('parte-id');
        selectedItems.push({
            consumo_det_id: consumo_det_id,
            parte_id: parte_id
        });
    });
    let periodo = $('#periodo_revalorizar').val();

    if (selectedItems.length === 0 || !periodo) {
        alert('Debe seleccionar al menos una fila y un periodo.');
        return;
    }
    $.post(window.routes.revalorizar, {
        _token: token,
        selected_ids: selectedItems,
        periodo_revalorizar: periodo
    }, function(response) {
        const alertDiv = '<div class="alert alert-success py-2">' + response.success + '</div>';
        $('#alert-container').html(alertDiv);
    }).fail(function(response) {
        let errorMessages = 'Ocurrió un error inesperado.';
        if (response.responseJSON && response.responseJSON.errors) {
            errorMessages = '';
            for (const [field, messages] of Object.entries(response.responseJSON.errors)) {
                errorMessages += "<li>" + messages + "</li>";
            }
        }
        const alertDiv = '<div class="alert alert-danger py-2"><ul class="no-bullets">' + errorMessages +
            '</ul></div>';
        $('#alert-container').html(alertDiv);
    }).always(function() {
        const ytop = $('#alert-container').offset().top;
        window.scrollTo({
            top: ytop - 300,
            behavior: 'smooth'
        });
    })
};

window.revalorizarPartesJS = revalorizarPartesJS;

$(document).ready(function() {
    $('[data-bs-toggle="tooltip"]').tooltip();

    $('.select2').select2({
        placeholder: "-- Seleccione --",
        allowClear: true
    });

    // marca todos las filas de la tabla
    document.getElementById('ck_todo').addEventListener('change', function() {
        var checkboxes = document.querySelectorAll('.ck_item');
        checkboxes.forEach(function(checkbox) {
            checkbox.checked = document.getElementById('ck_todo').checked;
        });
    });

    // hacer submit de filas marcadas
    $('#generate_rendicion_btn').on('click', function() {
        let selectedItems = [];
        $('.ck_item:checked').each(function() {
            let consumo_det_id = $(this).val();
            let parte_id = $(this).data('parte-id');
            selectedItems.push({
                consumo_det_id: consumo_det_id,
                parte_id: parte_id
            });
        });

        let periodo = $('#periodo').val();

        if (selectedItems.length === 0 || !periodo) {
            alert('Debe seleccionar al menos una fila y un periodo.');
            return;
        }

        $('<input>').attr({
            type: 'hidden',
            name: 'selected_ids',
            value: JSON.stringify(selectedItems)
        }).appendTo('#generate_rendicion_form');

        $('#generate_rendicion_form').submit();
    });

    // Enviar filas seleccionadas para cambiar el estado
    $('#cambio_estados_btn').on('click', function() {
        let selectedItems = [];
        $('.ck_item:checked').each(function() {
            let consumo_det_id = $(this).val();
            let parte_id = $(this).data('parte-id');
            selectedItems.push({
                consumo_det_id: consumo_det_id,
                parte_id: parte_id
            });
        });

        let estadoCambio = $('#estadoCambio').val();
        let periodo_refac = $('#periodo_refac').val();
        let obs_refac = $('#obs_refac').val();

        $.ajax({
            url: window.routes.cambiarEstado,
            method: 'POST',
            data: {
                _token: token,
                selected_ids: selectedItems,
                estadoCambio: estadoCambio,
                periodo_refac: periodo_refac,
                obs_refac: obs_refac
            },
            success: function(response) {
                const alertDiv = '<div class="alert alert-success py-2">' + response
                    .success + '</div>';
                $('#alert-container').html(alertDiv);
            },
            error: function(response) {
                let errorMessages = '';
                console.log(response.responseJSON.error)
                if (response.responseJSON && response.responseJSON.errors) {
                    for (const [field, messages] of Object.entries(response.responseJSON
                            .errors)) {
                        errorMessages += "<li>" + messages + "</li>";
                    }
                } else if (response.responseJSON.error) {
                    errorMessages += "<li>" + response.responseJSON.error + "</li>";
                } else {
                    errorMessages = 'Ocurrió un error inesperado.';
                }

                const alertDiv =
                    '<div class="alert alert-danger py-2"><ul class="no-bullets">' +
                    errorMessages + '</ul></div>';
                $('#alert-container').html(alertDiv);
            },
            complete: function() {
                const ytop = $('#alert-container').offset().top;
                window.scrollTo({
                    top: ytop - 300,
                    behavior: 'smooth'
                });
            }
        });
    });

    // agregar nuevo consumo
    $('#btnAgregar').on('click', function() {
        let selectedItems = [];
        $('.ck_item:checked').each(function() {
            let consumo_det_id = $(this).val();
            let parte_id = $(this).data('parte-id');
            selectedItems.push({
                consumo_det_id: consumo_det_id,
                parte_id: parte_id
            });
        });

        let estadoAgregar = $('#estadoAgregar').val();
        let periodoAgregar = $('#periodoAgregar').val();
        let obsAgregar = $('#obsAgregar').val();
        let valorAgregar = $('#valorAgregar').val();

        $.ajax({
            url: window.routes.agregarConsumo,
            method: 'POST',
            data: {
                _token: token,
                selected_ids: selectedItems,
                estadoAgregar: estadoAgregar,
                periodoAgregar: periodoAgregar,
                obsAgregar: obsAgregar,
                valorAgregar: valorAgregar
            },
            success: function(response) {
                const alertDiv = '<div class="alert alert-success py-2">' + response
                    .success + '</div>';
                $('#alert-container').html(alertDiv);
            },
            error: function(response) {
                let errorMessages = '';
                console.log(response.responseJSON.error)
                if (response.responseJSON && response.responseJSON.errors) {
                    for (const [field, messages] of Object.entries(response.responseJSON
                            .errors)) {
                        errorMessages += "<li>" + messages + "</li>";
                    }
                } else if (response.responseJSON.error) {
                    errorMessages += "<li>" + response.responseJSON.error + "</li>";
                } else {
                    errorMessages = 'Ocurrió un error inesperado.';
                }

                const alertDiv =
                    '<div class="alert alert-danger py-2"><ul class="no-bullets">' +
                    errorMessages + '</ul></div>';
                $('#alert-container').html(alertDiv);
            },
            complete: function() {
                const ytop = $('#alert-container').offset().top;
                window.scrollTo({
                    top: ytop - 300,
                    behavior: 'smooth'
                });
            }
        });
    });

    // agregar nuevo consumo y diferencia
    $('#btnAgregaryDiff').on('click', function() {
        let selectedItems = [];
        $('.ck_item:checked').each(function() {
            let consumo_det_id = $(this).val();
            let parte_id = $(this).data('parte-id');
            selectedItems.push({
                consumo_det_id: consumo_det_id,
                parte_id: parte_id
            });
        });
        let estadoAgregar = $('#estadoAgregaryDiff').val();
        let periodoAgregar = $('#periodoAgregaryDiff').val();
        let obsAgregar = $('#obsAgregaryDiff').val();
        let valorAgregar = $('#valorAgregaryDiff').val();
        let refacturar = $('input[name="refacturaryDiff"]:checked').val();

        $.ajax({
            url: window.routes.agregarConsumoConDiferencia,
            method: 'POST',
            data: {
                _token: token,
                selected_ids: selectedItems,
                estadoAgregar: estadoAgregar,
                periodoAgregar: periodoAgregar,
                obsAgregar: obsAgregar,
                valorAgregar: valorAgregar,
                refacturar: refacturar
            },
            success: function(response) {
                const alertDiv = '<div class="alert alert-success py-2">' + response
                    .success + '</div>';
                $('#alert-container').html(alertDiv);
            },
            error: function(response) {
                let errorMessages = '';
                console.log(response.responseJSON.error)
                if (response.responseJSON && response.responseJSON.errors) {
                    for (const [field, messages] of Object.entries(response.responseJSON
                            .errors)) {
                        errorMessages += "<li>" + messages + "</li>";
                    }
                } else if (response.responseJSON.error) {
                    errorMessages += "<li>" + response.responseJSON.error + "</li>";
                } else {
                    errorMessages = 'Ocurrió un error inesperado.';
                }

                const alertDiv =
                    '<div class="alert alert-danger py-2"><ul class="no-bullets">' +
                    errorMessages + '</ul></div>';
                $('#alert-container').html(alertDiv);
            },
            complete: function() {
                const ytop = $('#alert-container').offset().top;
                window.scrollTo({
                    top: ytop - 300,
                    behavior: 'smooth'
                });
            }
        });
    });
});

document.addEventListener('DOMContentLoaded', () => {
    const contenedorGrilla = document.getElementById('contenedor-grilla');
    const columnasExtra = document.querySelectorAll('.columna-extra');

    const ajustarColumnas = () => {
        const anchoGrilla = contenedorGrilla.offsetWidth;
        // console.log("paso por aca: ",anchoGrilla);
        if (anchoGrilla < 1250) {
            columnasExtra.forEach(col => col.classList.add('d-none'));
        } else {
            columnasExtra.forEach(col => col.classList.remove('d-none'));
        }
    };

    //             window.addEventListener('resize', () => {
    //     console.log('Se detectó un cambio en el tamaño de la ventana');
    // });

    window.addEventListener('resize', ajustarColumnas);
    const observer = new ResizeObserver(ajustarColumnas);
    observer.observe(contenedorGrilla);
    ajustarColumnas();
});
