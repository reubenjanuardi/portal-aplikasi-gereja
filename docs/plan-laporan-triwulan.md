# Rencana Fitur — Laporan Keuangan Triwulan

## Tujuan

Menambahkan laporan triwulan pada modul **Keuangan**. Pengguna memilih tahun anggaran dan triwulan, melihat rekapitulasi serta rincian transaksi/akun sesuai periode fiskal GPIB, lalu mencetak laporan ke PDF atau Excel.

Dokumen ini tahap perencanaan; belum mengubah kode aplikasi.

## Aturan periode

Tahun anggaran berjalan dari **1 April sampai 31 Maret** tahun berikutnya. Tahun anggaran diberi label berdasarkan rentang tahun, misalnya **2025–2026**.

| Pilihan | Rentang tanggal |
|---|---|
| Triwulan I | 1 April – 30 Juni |
| Triwulan II | 1 Juli – 30 September |
| Triwulan III | 1 Oktober – 31 Desember |
| Triwulan IV | 1 Januari – 31 Maret |

Rentang tanggal harus dihitung dari tahun anggaran terpilih, bukan tahun kalender saat ini. Label periode yang ditampilkan harus menyertakan tanggal dan tahun, khususnya untuk Triwulan IV yang melintasi tahun kalender.

Anggaran triwulan bersifat kumulatif terhadap anggaran tahunan: Triwulan I = 25%, Triwulan II = 50%, Triwulan III = 75%, Triwulan IV = 100%. Dengan demikian, angka kolom **Anggaran Triwulan** pada rekap adalah batas anggaran kumulatif sampai triwulan terpilih (bukan anggaran satu triwulan tersendiri). Validasi ini mengikuti keterangan pengguna tentang persentase pada template. Implementasi tetap perlu memastikan angka untuk tiap akun dihitung dari `budget` CoA dengan persentase tersebut.

## Alur pengguna

1. Buka **Laporan → Laporan Triwulan** di panel Keuangan.
2. Pilih **Tahun Anggaran** dan **Triwulan I–IV**.
3. Sistem menampilkan periode tanggal yang dihitung otomatis dan laporan.
4. Halaman pertama adalah **rekapitulasi**, lalu halaman berikutnya berisi **detail laporan** mengikuti urutan akun pada sumber data.
5. Pilih **Cetak PDF** atau **Export Excel** untuk periode dan filter yang sedang dipilih.

## Struktur laporan

### Halaman 1 — Rekapitulasi

Ikuti gambar **Template Laporan Triwulan Page1** sebagai acuan tata letak. Struktur yang terbaca:

- Judul laporan, nama jemaat/organisasi, triwulan, rentang tanggal, dan tahun anggaran.
- Dua bagian utama: **Penerimaan** dan **Pengeluaran**.
- Baris akun dengan **No. MA** dan **Uraian Anggaran**.
- Kelompok **Anggaran Setahun (Rp)**, dipecah menjadi **Penerimaan** dan **Pengeluaran**.
- Kelompok anggaran/realisasi periode mengikuti acuan persentase kumulatif: anggaran triwulan dihitung dari anggaran tahunan (25%, 50%, 75%, atau 100% sesuai triwulan terpilih), lalu dibandingkan dengan realisasi sampai akhir periode.
- Total per bagian dan total penerimaan/pengeluaran.
- Saldo awal/kas-bank dan blok tanda tangan/pengesahan **tidak disertakan untuk tahap ini**.

### Halaman berikutnya — Detail

Gunakan foto **Template Detail Laporan Triwulan** sebagai referensi susunan detail. Header foto tidak seluruhnya terbaca; kolom-kolom berikut mengikuti spesifikasi pengguna.

- Judul detail, triwulan, rentang tanggal, tahun anggaran, dan nama organisasi.
- Hirarki akun: kode dan nama mata anggaran; pertahankan urutan CoA.
- Setelah kolom **Mata Anggaran**, tampilkan kelompok **Anggaran Tahunan** dengan dua subkolom: **Penerimaan/Masuk** dan **Pengeluaran/Keluar**.
- Berikutnya tampilkan tiap bulan pada triwulan terpilih; setiap bulan memiliki subkolom **Penerimaan/Masuk** dan **Pengeluaran/Keluar**. Bulan dinamis sesuai triwulan (contoh Triwulan I: April, Mei, Juni).
- Kolom paling akhir **Real to Plan**: satu pasang subkolom persentase **Masuk** dan **Keluar** untuk keseluruhan triwulan (bukan per bulan). Rumus: realisasi kumulatif sepanjang triwulan dibagi anggaran kumulatif triwulan (25%, 50%, 75%, atau 100% dari anggaran tahunan sesuai triwulan terpilih), dikali 100; bila anggaran nol, tampilkan tanda tidak tersedia.
- Pertahankan subtotal kelompok dan total akhir; baris induk menjumlahkan akun anak satu kali.
- Format angka rupiah konsisten, angka rata kanan, kode/nama akun mudah dibaca, serta header tabel berulang pada halaman PDF lanjutan.

Angka persentase triwulan pada template rekap adalah kumulatif: 25%, 50%, 75%, 100%. Kolom Real to Plan pada detail berupa satu angka per baris untuk seluruh triwulan, bukan per bulan: realisasi kumulatif triwulan dibanding anggaran kumulatif triwulan (persentase kumulatif × anggaran tahunan). Jangan menghitung persentase dengan penyebut nol, dan tandai tidak tersedia bila anggaran tidak ada.

## Data dan aturan kalkulasi

- Sumber transaksi: voucher bertanggal dalam periode triwulan dan detail transaksi per akun.
- Sumber hierarki serta anggaran tahunan: **Chart of Accounts** (`chart_of_accounts`, `kode_akun`, `nama_akun`, `parent_code`, `kategori`, `budget`, `is_postable`), berdasarkan kode aplikasi yang diperiksa.
- Agregasi detail: jumlahkan nominal transaksi akun per bulan; total triwulan merupakan jumlah bulan dalam triwulan. Baris induk harus menjumlahkan anak satu kali saja agar tidak terjadi hitung ganda.
- Rekap: kelompokkan dan jumlahkan menurut kategori CoA. Saldo awal/kas-bank tidak menjadi bagian laporan tahap ini.
- Pertahankan presisi desimal; hindari pembulatan saat agregasi. Pembulatan hanya pada tampilan apabila format laporan mengharuskan, tanpa mengubah nilai sumber.
- Tentukan perlakuan voucher batal/dihapus dan jenis voucher berdasarkan aturan yang berlaku pada laporan saat implementasi.

## Cakupan fitur

### Wajib

- Pilihan tahun anggaran dan triwulan.
- Perhitungan periode fiskal April–Maret yang benar.
- Tampilan rekapitulasi halaman awal dan detail setelahnya.
- Cetak PDF dan ekspor Excel dengan data/periode yang sama dengan tampilan.
- Akses mengikuti izin laporan keuangan yang berlaku.
- Tampilan kosong yang jelas bila belum ada transaksi.

### Di luar tahap awal

- Mengubah anggaran atau jurnal dari halaman laporan.
- Mengedit struktur akun khusus untuk laporan.
- Perbandingan antartriwulan atau analisis tren.

## Rancangan teknis untuk implementasi

- Tambahkan halaman Filament laporan baru dalam grup navigasi **Laporan**; ikuti pola halaman laporan yang sudah ada.
- Pisahkan kalkulasi periode triwulan dan agregasi report data dalam service agar tampilan, PDF, dan Excel memakai satu sumber logika.
- Tambahkan endpoint/route ekspor yang tervalidasi dan membawa parameter tahun anggaran serta triwulan; terapkan otorisasi laporan.
- Buat template PDF berorientasi lanskap bila tabel lebar; evaluasi ukuran kertas setelah uji cetak.
- Buat ekspor Excel dengan sheet rekapitulasi dan detail, atau tata letak setara yang mempertahankan urutan bagian dan subtotal.
- Jangan menambah skema database kecuali implementasi membuktikan data anggaran bulanan/triwulan memang belum tersedia dan dibutuhkan.

## Kriteria penerimaan

1. Pilihan TA 2025–2026 menghasilkan Triwulan I 1 April–30 Juni 2025, II 1 Juli–30 September 2025, III 1 Oktober–31 Desember 2025, dan IV 1 Januari–31 Maret 2026.
2. Transaksi tepat di awal/akhir periode masuk; transaksi di luar periode tidak masuk.
3. Ringkasan, detail, PDF, dan Excel menggunakan filter dan hasil kalkulasi yang sama.
4. Total akun induk/subtotal tidak menggandakan nilai akun anak.
5. PDF menempatkan rekapitulasi pada halaman pertama, diikuti detail; tabel berulang dengan baik jika detail melampaui satu halaman.
6. Pengguna tanpa izin laporan tidak dapat membuka halaman maupun endpoint ekspor.
7. Kasus tanpa transaksi, nilai desimal, serta batas pergantian tahun diuji.

## Tahapan implementasi

1. **Kunci detail aturan Real to Plan kumulatif** (realisasi triwulan vs anggaran kumulatif triwulan) dan validasi kategori akun yang dipakai.
2. **Rancang service dan periode fiskal**: fungsi murni pemetaan tahun anggaran/triwulan ke tanggal; uji semua batas tanggal.
3. **Bangun tampilan laporan**: filter TA/triwulan, rekap, detail bertingkat, subtotal.
4. **Bangun ekspor**: PDF berhalaman dengan rekap sebagai halaman pertama; Excel dengan struktur konsisten.
5. **Uji dan review**: data tanpa transaksi, akun bertingkat, nilai desimal, boundary tanggal, akses RBAC, konsistensi ekspor, dan format cetak.

## Keputusan pengguna

1. **Anggaran triwulan kumulatif**: Triwulan I = 25% anggaran tahunan; Triwulan II = 50%; Triwulan III = 75%; Triwulan IV = 100%.
2. **Kolom detail**: setelah Mata Anggaran, Anggaran Tahunan dipecah menjadi Penerimaan/Masuk dan Pengeluaran/Keluar; setelahnya nilai masuk/keluar untuk setiap bulan dalam triwulan; paling akhir Real to Plan berisi **satu pasang persentase Masuk/Keluar untuk keseluruhan triwulan** (realisasi kumulatif triwulan ÷ anggaran kumulatif triwulan). Tidak ada kolom persentase per bulan.
3. **Saldo awal dan blok tanda tangan/pengesahan** tidak diperlukan untuk tahap ini.
4. **Data historis**: catatan sistem dimulai dari TA 2026–2027 Triwulan II; Triwulan I belum memiliki transaksi backdate. UI tetap perlu menyajikan Triwulan I dengan keadaan kosong yang jelas, tidak membuat atau mengarang data historis.

### Ketetapan teknis

- Real to Plan memakai satu angka per baris untuk seluruh triwulan: realisasi kumulatif triwulan ÷ anggaran kumulatif triwulan. Penyebut memakai persentase kumulatif (25/50/75/100% × `budget` tahunan), bukan pembagian per bulan. Persentase dihitung hanya bila penyebut lebih dari nol; selain itu tampilkan tanda tidak tersedia.

## Dasar perencanaan

- Empat gambar template yang disertakan pengguna; foto halaman pertama terbaca sebagai rekap Penerimaan/Pengeluaran dan posisi saldo awal, sedangkan foto detail menunjukkan hirarki akun namun beberapa header tidak terbaca.
- Implementasi yang diperiksa: halaman Filament `app/Filament/Pages/LaporanRealisasiMingguan.php`, service `app/Services/LaporanRealisasiService.php`, model `app/Models/ChartOfAccount.php`, serta pola route ekspor di `routes/web.php`.
- Planning ini belum mengubah kode aplikasi dan belum memverifikasi data anggaran aktual di database.
