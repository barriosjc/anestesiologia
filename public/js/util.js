// Funciones utilitarias de confirmación con SweetAlert2 (sin dependencia de jQuery)

function confirmDelete(id, text = "Esta acción es irreversible.", onConfirm = null) {
    Swal.fire({
        title: '¿Confirma eliminar?',
        text: text,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            if (onConfirm) {
                onConfirm();
            } else {
                document.getElementById('delete-form-' + id).submit();
            }
        }
    })
}

function confirmJob(msg, metodo, correSubmit = true, text = "Esta acción es irreversible.", onConfirm = null) {
    Swal.fire({
        title: msg,
        text: text,
        icon: 'info',
        showCancelButton: true,
        confirmButtonColor: '#17a2b8',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Sí, confirmar',
        cancelButtonText: 'Cancelar',
        background: '#e3f2fd',
        customClass: {
            title: 'swal-title-custom',
            popup: 'swal-popup-custom',
        }
    }).then((result) => {
        if (result.isConfirmed) {
            if (onConfirm) {
                onConfirm();
            } else if (correSubmit) {
                document.getElementById(metodo).submit();
            } else {
                // en lugar de hacer submit se llama a una funcion de js, que hace una determinada cosa custom
                window[metodo]();
            }
        }
    })
}
