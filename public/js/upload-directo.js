/**
 * Subida directa de archivos por XHR contra una ruta de Laravel.
 *
 * Reemplaza la subida temporal de Livewire: el archivo va del navegador al
 * servidor en un unico POST multipart, se muestra el progreso real y los
 * errores del servidor (validacion, tamano, permisos, sesion) se muestran
 * tal cual los devuelve la ruta.
 *
 * Uso: en el contenedor (normalmente el modal) agregar
 *   data-upload-url="{{ route('...') }}"
 *   data-upload-evento="evento-livewire"      (opcional, se emite al terminar)
 *   data-upload-max-mb="100"                  (opcional, default 100)
 *   data-upload-extensiones="pdf,csv,xlsx"    (opcional, default pdf)
 * y dentro del contenedor:
 *   [data-upload-input]     input file
 *   [data-upload-submit]    boton que inicia la subida
 *   [data-upload-close]     boton para cerrar (se deshabilita mientras sube)
 *   [data-upload-error]     caja de error
 *   [data-upload-progress]  caja del progreso
 *   [data-upload-bar]       barra (width en %)
 *   [data-upload-percent]   texto del porcentaje
 *   [data-upload-status]    texto de estado
 *   [data-upload-name]      nombre del archivo elegido
 */
(function () {
    'use strict';

    /**
     * Cabecera CSRF: se usa el token del meta (layouts.main) y, si no existe,
     * la cookie XSRF-TOKEN (que Laravel espera en X-XSRF-TOKEN, encriptada).
     */
    function cabeceraCsrf() {
        var meta = document.querySelector('meta[name="csrf-token"]');
        if (meta && meta.getAttribute('content')) {
            return { 'X-CSRF-TOKEN': meta.getAttribute('content') };
        }

        var cookie = document.cookie.match(/(?:^|;\s*)XSRF-TOKEN=([^;]+)/);
        if (cookie) {
            return { 'X-XSRF-TOKEN': decodeURIComponent(cookie[1]) };
        }

        return {};
    }

    function inicializar(contenedor) {
        if (contenedor.dataset.uploadListo === '1') {
            return;
        }
        contenedor.dataset.uploadListo = '1';

        var config = {
            url: contenedor.dataset.uploadUrl,
            evento: contenedor.dataset.uploadEvento || '',
            maxMb: parseFloat(contenedor.dataset.uploadMaxMb || '100') || 100,
            extensiones: (contenedor.dataset.uploadExtensiones || 'pdf')
                .split(',')
                .map(function (extension) { return extension.trim().toLowerCase(); })
                .filter(Boolean),
        };

        var xhrActual = null;

        function el(nombre) {
            return contenedor.querySelector('[data-upload-' + nombre + ']');
        }

        function ocultarError() {
            var caja = el('error');
            if (!caja) return;
            caja.textContent = '';
            caja.classList.add('d-none');
        }

        function mostrarError(mensaje) {
            var caja = el('error');
            if (!caja) return;
            caja.textContent = mensaje;
            caja.classList.remove('d-none');
        }

        function mostrarProgreso(porcentaje, estado) {
            var caja = el('progress');
            var barra = el('bar');
            var numero = el('percent');
            var texto = el('status');

            if (caja) caja.classList.remove('d-none');
            if (barra) {
                barra.style.width = porcentaje + '%';
                barra.setAttribute('aria-valuenow', String(porcentaje));
            }
            if (numero) numero.textContent = porcentaje + '%';
            if (texto && estado) texto.textContent = estado;
        }

        function ocultarProgreso() {
            var caja = el('progress');
            if (caja) caja.classList.add('d-none');

            var barra = el('bar');
            if (barra) barra.style.width = '0%';

            var numero = el('percent');
            if (numero) numero.textContent = '0%';

            var texto = el('status');
            if (texto) texto.textContent = 'Subiendo archivo...';
        }

        function bloquear(bloqueado) {
            ['input', 'submit', 'close'].forEach(function (nombre) {
                var nodo = el(nombre);
                if (nodo) nodo.disabled = bloqueado;
            });
        }

        function limpiar() {
            if (xhrActual) {
                xhrActual.abort();
                xhrActual = null;
            }

            var input = el('input');
            if (input) input.value = '';

            var nombre = el('name');
            if (nombre) {
                nombre.textContent = '';
                nombre.classList.add('d-none');
            }

            ocultarError();
            ocultarProgreso();
            bloquear(false);

            var submit = el('submit');
            if (submit) submit.disabled = true;
        }

        function validarArchivo(archivo) {
            var extension = (archivo.name.split('.').pop() || '').toLowerCase();

            if (config.extensiones.length > 0 && config.extensiones.indexOf(extension) === -1) {
                return 'Formato no permitido. Usá: ' + config.extensiones.join(', ').toUpperCase() + '.';
            }

            if (archivo.size > config.maxMb * 1024 * 1024) {
                return 'El archivo pesa ' + (archivo.size / 1024 / 1024).toFixed(1)
                    + ' MB y el máximo es ' + config.maxMb + ' MB.';
            }

            return null;
        }

        function mensajeDeError(estadoHttp, datos) {
            if (datos && datos.message) {
                return datos.message;
            }

            if (estadoHttp === 413) {
                return 'El archivo es demasiado grande para el servidor (máximo de la aplicación: ' + config.maxMb + ' MB).';
            }
            if (estadoHttp === 419) {
                return 'La sesión expiró. Recargá la página e intentá de nuevo.';
            }
            if (estadoHttp === 401 || estadoHttp === 403) {
                return 'No tenés permisos para subir archivos.';
            }

            return 'El servidor respondió con un error (' + estadoHttp + ').';
        }

        function cerrarModal() {
            if (!window.bootstrap || !window.bootstrap.Modal) return;

            var instancia = window.bootstrap.Modal.getInstance(contenedor);
            if (instancia) instancia.hide();
        }

        function avisar(mensaje) {
            if (!window.Swal) return;

            window.Swal.fire({
                toast: true,
                position: 'top-end',
                icon: 'success',
                title: mensaje,
                showConfirmButton: false,
                timer: 4000,
                timerProgressBar: true,
            });
        }

        function fallar(mensaje) {
            ocultarProgreso();
            bloquear(false);
            mostrarError(mensaje);

            var submit = el('submit');
            if (submit) submit.disabled = false;
        }

        function terminar(datos) {
            mostrarProgreso(100, datos.message || 'Listo.');

            if (datos.redirect) {
                window.location.href = datos.redirect;
                return;
            }

            if (datos.ok === false) {
                fallar(datos.message || 'No se pudo procesar el archivo.');
                return;
            }

            cerrarModal();
            avisar(datos.message || 'Archivo procesado.');

            if (config.evento && window.Livewire && typeof window.Livewire.dispatch === 'function') {
                window.Livewire.dispatch(config.evento);
            }
        }

        function subir() {
            var input = el('input');
            var archivo = input && input.files ? input.files[0] : null;

            if (!archivo) {
                mostrarError('Seleccioná un archivo.');
                return;
            }

            ocultarError();
            bloquear(true);
            mostrarProgreso(0, 'Subiendo archivo...');

            var datos = new FormData();
            datos.append('archivo', archivo);

            xhrActual = new XMLHttpRequest();
            xhrActual.open('POST', config.url, true);

            var cabeceras = cabeceraCsrf();
            Object.keys(cabeceras).forEach(function (nombre) {
                xhrActual.setRequestHeader(nombre, cabeceras[nombre]);
            });

            xhrActual.setRequestHeader('Accept', 'application/json');
            xhrActual.setRequestHeader('X-Requested-With', 'XMLHttpRequest');

            xhrActual.upload.addEventListener('progress', function (evento) {
                if (!evento.lengthComputable) return;

                var porcentaje = Math.round((evento.loaded / evento.total) * 100);
                mostrarProgreso(Math.min(porcentaje, 99), 'Subiendo archivo... ' + porcentaje + '%');
            });

            xhrActual.upload.addEventListener('load', function () {
                mostrarProgreso(100, 'Archivo recibido. Procesando en el servidor...');
            });

            xhrActual.addEventListener('load', function () {
                var respuesta = null;
                try {
                    respuesta = JSON.parse(xhrActual.responseText);
                } catch (error) {
                    respuesta = null;
                }

                if (xhrActual.status >= 200 && xhrActual.status < 300 && respuesta) {
                    terminar(respuesta);
                    return;
                }

                fallar(mensajeDeError(xhrActual.status, respuesta));
            });

            xhrActual.addEventListener('error', function () {
                fallar('No se pudo conectar con el servidor. Verificá que el servidor esté funcionando.');
            });

            xhrActual.addEventListener('abort', function () {
                ocultarProgreso();
                bloquear(false);
            });

            xhrActual.send(datos);
        }

        contenedor.addEventListener('show.bs.modal', limpiar);

        contenedor.addEventListener('change', function (evento) {
            if (!evento.target.matches('[data-upload-input]')) return;

            var archivo = evento.target.files && evento.target.files[0];
            var submit = el('submit');
            var nombre = el('name');

            ocultarError();
            ocultarProgreso();

            if (!archivo) {
                if (submit) submit.disabled = true;
                if (nombre) nombre.classList.add('d-none');
                return;
            }

            var error = validarArchivo(archivo);

            if (error) {
                evento.target.value = '';
                if (submit) submit.disabled = true;
                if (nombre) nombre.classList.add('d-none');
                mostrarError(error);
                return;
            }

            if (nombre) {
                nombre.textContent = 'Archivo seleccionado: ' + archivo.name
                    + ' (' + (archivo.size / 1024 / 1024).toFixed(2) + ' MB)';
                nombre.classList.remove('d-none');
            }

            if (submit) submit.disabled = false;
        });

        contenedor.addEventListener('click', function (evento) {
            var boton = evento.target.closest('[data-upload-submit]');

            if (!boton || boton.disabled) return;

            evento.preventDefault();
            subir();
        });
    }

    function iniciar() {
        document.querySelectorAll('[data-upload-url]').forEach(inicializar);
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', iniciar);
    } else {
        iniciar();
    }

    document.addEventListener('livewire:init', iniciar);
    document.addEventListener('livewire:navigated', iniciar);
})();
