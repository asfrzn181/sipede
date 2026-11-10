(function($){
  'use strict';

  window.APP = window.APP || {baseUrl: '/', csrfName: 'csrf_token', csrfHash: ''};

  function toast(message, icon){
    Swal.fire({
      toast: true,
      position: 'top-end',
      timer: 2800,
      showConfirmButton: false,
      icon: icon || 'success',
      title: message
    });
  }

  function refreshToken(response){
    var token = response && (response.csrf_hash || (response.responseJSON && response.responseJSON.csrf_hash));
    if(token){
      window.APP.csrfHash = token;
      $('[name="' + window.APP.csrfName + '"]').val(token);
    }
  }

  // Global jQuery AJAX Setup: Otomatis sisipkan CSRF token & XMLHttpRequest header
  $.ajaxPrefilter(function(options, originalOptions, xhr){
    if(window.APP && window.APP.csrfHash){
      xhr.setRequestHeader('X-CSRF-TOKEN', window.APP.csrfHash);
      xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
    }
  });

  $(document).ajaxComplete(function(event, xhr, settings){
    var res = xhr.responseJSON;
    if(res && res.csrf_hash){
      refreshToken(res);
    }
  });

  window.App = {
    ajax: function(options){
      var opts = $.extend(true, {method: 'POST', dataType: 'json', headers: {}}, options);
      var jsonRequest = opts.contentType === 'application/json' || (opts.headers && opts.headers['Content-Type'] === 'application/json');

      if(opts.data instanceof FormData){
        opts.data.append(window.APP.csrfName, window.APP.csrfHash);
        opts.processData = false;
        opts.contentType = false;
      } else if(jsonRequest){
        var jsonData = typeof opts.data === 'string' ? (function(){ try { return JSON.parse(opts.data); } catch(e){ return {}; } })() : (opts.data || {});
        jsonData[window.APP.csrfName] = window.APP.csrfHash;
        opts.data = JSON.stringify(jsonData);
      } else if(typeof opts.data === 'string'){
        opts.data += (opts.data ? '&' : '') + encodeURIComponent(window.APP.csrfName) + '=' + encodeURIComponent(window.APP.csrfHash);
      } else {
        opts.data = opts.data || {};
        opts.data[window.APP.csrfName] = window.APP.csrfHash;
      }

      return $.ajax(opts).done(function(res){
        refreshToken(res);
        if(res.message) toast(res.message, res.status ? 'success' : 'error');
        if(res.status && res.data && res.data.redirect) window.location = res.data.redirect;
      }).fail(function(xhr){
        refreshToken(xhr);
        var res = xhr.responseJSON || {};
        if(xhr.status === 401){
          toast(res.message || 'Username atau password salah.', 'error');
          return;
        }
        var raw = (xhr.responseText || '').replace(/<[^>]*>/g, ' ').replace(/\s+/g, ' ').trim();
        toast(res.message || raw.substring(0, 180) || 'Terjadi kesalahan.', 'error');
        if(res.errors){
          Object.keys(res.errors).forEach(function(k){
            var f = $('[name="' + k + '"]');
            f.addClass('is-invalid').next('.invalid-feedback').remove();
            f.after('<div class="invalid-feedback">' + res.errors[k] + '</div>');
          });
        }
      });
    },
    toast: toast
  };

  // Setup DataTables Indonesian Defaults
  if ($.fn.DataTable) {
    window.DataTableLang = {
      search: "Cari:",
      searchPlaceholder: "Ketik kata kunci...",
      lengthMenu: "Tampilkan _MENU_ data",
      info: "Menampilkan _START_ s/d _END_ dari _TOTAL_ data",
      infoEmpty: "Menampilkan 0 data",
      infoFiltered: "(disaring dari _MAX_ data)",
      zeroRecords: "Tidak ada data yang cocok",
      emptyTable: "Belum ada data tersedia",
      paginate: {
        first: '<i class="bi bi-chevron-double-left"></i>',
        previous: '<i class="bi bi-chevron-left"></i>',
        next: '<i class="bi bi-chevron-right"></i>',
        last: '<i class="bi bi-chevron-double-right"></i>'
      }
    };

    $.extend(true, $.fn.dataTable.defaults, {
      language: window.DataTableLang,
      pageLength: 10,
      responsive: true,
      autoWidth: false,
      columnDefs: [
        { targets: 'no-sort', orderable: false }
      ]
    });

    window.createDataTableServerSide = function(selector, options) {
      var $el = $(selector);
      if (!$el.length) return null;

      if ($.fn.DataTable.isDataTable($el)) {
        $el.DataTable().destroy();
      }

      var defaultOpts = {
        processing: true,
        serverSide: true,
        responsive: true,
        ajax: {
          type: 'POST',
          data: function(d) {
            d[window.APP.csrfName] = window.APP.csrfHash;
          }
        }
      };

      var finalOpts = $.extend(true, {}, defaultOpts, options);

      if (options && options.ajax && typeof options.ajax.data === 'function') {
        var customDataFn = options.ajax.data;
        finalOpts.ajax.data = function(d) {
          d[window.APP.csrfName] = window.APP.csrfHash;
          return customDataFn(d) || d;
        };
      }

      return $el.DataTable(finalOpts);
    };
  }

  // Setup Select2 Defaults & Auto-Init
  window.initSelect2 = function(context) {
    if (!$.fn.select2) return;
    var $root = context ? $(context) : $(document);
    $root.find('select.select2, select.form-select').not('.no-select2').each(function(){
      var $sel = $(this);
      if ($sel.hasClass('select2-hidden-accessible')) return;

      var $modal = $sel.closest('.modal');
      var placeholder = $sel.find('option[value=""]').first().text() || $sel.attr('placeholder') || '-- Pilih --';
      var opts = {
        width: '100%',
        placeholder: placeholder,
        allowClear: Boolean($sel.find('option[value=""]').length)
      };
      if ($modal.length) {
        opts.dropdownParent = $modal;
      }
      $sel.select2(opts);
    });
  };

  $(document).on('submit', '.js-ajax-form', function(e){
    e.preventDefault();
    var form = this;
    App.ajax({
      url: form.action,
      data: $(form).serialize()
    }).done(function(res){
      if(res.status){
        var modal = $(form).closest('.modal')[0];
        if(modal) bootstrap.Modal.getOrCreateInstance(modal).hide();
        setTimeout(function(){ location.reload(); }, 500);
      }
    });
  });

  $(document).on('click', '.js-lock', function(){
    var id = $(this).data('id');
    Swal.fire({
      title: 'Ubah status kunci?',
      icon: 'question',
      showCancelButton: true,
      confirmButtonText: 'Ya',
      cancelButtonText: 'Batal'
    }).then(function(r){
      if(r.isConfirmed){
        App.ajax({url: window.APP.baseUrl + 'tahun/lock/' + id}).done(function(res){
          if(res.status) setTimeout(function(){ location.reload(); }, 500);
        });
      }
    });
  });

  $(function(){
    $('#loginForm').on('submit', function(e){
      e.preventDefault();
      App.ajax({url: this.action, data: $(this).serialize()});
    });

    // Inisialisasi DataTables client-side hanya untuk tabel yang bukan server-side
    if ($.fn.DataTable) {
      $('table.datatable').not('#latestTable, #reportTable, #kegiatanTable, #antrianTable, #rekapTable, #penerimaTable, #userTable, #auditTable, #tahunTable, #jenisBiayaTable, .datatable-serverside').each(function(){
        if (!$.fn.DataTable.isDataTable(this)) {
          $(this).DataTable();
        }
      });

      // Auto-init tabel dengan class datatable-serverside
      $('table.datatable-serverside').each(function(){
        var $t = $(this);
        var url = $t.data('url');
        if (url && !$.fn.DataTable.isDataTable(this)) {
          window.createDataTableServerSide(this, {
            ajax: { url: url }
          });
        }
      });
    }

    // Inisialisasi Select2 pada semua select element
    if ($.fn.select2) {
      window.initSelect2();
      $(document).on('shown.bs.modal', function(e){
        window.initSelect2(e.target);
      });
    }

    if($('#dashboard').length){
      App.ajax({
        url: $('#dashboard').data('url'),
        method: 'GET'
      }).done(function(r){
        if(!r.status) return;
        var s = r.data.summary || {};
        $('#statPagu').text(formatRupiah(s.pagu));
        $('#statRealisasi').text(formatRupiah(s.realisasi));
        $('#statSisa').text(formatRupiah(s.sisa));

        var labels = [], values = [];
        (r.data.monthly || []).forEach(function(x){
          labels.push('Bulan ' + x.bulan);
          values.push(x.total);
        });

        var chartElem = document.getElementById('monthlyChart');
        if (chartElem) {
          new Chart(chartElem, {
            type: 'bar',
            data: {
              labels: labels,
              datasets: [{
                label: 'Realisasi',
                data: values,
                backgroundColor: '#2563eb',
                borderRadius: 6
              }]
            },
            options: {
              responsive: true,
              plugins: {legend: {display: false}}
            }
          });
        }

        (r.data.terkecil || []).forEach(function(x){
          $('#smallestList').append(
            '<div class="list-group-item d-flex justify-content-between align-items-center">' +
              '<span>' + escapeHtml(x.nama || x.penerima_nama || '') + '</span>' +
              '<strong class="text-primary">' + formatRupiah(x.sisa) + '</strong>' +
            '</div>'
          );
        });

        var latestTbody = $('#latestTable tbody');
        (r.data.latest || []).forEach(function(x){
          var badgeClass = x.status === 'BATAL' ? 'danger' : (x.status === 'AKTIF' ? 'success' : (x.status === 'DIAJUKAN' ? 'warning' : 'secondary'));
          latestTbody.append(
            '<tr>' +
              '<td class="fw-semibold">' + escapeHtml(x.nama_kegiatan) + '</td>' +
              '<td>' + escapeHtml(x.tanggal_mulai) + '</td>' +
              '<td>' + escapeHtml(x.tempat || '-') + '</td>' +
              '<td><span class="badge text-bg-' + badgeClass + '">' + escapeHtml(x.status) + '</span></td>' +
            '</tr>'
          );
        });

        // Initialize DataTable on latest activities
        if ($.fn.DataTable && $('#latestTable').length) {
          if ($.fn.DataTable.isDataTable('#latestTable')) {
            $('#latestTable').DataTable().destroy();
          }
          $('#latestTable').DataTable({
            pageLength: 5,
            lengthChange: false,
            searching: false,
            info: false
          });
        }
      });
    }
  });

  window.formatRupiah = function(v){
    return 'Rp ' + Number(v || 0).toLocaleString('id-ID');
  };

  window.cleanRupiah = function(v){
    return Number(String(v || 0).replace(/\D/g, '')) || 0;
  };

  /**
   * Konversi angka ke kalimat terbilang Bahasa Indonesia
   */
  window.terbilang = function(angka){
    var bilangan = ['', 'Satu', 'Dua', 'Tiga', 'Empat', 'Lima', 'Enam', 'Tujuh', 'Delapan', 'Sembilan', 'Sepuluh', 'Sebelas'];
    var n = Math.floor(Math.abs(Number(angka) || 0));
    if (n === 0) return 'Nol Rupiah';

    function toKata(val) {
      if (val < 12) return bilangan[val];
      if (val < 20) return toKata(val - 10) + ' Belas';
      if (val < 100) return toKata(Math.floor(val / 10)) + ' Puluh' + (val % 10 ? ' ' + bilangan[val % 10] : '');
      if (val < 200) return 'Seratus' + (val - 100 ? ' ' + toKata(val - 100) : '');
      if (val < 1000) return toKata(Math.floor(val / 100)) + ' Ratus' + (val % 100 ? ' ' + toKata(val % 100) : '');
      if (val < 2000) return 'Seribu' + (val - 1000 ? ' ' + toKata(val - 1000) : '');
      if (val < 1000000) return toKata(Math.floor(val / 1000)) + ' Ribu' + (val % 1000 ? ' ' + toKata(val % 1000) : '');
      if (val < 1000000000) return toKata(Math.floor(val / 1000000)) + ' Juta' + (val % 1000000 ? ' ' + toKata(val % 1000000) : '');
      if (val < 1000000000000) return toKata(Math.floor(val / 1000000000)) + ' Miliar' + (val % 1000000000 ? ' ' + toKata(val % 1000000000) : '');
      if (val < 1000000000000000) return toKata(Math.floor(val / 1000000000000)) + ' Triliun' + (val % 1000000000000 ? ' ' + toKata(val % 1000000000000) : '');
      return '';
    }

    var hasil = toKata(n).replace(/\s+/g, ' ').trim();
    return hasil ? hasil + ' Rupiah' : '';
  };

  // Masking format rupiah otomatis dengan teks pembilang
  $(document).on('input', '.input-rupiah', function(){
    var $input = $(this);
    var raw = $input.val().replace(/\D/g, '');
    var num = raw ? parseInt(raw, 10) : 0;

    // Format tampilan bertitik
    $input.val(raw ? num.toLocaleString('id-ID') : '');

    // Sinkronkan ke input target hidden (numeric value murni)
    var targetHidden = $input.data('target-hidden');
    if (targetHidden) {
      $(targetHidden).val(num).trigger('change');
    }

    // Tampilkan kalimat pembilang terbilang
    var targetTerbilang = $input.data('target-terbilang');
    if (targetTerbilang) {
      var kata = raw ? window.terbilang(num) : '';
      $(targetTerbilang).text(kata);
      var $box = $(targetTerbilang).closest('.pembilang-box');
      if ($box.length) {
        if (kata) $box.removeClass('d-none');
        else $box.addClass('d-none');
      }
    }
  });

  window.escapeHtml = function(s){
    return $('<div>').text(s == null ? '' : s).html();
  };
})(jQuery);
