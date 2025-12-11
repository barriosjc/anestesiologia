/******/ (() => { // webpackBootstrap
var __webpack_exports__ = {};
/*!*************************************!*\
  !*** ./resources/js/rendiciones.js ***!
  \*************************************/
function _slicedToArray(arr, i) { return _arrayWithHoles(arr) || _iterableToArrayLimit(arr, i) || _unsupportedIterableToArray(arr, i) || _nonIterableRest(); }
function _nonIterableRest() { throw new TypeError("Invalid attempt to destructure non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); }
function _unsupportedIterableToArray(o, minLen) { if (!o) return; if (typeof o === "string") return _arrayLikeToArray(o, minLen); var n = Object.prototype.toString.call(o).slice(8, -1); if (n === "Object" && o.constructor) n = o.constructor.name; if (n === "Map" || n === "Set") return Array.from(o); if (n === "Arguments" || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(n)) return _arrayLikeToArray(o, minLen); }
function _arrayLikeToArray(arr, len) { if (len == null || len > arr.length) len = arr.length; for (var i = 0, arr2 = new Array(len); i < len; i++) arr2[i] = arr[i]; return arr2; }
function _iterableToArrayLimit(arr, i) { var _i = null == arr ? null : "undefined" != typeof Symbol && arr[Symbol.iterator] || arr["@@iterator"]; if (null != _i) { var _s, _e, _x, _r, _arr = [], _n = !0, _d = !1; try { if (_x = (_i = _i.call(arr)).next, 0 === i) { if (Object(_i) !== _i) return; _n = !1; } else for (; !(_n = (_s = _x.call(_i)).done) && (_arr.push(_s.value), _arr.length !== i); _n = !0); } catch (err) { _d = !0, _e = err; } finally { try { if (!_n && null != _i["return"] && (_r = _i["return"](), Object(_r) !== _r)) return; } finally { if (_d) throw _e; } } return _arr; } }
function _arrayWithHoles(arr) { if (Array.isArray(arr)) return arr; }
var token = document.querySelector('input[name="_token"]').value;
$("#estadoCambio").on('change', function () {
  var value = $(this).val();
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
});
function revalorizarPartesJS() {
  var selectedItems = [];
  $('.ck_item:checked').each(function () {
    var consumo_det_id = $(this).val();
    var parte_id = $(this).data('parte-id');
    selectedItems.push({
      consumo_det_id: consumo_det_id,
      parte_id: parte_id
    });
  });
  var periodo = $('#periodo_revalorizar').val();
  if (selectedItems.length === 0 || !periodo) {
    alert('Debe seleccionar al menos una fila y un periodo.');
    return;
  }
  $.post(window.routes.revalorizar, {
    _token: token,
    selected_ids: selectedItems,
    periodo_revalorizar: periodo
  }, function (response) {
    var alertDiv = '<div class="alert alert-success py-2">' + response.success + '</div>';
    $('#alert-container').html(alertDiv);
  }).fail(function (response) {
    var errorMessages = 'Ocurrió un error inesperado.';
    if (response.responseJSON && response.responseJSON.errors) {
      errorMessages = '';
      for (var _i = 0, _Object$entries = Object.entries(response.responseJSON.errors); _i < _Object$entries.length; _i++) {
        var _Object$entries$_i = _slicedToArray(_Object$entries[_i], 2),
          field = _Object$entries$_i[0],
          messages = _Object$entries$_i[1];
        errorMessages += "<li>" + messages + "</li>";
      }
    }
    var alertDiv = '<div class="alert alert-danger py-2"><ul class="no-bullets">' + errorMessages + '</ul></div>';
    $('#alert-container').html(alertDiv);
  }).always(function () {
    var ytop = $('#alert-container').offset().top;
    window.scrollTo({
      top: ytop - 300,
      behavior: 'smooth'
    });
  });
}
;
window.revalorizarPartesJS = revalorizarPartesJS;
$(document).ready(function () {
  $('[data-bs-toggle="tooltip"]').tooltip();
  $('.select2').select2({
    placeholder: "-- Seleccione --",
    allowClear: true
  });

  // marca todos las filas de la tabla
  document.getElementById('ck_todo').addEventListener('change', function () {
    var checkboxes = document.querySelectorAll('.ck_item');
    checkboxes.forEach(function (checkbox) {
      checkbox.checked = document.getElementById('ck_todo').checked;
    });
  });

  // hacer submit de filas marcadas
  $('#generate_rendicion_btn').on('click', function () {
    var selectedItems = [];
    $('.ck_item:checked').each(function () {
      var consumo_det_id = $(this).val();
      var parte_id = $(this).data('parte-id');
      selectedItems.push({
        consumo_det_id: consumo_det_id,
        parte_id: parte_id
      });
    });
    var periodo = $('#periodo').val();
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
  $('#cambio_estados_btn').on('click', function () {
    var selectedItems = [];
    $('.ck_item:checked').each(function () {
      var consumo_det_id = $(this).val();
      var parte_id = $(this).data('parte-id');
      selectedItems.push({
        consumo_det_id: consumo_det_id,
        parte_id: parte_id
      });
    });
    var estadoCambio = $('#estadoCambio').val();
    var periodo_refac = $('#periodo_refac').val();
    var obs_refac = $('#obs_refac').val();
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
      success: function success(response) {
        var alertDiv = '<div class="alert alert-success py-2">' + response.success + '</div>';
        $('#alert-container').html(alertDiv);
      },
      error: function error(response) {
        var errorMessages = '';
        console.log(response.responseJSON.error);
        if (response.responseJSON && response.responseJSON.errors) {
          for (var _i2 = 0, _Object$entries2 = Object.entries(response.responseJSON.errors); _i2 < _Object$entries2.length; _i2++) {
            var _Object$entries2$_i = _slicedToArray(_Object$entries2[_i2], 2),
              field = _Object$entries2$_i[0],
              messages = _Object$entries2$_i[1];
            errorMessages += "<li>" + messages + "</li>";
          }
        } else if (response.responseJSON.error) {
          errorMessages += "<li>" + response.responseJSON.error + "</li>";
        } else {
          errorMessages = 'Ocurrió un error inesperado.';
        }
        var alertDiv = '<div class="alert alert-danger py-2"><ul class="no-bullets">' + errorMessages + '</ul></div>';
        $('#alert-container').html(alertDiv);
      },
      complete: function complete() {
        var ytop = $('#alert-container').offset().top;
        window.scrollTo({
          top: ytop - 300,
          behavior: 'smooth'
        });
      }
    });
  });

  // agregar nuevo consumo
  $('#btnAgregar').on('click', function () {
    var selectedItems = [];
    $('.ck_item:checked').each(function () {
      var consumo_det_id = $(this).val();
      var parte_id = $(this).data('parte-id');
      selectedItems.push({
        consumo_det_id: consumo_det_id,
        parte_id: parte_id
      });
    });
    var estadoAgregar = $('#estadoAgregar').val();
    var periodoAgregar = $('#periodoAgregar').val();
    var obsAgregar = $('#obsAgregar').val();
    var valorAgregar = $('#valorAgregar').val();
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
      success: function success(response) {
        var alertDiv = '<div class="alert alert-success py-2">' + response.success + '</div>';
        $('#alert-container').html(alertDiv);
      },
      error: function error(response) {
        var errorMessages = '';
        console.log(response.responseJSON.error);
        if (response.responseJSON && response.responseJSON.errors) {
          for (var _i3 = 0, _Object$entries3 = Object.entries(response.responseJSON.errors); _i3 < _Object$entries3.length; _i3++) {
            var _Object$entries3$_i = _slicedToArray(_Object$entries3[_i3], 2),
              field = _Object$entries3$_i[0],
              messages = _Object$entries3$_i[1];
            errorMessages += "<li>" + messages + "</li>";
          }
        } else if (response.responseJSON.error) {
          errorMessages += "<li>" + response.responseJSON.error + "</li>";
        } else {
          errorMessages = 'Ocurrió un error inesperado.';
        }
        var alertDiv = '<div class="alert alert-danger py-2"><ul class="no-bullets">' + errorMessages + '</ul></div>';
        $('#alert-container').html(alertDiv);
      },
      complete: function complete() {
        var ytop = $('#alert-container').offset().top;
        window.scrollTo({
          top: ytop - 300,
          behavior: 'smooth'
        });
      }
    });
  });

  // agregar nuevo consumo y diferencia
  $('#btnAgregaryDiff').on('click', function () {
    var selectedItems = [];
    $('.ck_item:checked').each(function () {
      var consumo_det_id = $(this).val();
      var parte_id = $(this).data('parte-id');
      selectedItems.push({
        consumo_det_id: consumo_det_id,
        parte_id: parte_id
      });
    });
    var estadoAgregar = $('#estadoAgregaryDiff').val();
    var periodoAgregar = $('#periodoAgregaryDiff').val();
    var obsAgregar = $('#obsAgregaryDiff').val();
    var valorAgregar = $('#valorAgregaryDiff').val();
    var refacturar = $('input[name="refacturaryDiff"]:checked').val();
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
      success: function success(response) {
        var alertDiv = '<div class="alert alert-success py-2">' + response.success + '</div>';
        $('#alert-container').html(alertDiv);
      },
      error: function error(response) {
        var errorMessages = '';
        console.log(response.responseJSON.error);
        if (response.responseJSON && response.responseJSON.errors) {
          for (var _i4 = 0, _Object$entries4 = Object.entries(response.responseJSON.errors); _i4 < _Object$entries4.length; _i4++) {
            var _Object$entries4$_i = _slicedToArray(_Object$entries4[_i4], 2),
              field = _Object$entries4$_i[0],
              messages = _Object$entries4$_i[1];
            errorMessages += "<li>" + messages + "</li>";
          }
        } else if (response.responseJSON.error) {
          errorMessages += "<li>" + response.responseJSON.error + "</li>";
        } else {
          errorMessages = 'Ocurrió un error inesperado.';
        }
        var alertDiv = '<div class="alert alert-danger py-2"><ul class="no-bullets">' + errorMessages + '</ul></div>';
        $('#alert-container').html(alertDiv);
      },
      complete: function complete() {
        var ytop = $('#alert-container').offset().top;
        window.scrollTo({
          top: ytop - 300,
          behavior: 'smooth'
        });
      }
    });
  });
});
document.addEventListener('DOMContentLoaded', function () {
  var contenedorGrilla = document.getElementById('contenedor-grilla');
  var columnasExtra = document.querySelectorAll('.columna-extra');
  var ajustarColumnas = function ajustarColumnas() {
    var anchoGrilla = contenedorGrilla.offsetWidth;
    // console.log("paso por aca: ",anchoGrilla);
    if (anchoGrilla < 1250) {
      columnasExtra.forEach(function (col) {
        return col.classList.add('d-none');
      });
    } else {
      columnasExtra.forEach(function (col) {
        return col.classList.remove('d-none');
      });
    }
  };

  //             window.addEventListener('resize', () => {
  //     console.log('Se detectó un cambio en el tamaño de la ventana');
  // });

  window.addEventListener('resize', ajustarColumnas);
  var observer = new ResizeObserver(ajustarColumnas);
  observer.observe(contenedorGrilla);
  ajustarColumnas();
});
/******/ })()
;