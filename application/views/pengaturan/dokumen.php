<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
  <div>
    <h1 class="h5 fw-bold text-dark mb-0">Pengaturan KOP &amp; Penandatangan Dokumen</h1>
    <p class="text-muted small mb-0">Atur logo daerah, nama perangkat daerah, alamat kantor, serta pejabat penandatangan dokumen keluaran.</p>
  </div>
  <div>
    <button type="submit" form="formPengaturanDokumen" class="btn btn-primary d-inline-flex align-items-center gap-1 shadow-sm">
      <i class="bi bi-floppy2-fill"></i>
      <span>Simpan Pengaturan</span>
    </button>
  </div>
</div>

<form id="formPengaturanDokumen" action="<?= site_url('pengaturan/save_dokumen') ?>" method="post" enctype="multipart/form-data">
  <input type="hidden" name="<?= $this->security->get_csrf_token_name() ?>" value="<?= $this->security->get_csrf_hash() ?>">

  <div class="row g-4">
    <!-- Kolom Kiri: Form Input KOP & TTD -->
    <div class="col-12 col-xl-7">
      <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-white p-3 border-bottom">
          <ul class="nav nav-pills card-header-pills" id="settingsTab" role="tablist">
            <li class="nav-item">
              <button class="nav-link active d-flex align-items-center gap-1" id="tab-kop" data-bs-toggle="tab" data-bs-target="#content-kop" type="button">
                <i class="bi bi-file-earmark-richtext"></i>
                <span>1. KOP Dokumen &amp; Logo</span>
              </button>
            </li>
            <li class="nav-item">
              <button class="nav-link d-flex align-items-center gap-1" id="tab-ttd" data-bs-toggle="tab" data-bs-target="#content-ttd" type="button">
                <i class="bi bi-pen"></i>
                <span>2. Pejabat Penandatangan</span>
              </button>
            </li>
          </ul>
        </div>

        <div class="card-body p-4">
          <div class="tab-content" id="settingsTabContent">
            
            <!-- TAB 1: KOP DOKUMEN & LOGO -->
            <div class="tab-pane fade show active" id="content-kop" role="tabpanel">
              <div class="mb-4 pb-3 border-bottom">
                <label class="form-label fw-bold text-dark mb-1">Logo Instansi / Pemerintah Daerah</label>
                <div class="d-flex align-items-center gap-3">
                  <div class="p-2 border rounded bg-light text-center" style="width: 100px; height: 100px;">
                    <img id="imgLogoPreview" src="<?= base_url($setting['logo_path']) ?>" alt="Logo Instansi" class="img-fluid h-100" style="object-fit: contain;">
                  </div>
                  <div class="flex-grow-1">
                    <input class="form-control" type="file" name="logo" id="inpLogoFile" accept="image/png, image/jpeg, image/jpg, image/svg+xml, image/webp">
                    <div class="form-text small text-muted">
                      Format: PNG, JPG, JPEG, SVG, WebP. Maksimal 3 MB. Disarankan menggunakan gambar berlatar transparan.
                    </div>
                  </div>
                </div>
              </div>

              <div class="row g-3">
                <div class="col-12">
                  <label class="form-label small fw-semibold">Nama Pemerintah Daerah <span class="text-danger">*</span></label>
                  <input type="text" class="form-control" name="nama_pemda" id="inpNamaPemda" value="<?= html_escape($setting['nama_pemda']) ?>" placeholder="Contoh: PEMERINTAH DAERAH PROVINSI / KABUPATEN BOGOR" required>
                  <div class="form-text small text-muted">Tampil pada baris paling atas KOP surat.</div>
                </div>

                <div class="col-12 col-md-6">
                  <label class="form-label small fw-semibold">Nama Lembaga / Instansi Induk</label>
                  <input type="text" class="form-control" name="nama_lembaga" id="inpNamaLembaga" value="<?= html_escape($setting['nama_lembaga']) ?>" placeholder="Contoh: DEWAN PERWAKILAN RAKYAT DAERAH">
                  <div class="form-text small text-muted">Tampil pada baris kedua KOP surat.</div>
                </div>

                <div class="col-12 col-md-6">
                  <label class="form-label small fw-semibold">Nama Perangkat Daerah (SKPD) <span class="text-danger">*</span></label>
                  <input type="text" class="form-control" name="nama_skpd" id="inpNamaSkpd" value="<?= html_escape($setting['nama_skpd']) ?>" placeholder="Contoh: SEKRETARIAT DPRD" required>
                  <div class="form-text small text-muted">Tampil dengan cetak tebal utama pada baris ketiga KOP.</div>
                </div>

                <div class="col-12">
                  <label class="form-label small fw-semibold">Alamat Kantor Lengkap <span class="text-danger">*</span></label>
                  <textarea class="form-control" name="alamat" id="inpAlamat" rows="2" placeholder="Contoh: Jalan Protokol Pemerintahan No. 01, Kompleks Perkantoran Pemda" required><?= html_escape($setting['alamat']) ?></textarea>
                </div>

                <div class="col-12 col-md-4">
                  <label class="form-label small fw-semibold">Kode Pos</label>
                  <input type="text" class="form-control" name="kode_pos" id="inpKodePos" value="<?= html_escape($setting['kode_pos']) ?>" placeholder="Contoh: 16914">
                </div>

                <div class="col-12 col-md-4">
                  <label class="form-label small fw-semibold">No. Telepon</label>
                  <input type="text" class="form-control" name="telepon" id="inpTelepon" value="<?= html_escape($setting['telepon']) ?>" placeholder="Contoh: (021) 8754531">
                </div>

                <div class="col-12 col-md-4">
                  <label class="form-label small fw-semibold">No. Fax</label>
                  <input type="text" class="form-control" name="fax" id="inpFax" value="<?= html_escape($setting['fax']) ?>" placeholder="Contoh: (021) 8754532">
                </div>

                <div class="col-12 col-md-6">
                  <label class="form-label small fw-semibold">Email Resmi</label>
                  <input type="email" class="form-control" name="email" id="inpEmail" value="<?= html_escape($setting['email']) ?>" placeholder="Contoh: setwan@daerah.go.id">
                </div>

                <div class="col-12 col-md-6">
                  <label class="form-label small fw-semibold">Website Resmi</label>
                  <input type="text" class="form-control" name="website" id="inpWebsite" value="<?= html_escape($setting['website']) ?>" placeholder="Contoh: dprd.daerah.go.id">
                </div>
              </div>
            </div>

            <!-- TAB 2: PEJABAT PENANDATANGAN -->
            <div class="tab-pane fade" id="content-ttd" role="tabpanel">
              <div class="mb-4">
                <label class="form-label small fw-semibold">Tempat / Kota Penetapan Dokumen <span class="text-danger">*</span></label>
                <input type="text" class="form-control" name="kota_penetapan" id="inpKota" value="<?= html_escape($setting['kota_penetapan']) ?>" placeholder="Contoh: Cibinong / Bandung / Jakarta" required>
                <div class="form-text small text-muted">Akan ditampilkan pada tanggal penetapan dokumen (Contoh: "<em>Cibinong, 10 November 2026</em>").</div>
              </div>

              <!-- Accordion Penandatangan 3 Pejabat -->
              <div class="accordion" id="accSignatories">
                
                <!-- 1. Pengguna Anggaran -->
                <div class="accordion-item border shadow-none mb-3 rounded overflow-hidden">
                  <h2 class="accordion-header">
                    <button class="accordion-button fw-bold bg-light" type="button" data-bs-toggle="collapse" data-bs-target="#accPA" aria-expanded="true">
                      <i class="bi bi-person-badge text-primary me-2 fs-5"></i>
                      <span>1. Pengguna Anggaran (PA / Sekretaris DPRD)</span>
                    </button>
                  </h2>
                  <div id="accPA" class="accordion-collapse collapse show">
                    <div class="accordion-body p-3 bg-white">
                      <div class="row g-3">
                        <div class="col-12 col-md-6">
                          <label class="form-label small fw-semibold">Label Atas Tanda Tangan</label>
                          <input type="text" class="form-control" name="pa_label" id="inpPaLabel" value="<?= html_escape($setting['pa_label']) ?>" placeholder="Contoh: Mengetahui,">
                        </div>
                        <div class="col-12 col-md-6">
                          <label class="form-label small fw-semibold">Jabatan Pengesahan</label>
                          <input type="text" class="form-control" name="pa_jabatan" id="inpPaJabatan" value="<?= html_escape($setting['pa_jabatan']) ?>" placeholder="Contoh: Sekretaris DPRD Selaku Pengguna Anggaran">
                        </div>
                        <div class="col-12 col-md-6">
                          <label class="form-label small fw-semibold">Nama Lengkap &amp; Gelar</label>
                          <input type="text" class="form-control" name="pa_nama" id="inpPaNama" value="<?= html_escape($setting['pa_nama']) ?>" placeholder="Contoh: Drs. H. AHMAD FAUZI, M.Si" required>
                        </div>
                        <div class="col-12 col-md-6">
                          <label class="form-label small fw-semibold">Pangkat / Golongan</label>
                          <input type="text" class="form-control" name="pa_pangkat" id="inpPaPangkat" value="<?= html_escape($setting['pa_pangkat']) ?>" placeholder="Contoh: Pembina Utama Muda (IV/c)">
                        </div>
                        <div class="col-12">
                          <label class="form-label small fw-semibold">NIP (Nomor Induk Pegawai)</label>
                          <input type="text" class="form-control" name="pa_nip" id="inpPaNip" value="<?= html_escape($setting['pa_nip']) ?>" placeholder="Contoh: 19750812 199803 1 002">
                        </div>
                      </div>
                    </div>
                  </div>
                </div>

                <!-- 2. PPTK -->
                <div class="accordion-item border shadow-none mb-3 rounded overflow-hidden">
                  <h2 class="accordion-header">
                    <button class="accordion-button fw-bold bg-light collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#accPPTK">
                      <i class="bi bi-briefcase text-info me-2 fs-5"></i>
                      <span>2. Pejabat Pelaksana Teknis Kegiatan (PPTK)</span>
                    </button>
                  </h2>
                  <div id="accPPTK" class="accordion-collapse collapse">
                    <div class="accordion-body p-3 bg-white">
                      <div class="row g-3">
                        <div class="col-12 col-md-6">
                          <label class="form-label small fw-semibold">Label Atas Tanda Tangan</label>
                          <input type="text" class="form-control" name="pptk_label" id="inpPptkLabel" value="<?= html_escape($setting['pptk_label']) ?>" placeholder="Contoh: Pejabat Pelaksana Teknis Kegiatan">
                        </div>
                        <div class="col-12 col-md-6">
                          <label class="form-label small fw-semibold">Jabatan PPTK</label>
                          <input type="text" class="form-control" name="pptk_jabatan" id="inpPptkJabatan" value="<?= html_escape($setting['pptk_jabatan']) ?>" placeholder="Contoh: PPTK Kegiatan Perjalanan Dinas">
                        </div>
                        <div class="col-12 col-md-6">
                          <label class="form-label small fw-semibold">Nama Lengkap &amp; Gelar</label>
                          <input type="text" class="form-control" name="pptk_nama" id="inpPptkNama" value="<?= html_escape($setting['pptk_nama']) ?>" placeholder="Contoh: BAMBANG SUSANTO, S.E., M.M." required>
                        </div>
                        <div class="col-12 col-md-6">
                          <label class="form-label small fw-semibold">Pangkat / Golongan</label>
                          <input type="text" class="form-control" name="pptk_pangkat" id="inpPptkPangkat" value="<?= html_escape($setting['pptk_pangkat']) ?>" placeholder="Contoh: Penata Tk. I (III/d)">
                        </div>
                        <div class="col-12">
                          <label class="form-label small fw-semibold">NIP (Nomor Induk Pegawai)</label>
                          <input type="text" class="form-control" name="pptk_nip" id="inpPptkNip" value="<?= html_escape($setting['pptk_nip']) ?>" placeholder="Contoh: 19820415 200501 1 008">
                        </div>
                      </div>
                    </div>
                  </div>
                </div>

                <!-- 3. Bendahara Pengeluaran -->
                <div class="accordion-item border shadow-none rounded overflow-hidden">
                  <h2 class="accordion-header">
                    <button class="accordion-button fw-bold bg-light collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#accBendahara">
                      <i class="bi bi-cash-coin text-success me-2 fs-5"></i>
                      <span>3. Bendahara Pengeluaran</span>
                    </button>
                  </h2>
                  <div id="accBendahara" class="accordion-collapse collapse">
                    <div class="accordion-body p-3 bg-white">
                      <div class="row g-3">
                        <div class="col-12 col-md-6">
                          <label class="form-label small fw-semibold">Label Atas Tanda Tangan</label>
                          <input type="text" class="form-control" name="bendahara_label" id="inpBendaharaLabel" value="<?= html_escape($setting['bendahara_label']) ?>" placeholder="Contoh: Bendahara Pengeluaran">
                        </div>
                        <div class="col-12 col-md-6">
                          <label class="form-label small fw-semibold">Jabatan</label>
                          <input type="text" class="form-control" name="bendahara_jabatan" id="inpBendaharaJabatan" value="<?= html_escape($setting['bendahara_jabatan']) ?>" placeholder="Contoh: Bendahara Pengeluaran Sekretariat DPRD">
                        </div>
                        <div class="col-12 col-md-6">
                          <label class="form-label small fw-semibold">Nama Lengkap &amp; Gelar</label>
                          <input type="text" class="form-control" name="bendahara_nama" id="inpBendaharaNama" value="<?= html_escape($setting['bendahara_nama']) ?>" placeholder="Contoh: SITI NURHALIZA, S.Ak." required>
                        </div>
                        <div class="col-12 col-md-6">
                          <label class="form-label small fw-semibold">Pangkat / Golongan</label>
                          <input type="text" class="form-control" name="bendahara_pangkat" id="inpBendaharaPangkat" value="<?= html_escape($setting['bendahara_pangkat']) ?>" placeholder="Contoh: Penata Muda Tk. I (III/b)">
                        </div>
                        <div class="col-12">
                          <label class="form-label small fw-semibold">NIP (Nomor Induk Pegawai)</label>
                          <input type="text" class="form-control" name="bendahara_nip" id="inpBendaharaNip" value="<?= html_escape($setting['bendahara_nip']) ?>" placeholder="Contoh: 19881120 201101 2 004">
                        </div>
                      </div>
                    </div>
                  </div>
                </div>

              </div>
            </div>

          </div>
        </div>

        <div class="card-footer bg-light p-3 d-flex justify-content-between align-items-center">
          <small class="text-muted"><i class="bi bi-info-circle me-1"></i> Perubahan akan langsung tercermin pada seluruh dokumen cetak.</small>
          <button type="submit" class="btn btn-primary d-inline-flex align-items-center gap-1 shadow-sm">
            <i class="bi bi-floppy2-fill"></i>
            <span>Simpan Pengaturan</span>
          </button>
        </div>
      </div>
    </div>

    <!-- Kolom Kanan: Pratinjau Dokumen Cetak (Live Preview) -->
    <div class="col-12 col-xl-5">
      <div class="card shadow-sm border-0 sticky-top" style="top: 85px;">
        <div class="card-header bg-white fw-bold d-flex align-items-center justify-content-between">
          <div class="d-flex align-items-center gap-2">
            <i class="bi bi-eye text-primary"></i>
            <span>Pratinjau KOP &amp; Tanda Tangan (Live)</span>
          </div>
          <span class="badge text-bg-light border text-muted small">Ukuran A4</span>
        </div>
        <div class="card-body p-3 p-md-4 bg-secondary bg-opacity-10">
          
          <!-- Mockup Lembar Kertas Dokumen Cetak -->
          <div class="bg-white p-4 shadow-sm rounded border" style="font-size: 0.82rem; min-height: 480px;">
            
            <!-- Live Preview KOP -->
            <div id="previewKopContainer">
              <?php $this->load->view('layout/kop_dokumen', array('setting' => $setting)); ?>
            </div>

            <!-- Contoh Isi Dokumen -->
            <div class="text-center my-3 py-2 bg-light border border-dashed rounded">
              <h6 class="fw-bold mb-0 text-uppercase" style="letter-spacing: 1px; font-size: 0.88rem;">
                BUKTI REKAPITULASI BIAYA PERJALANAN DINAS
              </h6>
              <span class="text-muted small">Nomor: 090/ST/DPRD/<?= date('Y') ?></span>
            </div>

            <p class="text-muted small text-center my-3 fst-italic">
              [ ... Rincian peserta, biaya transportasi, uang harian, dan penginapan ... ]
            </p>

            <!-- Live Preview Tanda Tangan -->
            <div id="previewTtdContainer">
              <?php $this->load->view('layout/tanda_tangan', array('setting' => $setting, 'tanggal' => date('Y-m-d'))); ?>
            </div>

          </div>

        </div>
      </div>
    </div>
  </div>
</form>

<script>
$(function(){
  // Live update preview text
  function updateLivePreview(){
    var pemda = $('#inpNamaPemda').val() || 'PEMERINTAH DAERAH';
    var lembaga = $('#inpNamaLembaga').val() || 'DEWAN PERWAKILAN RAKYAT DAERAH';
    var skpd = $('#inpNamaSkpd').val() || 'SEKRETARIAT DPRD';
    var alamat = $('#inpAlamat').val() || '';
    var kodepos = $('#inpKodePos').val();
    var telp = $('#inpTelepon').val();
    var fax = $('#inpFax').val();
    var email = $('#inpEmail').val();
    var web = $('#inpWebsite').val();

    $('.kop-pemda').text(pemda);
    $('.kop-lembaga').text(lembaga);
    $('.kop-skpd').text(skpd);

    var parts = [];
    if(telp) parts.push('Telp: ' + telp);
    if(fax) parts.push('Fax: ' + fax);
    if(email) parts.push('Email: ' + email);
    if(web) parts.push('Website: ' + web);

    var kontakStr = alamat + (kodepos ? ' • Kode Pos: ' + kodepos : '') + '<br>' + parts.join(' • ');
    $('.kop-kontak').html(kontakStr);

    // Update Signatures
    var kota = $('#inpKota').val() || 'Kota';
    var paNama = $('#inpPaNama').val() || '-';
    var paPangkat = $('#inpPaPangkat').val() || '';
    var paNip = $('#inpPaNip').val() || '-';

    var pptkNama = $('#inpPptkNama').val() || '-';
    var pptkPangkat = $('#inpPptkPangkat').val() || '';
    var pptkNip = $('#inpPptkNip').val() || '-';

    var benNama = $('#inpBendaharaNama').val() || '-';
    var benPangkat = $('#inpBendaharaPangkat').val() || '';
    var benNip = $('#inpBendaharaNip').val() || '-';

    // Kolom 1 PA
    $('.tanda-tangan-block .col-4:nth-child(1) strong.text-decoration-underline').text(paNama);
    $('.tanda-tangan-block .col-4:nth-child(1) span:contains("NIP")').text('NIP. ' + paNip);

    // Kolom 2 PPTK
    $('.tanda-tangan-block .col-4:nth-child(2) strong.text-decoration-underline').text(pptkNama);
    $('.tanda-tangan-block .col-4:nth-child(2) span:contains("NIP")').text('NIP. ' + pptkNip);

    // Kolom 3 Bendahara
    $('.tanda-tangan-block .col-4:nth-child(3) strong.text-decoration-underline').text(benNama);
    $('.tanda-tangan-block .col-4:nth-child(3) span:contains("NIP")').text('NIP. ' + benNip);
    $('.tanda-tangan-block .col-4:nth-child(3) .ttd-header span:first-child').text(kota + ', ' + '<?= tgl_indo(date('Y-m-d')) ?>');
  }

  // Bind live preview on input
  $('#formPengaturanDokumen input, #formPengaturanDokumen textarea').on('input keyup change', updateLivePreview);

  // File logo preview
  $('#inpLogoFile').on('change', function(){
    var file = this.files && this.files[0];
    if(file){
      var reader = new FileReader();
      reader.onload = function(e){
        $('#imgLogoPreview').attr('src', e.target.result);
        $('.kop-logo').attr('src', e.target.result);
      };
      reader.readAsDataURL(file);
    }
  });

  // Submit form with FormData
  $('#formPengaturanDokumen').on('submit', function(e){
    e.preventDefault();
    var formData = new FormData(this);

    App.ajax({
      url: this.action,
      data: formData
    }).done(function(res){
      if(res.status){
        Swal.fire({
          icon: 'success',
          title: 'Pengaturan Disimpan!',
          text: res.message || 'KOP Dokumen dan Pejabat Penandatangan berhasil diperbarui.',
          timer: 2000,
          showConfirmButton: false
        });
        if(res.data && res.data.logoUrl){
          $('#imgLogoPreview').attr('src', res.data.logoUrl);
          $('.kop-logo').attr('src', res.data.logoUrl);
        }
      }
    });
  });
});
</script>

