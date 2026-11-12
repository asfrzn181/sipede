# SIPEDE — Sistem Informasi Perjalanan Dinas Sekretariat DPRD Kabupaten Bangka

Aplikasi berbasis **CodeIgniter 3 + PHP 8.2** dengan arsitektur **Pos Anggaran** (DPRD per orang & Pendamping per pos jabatan), realisasi dinamis, verifikasi PPTK, KOP & tanda tangan resmi, ekspor PDF/Excel, dan DataTables Server-Side Processing.

---

## 🌟 Fitur Utama

- **Pemisahan Pos Anggaran & Orang**:
  - Anggota DPRD: Pagu melekat pada orang (1 orang = 1 pos).
  - Pendamping: Pagu melekat pada 8 pos jabatan baku (dapat dialihkan dengan alasan jika berhalangan).
- **Pengaturan KOP Dokumen & Penandatangan**:
  - Upload logo daerah (SVG, PNG, JPG).
  - Tanda tangan dinamis 3 pejabat: Pengguna Anggaran (Sekretaris DPRD), PPTK, dan Bendahara Pengeluaran.
  - Interactive Live Preview (pratinjau lembar kertas A4 sebelum cetak).
- **Workflow Verifikasi Lengkap**:
  - DRAFT &rarr; DIAJUKAN &rarr; DIVERIFIKASI / AKTIF &rarr; BATAL.
  - SweetAlert confirmation pada tindakan verifikasi dan pembatalan.
- **Server-Side DataTables**:
  - Performa cepat dengan token CSRF terproteksi di setiap request AJAX.
- **Keamanan**:
  - Enkripsi Primary Key URL (`encrypt_id` / `decrypt_id`).
  - RBAC (Role-Based Access Control) multi-level dengan proteksi Super Admin.

---

## 🚀 Panduan Push ke GitHub & Auto-Deploy ke cPanel

### 1. Push ke GitHub Repository Anda
Jalankan perintah berikut di terminal komputer lokal Anda:

```bash
git remote add origin https://github.com/USERNAME_ANDA/NAMA_REPO_ANDA.git
git branch -M main
git push -u origin main
```

---

### 2. Pilihan Integrasi ke cPanel Hosting

Aplikasi ini sudah dilengkapi dengan **2 metode integrasi cPanel**:

#### Cara A: Otomatis via GitHub Actions (Sangat Disarankan ⭐)
Metode ini otomatis meng-compile vendor composer di cloud GitHub lalu mengunggahnya ke hosting cPanel melalui FTP/FTPS. Anda **tidak memerlukan akses SSH / Terminal** di cPanel.

1. Buka repositori Anda di GitHub.
2. Masuk ke tab **Settings** &gt; **Secrets and variables** &gt; **Actions** &gt; Klik **New repository secret**.
3. Tambahkan 3 variabel rahasia:
   - `FTP_SERVER`: Alamat server FTP hosting (contoh: `ftp.domainanda.com` atau alamat IP hosting).
   - `FTP_USERNAME`: Username akun FTP cPanel Anda.
   - `FTP_PASSWORD`: Password akun FTP cPanel Anda.
   - *(Opsional)* `FTP_SERVER_DIR`: Path tujuan di cPanel (default: `public_html/` atau `public_html/sipede/`).
4. **Selesai!** Setiap kali Anda melakukan `git push origin main`, GitHub Actions otomatis mendeploy aplikasi ke cPanel dalam waktu ±30 detik.

#### Cara B: Menggunakan cPanel Git™ Version Control
Jika hosting Anda mendukung fitur Git bawaan cPanel:
1. File [`.cpanel.yml`](file:///.cpanel.yml) sudah tersedia di root proyek.
2. Edit file `.cpanel.yml` pada baris `DEPLOYPATH` sesuai username cPanel Anda:
   ```yaml
   - export DEPLOYPATH=/home/USERNAME_CPANEL/public_html/
   ```
3. Di cPanel, buka menu **Git™ Version Control** &gt; **Create**.
4. Masukkan URL clone GitHub Anda &gt; Klik **Create**.
5. Pada tab **Manage**, klik **Update from Remote** &gt; **Deploy HEAD Commit**.

---

### 3. Setup Database di cPanel

1. Di cPanel, buka menu **MySQL Databases**:
   - Buat database baru (contoh: `cpaneluser_sipede`).
   - Buat user database baru dan berikan hak akses penuh (*ALL PRIVILEGES*).
2. Buka menu **phpMyAdmin** di cPanel:
   - Pilih database yang baru dibuat.
   - Klik tab **Import** &gt; pilih `database/schema.sql`, lalu `database/seed.sql`.
   - Untuk memasukkan 34 anggota DPRD, import `database/inject_anggota_dprd_2024_2029.sql` setelah seed.
   - Jika database sudah berjalan dan tidak ingin instal ulang, import `database/migrate_fleksibel_anggaran.sql` untuk menambahkan jenis kegiatan dan alokasi multi-pos.
3. Di **cPanel File Manager**:
   - Salin berkas `.env.example` menjadi `.env` di folder root situs Anda.
   - Edit file `.env` dan masukkan konfigurasi database cPanel:
     ```env
     CI_ENV=production
     APP_BASE_URL=https://sipede.domainanda.com/
     DB_HOST=localhost
     DB_NAME=cpaneluser_sipede
     DB_USER=cpaneluser_dbuser
     DB_PASS=PasswordDatabaseAnda!
     ENCRYPTION_KEY=ganti-kunci-acak-minimal-32-karakter
     ```

---

## 🔐 Akun Administrator Bawaan

| Username | Role | Password Awal |
| :--- | :--- | :--- |
| **`admin`** | Administrator | **`admin123`** |
| **`superadmin`** | Super Admin | **`setwan2026sipede`** |
