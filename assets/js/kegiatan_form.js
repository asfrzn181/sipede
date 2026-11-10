(function($){
  'use strict';

  if (!$('#kegiatanModal').length || !window.KegiatanData) {
    return;
  }

  var jenis   = (window.KegiatanData && window.KegiatanData.jenis)   || [];
  var sources = (window.KegiatanData && window.KegiatanData.sumberDana) || [];

  function sourceOptions(person) {
    var selected = person.sumber_dana_id ? String(person.sumber_dana_id) : '';
    return '<option value="">Pilih sumber dana</option>' +
      sources.map(function(x){
        return '<option value="' + x.id + '"' + (String(x.id) === selected ? ' selected' : '') + '>' +
               escapeHtml(x.kode + ' - ' + x.nama) + '</option>';
      }).join('');
  }

  /* ── Baris rincian biaya (jenis biaya opsional, input rupiah dengan pembilang) ── */
  function costRow() {
    var opts = '<option value="">— Tanpa jenis biaya —</option>' +
      jenis.map(function(x){
        return '<option value="' + x.id + '">' + escapeHtml(x.nama) + '</option>';
      }).join('');

    return $(
      '<tr>' +
        '<td>' +
          '<select class="form-select form-select-sm biaya-jenis">' + opts + '</select>' +
          '<div class="form-text small text-muted" style="font-size:0.72rem">Opsional</div>' +
        '</td>' +
        '<td style="width: 100px;"><input class="form-control form-control-sm biaya-volume" type="number" min="0" step=".01" value="1"></td>' +
        '<td style="min-width: 170px;">' +
          '<div class="input-group input-group-sm">' +
            '<span class="input-group-text bg-light">Rp</span>' +
            '<input class="form-control form-control-sm biaya-tarif-display" type="text" placeholder="0" value="0" autocomplete="off">' +
            '<input type="hidden" class="biaya-tarif" value="0">' +
          '</div>' +
          '<div class="biaya-terbilang text-success fst-italic mt-1" style="font-size:0.7rem; line-height: 1.2;"></div>' +
        '</td>' +
        '<td class="biaya-subtotal fw-semibold text-primary text-end">Rp 0</td>' +
        '<td class="text-end"><button type="button" class="btn btn-sm btn-outline-danger remove-cost">&times;</button></td>' +
      '</tr>'
    );
  }

  /* ── Kartu peserta ── */
  function card(id, person) {
    var badge = person.tipe === 'DPRD'
      ? '<span class="badge text-bg-primary bg-opacity-10 text-primary border ms-1" style="font-size:0.68rem">DPRD</span>'
      : '<span class="badge text-bg-secondary bg-opacity-10 text-secondary border ms-1" style="font-size:0.68rem">PENDAMPING</span>';

    var el = $(
      '<div class="participant-card" data-id="' + id + '">' +
        '<div class="row g-2 align-items-end mb-3">' +
          '<div class="col-12 col-md-5">' +
            '<h6 class="mb-1">' + escapeHtml(person.nama) + badge + '</h6>' +
            '<small class="text-muted">' + escapeHtml(person.jabatan || person.tipe) + '</small>' +
          '</div>' +
          '<div class="col-12 col-md-5">' +
            '<label class="form-label small mb-1">Anggaran / Sumber Dana</label>' +
            '<select class="form-select form-select-sm sumber-dana">' + sourceOptions(person) + '</select>' +
          '</div>' +
          '<div class="col-12 col-md-2 text-md-end">' +
            '<button type="button" class="btn btn-sm btn-outline-danger remove-person">Hapus</button>' +
          '</div>' +
        '</div>' +
        '<div class="saldo text-muted mb-2">Saldo peserta: memuat...</div>' +
        '<div class="table-responsive">' +
          '<table class="table table-sm">' +
            '<thead><tr><th>Jenis Biaya</th><th>Volume</th><th>Tarif</th><th>Jumlah</th><th></th></tr></thead>' +
            '<tbody></tbody>' +
          '</table>' +
        '</div>' +
        '<button type="button" class="btn btn-sm btn-outline-primary add-cost">+ Tambah rincian</button>' +
        '<strong class="float-end person-total">Rp 0</strong>' +
      '</div>'
    );
    el.find('tbody').append(costRow());
    return el;
  }

  /* ── Hitung ulang total ── */
  function recalc() {
    var grand = 0;
    $('.participant-card').each(function(){
      var total = 0;
      $(this).find('tbody tr').each(function(){
        var sub = Number($(this).find('.biaya-volume').val() || 0) *
                  Number($(this).find('.biaya-tarif').val()  || 0);
        total += sub;
        $(this).find('.biaya-subtotal').text(formatRupiah(sub));
      });
      grand += total;
      $(this).find('.person-total').text(formatRupiah(total));
    });
    $('#grandTotal').text(formatRupiah(grand));
  }

  /* ── Select2 peserta ── */
  $('#pesertaSelect').select2({
    dropdownParent: $('#kegiatanModal'),
    placeholder: 'Pilih peserta'
  }).on('change', function(){
    var selected = $(this).val() || [];
    // hapus kartu yang tidak lagi dipilih
    $('#participantCards .participant-card').each(function(){
      if (selected.indexOf(String($(this).data('id'))) < 0) $(this).remove();
    });
    // tambah kartu baru
    selected.forEach(function(id){
      if (!$('#participantCards .participant-card[data-id="' + id + '"]').length) {
        var p = (window.KegiatanData.penerima || []).find(function(x){ return String(x.id) === String(id); });
        if (p) $('#participantCards').append(card(id, p));
      }
    });
    recalc();
  });

  $(document)
    .on('click', '.add-cost',    function(){ $(this).closest('.participant-card').find('tbody').append(costRow()); recalc(); })
    .on('click', '.remove-cost', function(){ $(this).closest('tr').remove(); recalc(); })
    .on('click', '.remove-person', function(){
      var id   = String($(this).closest('.participant-card').data('id'));
      var vals = $('#pesertaSelect').val() || [];
      $('#pesertaSelect').val(vals.filter(function(x){ return String(x) !== id; })).trigger('change');
    })
    .on('input', '.biaya-volume', recalc)
    .on('input', '.biaya-tarif-display', function(){
      var raw = $(this).val().replace(/\D/g, '');
      var num = raw ? parseInt(raw, 10) : 0;
      $(this).val(raw ? num.toLocaleString('id-ID') : '');
      $(this).siblings('.biaya-tarif').val(num);
      var kata = raw ? (window.terbilang ? window.terbilang(num) : '') : '';
      $(this).closest('td').find('.biaya-terbilang').text(kata);
      recalc();
    });

  /* ── Submit form ── */
  $('#kegiatanForm').on('submit', function(e){
    e.preventDefault();
    var payload = {
      no_surat_tugas:    $('[name="no_surat_tugas"]').val(),
      tanggal_surat_tugas: $('[name="tanggal_surat_tugas"]').val(),
      tanggal_input:     $('[name="tanggal_input"]').val(),
      tanggal_mulai:     $('[name="tanggal_mulai"]').val(),
      tanggal_selesai:   $('[name="tanggal_selesai"]').val(),
      tempat:            $('[name="tempat"]').val(),
      tema:              $('[name="tema"]').val(),
      nama_kegiatan:     $('[name="tema"]').val(),
      status:            $('[name="status"]').val(),
      peserta: []
    };
    $('.participant-card').each(function(){
      var person = {
        penerima_id:   $(this).data('id'),
        sumber_dana_id: $(this).find('.sumber-dana').val(),
        biaya: []
      };
      $(this).find('tbody tr').each(function(){
        // biaya jenis bisa kosong (opsional)
        person.biaya.push({
          jenis_biaya_id: $(this).find('.biaya-jenis').val() || null,
          volume:         $(this).find('.biaya-volume').val(),
          tarif:          $(this).find('.biaya-tarif').val()
        });
      });
      payload.peserta.push(person);
    });
    App.ajax({
      url: this.action,
      data: JSON.stringify(payload),
      contentType: 'application/json'
    }).done(function(r){
      if (r.status) setTimeout(function(){ location.reload(); }, 600);
    });
  });

})(jQuery);
