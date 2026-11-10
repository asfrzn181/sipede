(function($){
  'use strict';

  var reportDT = null;

  function getFilterParams(){
    var o = {};
    $('#reportForm').serializeArray().forEach(function(x){
      if(x.name.endsWith('[]')){
        var k = x.name.slice(0, -2);
        (o[k] || (o[k] = [])).push(x.value);
      } else {
        o[x.name] = x.value;
      }
    });
    return o;
  }

  function initDataTable(){
    if (!$.fn.DataTable) return;

    if ($.fn.DataTable.isDataTable('#reportTable')) {
      $('#reportTable').DataTable().destroy();
    }

    reportDT = $('#reportTable').DataTable({
      processing: true,
      serverSide: true,
      responsive: true,
      ajax: {
        url: window.APP.baseUrl + 'laporan/data',
        type: 'POST',
        data: function(d) {
          d[window.APP.csrfName] = window.APP.csrfHash;
          var filters = getFilterParams();
          return $.extend({}, d, filters);
        }
      },
      order: [[1, 'desc']],
      columnDefs: [
        { targets: 7, className: 'text-end' }
      ]
    });
  }

  function downloadExport(fmt){
    var payload = getFilterParams();
    payload[window.APP.csrfName] = window.APP.csrfHash;

    fetch(window.APP.baseUrl + 'laporan/export/' + fmt, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': window.APP.csrfHash,
        'X-Requested-With': 'XMLHttpRequest'
      },
      body: JSON.stringify(payload)
    }).then(function(r){
      if (!r.ok) {
        throw new Error('Gagal mengekspor laporan (' + r.status + ')');
      }
      return r.blob();
    }).then(function(blob){
      var a = document.createElement('a');
      a.href = URL.createObjectURL(blob);
      a.download = (fmt === 'pdf' ? 'laporan.pdf' : 'laporan.xlsx');
      document.body.appendChild(a);
      a.click();
      a.remove();
      URL.revokeObjectURL(a.href);
    }).catch(function(err){
      App.toast(err.message || 'Gagal mengekspor berkas', 'error');
    });
  }

  $(function(){
    // Setup select2 pada form filter
    if ($.fn.select2) {
      $('#reportForm select').select2({ width: '100%' });
    }

    // Initialize Server-Side DataTable
    initDataTable();

    // Trigger reload pada saat filter disubmit
    $('#reportForm').on('submit', function(e){
      e.preventDefault();
      if (reportDT) {
        reportDT.ajax.reload();
      } else {
        initDataTable();
      }
    });

    $('#exportExcel').on('click', function(){
      downloadExport('excel');
    });

    $('#exportPdf').on('click', function(){
      downloadExport('pdf');
    });
  });
})(jQuery);
