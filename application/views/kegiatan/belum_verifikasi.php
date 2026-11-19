<div class="d-flex justify-content-center align-items-center" style="min-height: 60vh;">
  <div class="text-center p-4">
    <i class="bi bi-shield-exclamation text-warning" style="font-size: 4rem;"></i>
    <h4 class="fw-bold mt-3">Kegiatan Belum Diverifikasi</h4>
    <p class="text-muted">Bukti rekapitulasi hanya dapat dicetak setelah kegiatan berstatus <strong>AKTIF (Terverifikasi)</strong>.</p>
    <a href="<?= site_url('kegiatan/rincian/' . encrypt_id($kegiatan['id'])) ?>" class="btn btn-primary mt-2">
      <i class="bi bi-arrow-left me-1"></i>Kembali ke Rincian
    </a>
  </div>
</div>
