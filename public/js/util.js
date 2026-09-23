// Funciones utilitarias de confirmación con SweetAlert2 (sin dependencia de jQuery)

// Inicializador de Choices.js con búsqueda (renombrado de Tom Select)
function initChoices(selectEl, config = {}) {
    if (!selectEl) return null;
    if (selectEl._choices instanceof Choices) return selectEl._choices;

    const { onChange, ...choicesConfig } = config;

    const defaults = {
        searchEnabled: true,
        searchPlaceholderValue: 'Buscar...',
        itemSelectText: '',
        shouldSort: false,
        allowHTML: false,
        placeholder: false,
        removeItemButton: false,
        noChoicesText: 'No hay opciones disponibles',
        noResultsText: 'Sin resultados para la búsqueda',
    };

    const instance = new Choices(selectEl, Object.assign(defaults, choicesConfig));

    if (selectEl.classList.contains('form-select-sm')) {
        instance.containerOuter.element.classList.add('choices-sm');
    }

    if (typeof onChange === 'function') {
        selectEl.addEventListener('change', () => {
            onChange(instance.getValue(true), selectEl.value, instance);
        });
    }

    selectEl._choices = instance;
    return instance;
}

// Resetea visualmente un select de Choices.js
function resetChoices(instance, multi = false) {
    if (!instance) return;
    instance.clearInput();
    instance.hideDropdown();
    if (multi) {
        instance.removeActiveItems();
    } else {
        instance.setValue(['']);
        instance.clearInput();
    }
}

// ----------

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
