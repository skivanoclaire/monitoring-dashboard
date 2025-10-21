# Monitoring Layanan DKISP Kaltara

Aplikasi **Monitoring Layanan DKISP-APTIka** menampilkan metrik kinerja infrastruktur dan layanan TIK Pemerintah Provinsi Kalimantan Utara dalam bentuk **dashboard web** yang ringan dan mudah dibaca.

**URL produksi:** `https://monitoring.kaltaraprov.go.id/`

## 👀 Gambaran Umum

- **Monitoring VM** — grafik *CPU*, *RAM*, *Disk Usage*, dan *Bandwidth (Download/Upload)* per VM.
- **Pemilih VM (dropdown)** — ganti VM yang dipantau via parameter `?vm=<nama_vm>`.
- **Status Beban Terkini** — ringkasan beban saat ini (CPU 1 menit, RAM, Disk, Bandwidth) + kategori **Normal / Sedang / Tinggi**.
- **Menu Navigasi (Navbar)** — _Virtual Machine_, _Domain & Subdomain_, _Internet Perangkat Daerah_, _Email Resmi Pemerintah_ (area lain yang dipantau/direncanakan).

> Contoh URL: `https://monitoring.kaltaraprov.go.id/dashboard?vm=ciku`

---

## 📊 Metrik yang Ditampilkan

1. **CPU Overview**
   - Estimasi penggunaan CPU (%) dan *load average* (1, 5, 15 menit).
2. **RAM Usage (%)**
   - Persentase penggunaan memori fisik.
3. **Disk Usage (%)**
   - Persentase pemakaian storage utama pada VM/host yang dipilih.
4. **Bandwidth (Mbps)**
   - Lalu lintas **Download (↓)** dan **Upload (↑)** per interval pengiriman data.

**Status Beban (legend di footer):**
- **Normal**: ≤ 50%
- **Sedang**: 51–80%
- **Tinggi**: > 80%

> *Catatan:* Kategori dapat disesuaikan via konfigurasi.

---

## 🧱 Arsitektur Singkat

- **Frontend**: Blade/Tailwind + Chart.js (line chart)  
- **Backend**: PHP (Laravel 10/12) — _sesuaikan dengan `composer.json`_  
- **Sumber Data**: file log metrik yang dikirim berkala dari VM/agent, lalu dibaca & diolah oleh aplikasi.
- **Penyimpanan**: file log (default) / opsi DB untuk historis panjang.
- **Keamanan**: aplikasi hanya *read-only* ke sumber metrik; kredensial akses disimpan di `.env`.

### Sumber Log (contoh yang dipakai di lingkungan produksi)
- NAS: `10.15.11.22` port `3322` user `sampleuser`  
- Path: `/volume/BACKUP_SERVER/logmonitor`  
- Contoh nama file: `ciku_sys_monitor.log`, `sakip_sys_monitor.log`  
- Format baris: timestamp; cpu%; load1; load5; load15; ram%; disk%; down_mbps; up_mbps

> Sesuaikan di konfigurasi jika path/IP berubah.

---

## 📁 Struktur Direktori (ringkas)

