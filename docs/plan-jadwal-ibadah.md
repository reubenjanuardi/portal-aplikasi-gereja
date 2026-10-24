# Plan Modul Jadwal & Ibadah — GPIB Hosiana Jakarta Pusat

Status: usulan untuk review, belum implementasi aplikasi.

## 1. Arah produk

Modul menggantikan spreadsheet sebagai sumber jadwal resmi, bukan sekadar memindahkan kalender ke web. Cara kerja tetap mengikuti pelayanan: susun petugas tiga bulan lebih awal, konfirmasi saat persiapan ibadah, ganti petugas bila berhalangan, kemudian catat siapa yang benar-benar melayani.

**Prinsip utama: pisahkan rencana awal, jadwal berlaku, dan realisasi.** Mengganti nama tidak boleh menghapus jejak penugasan sebelumnya. Jadwal yang sudah lewat tidak otomatis dianggap terlaksana.

Target MVP: petugas kantor/koordinator dapat menyusun, menerbitkan, merevisi, mencetak, dan merekap jadwal tanpa harus kembali mengedit Excel sebagai sumber utama. Konfirmasi dapat dicatat operator; setiap pelayan tidak wajib mempunyai akun portal.

## 2. Dasar faktual

### 2.1 Workbook yang diperiksa

Sumber: `Jadwal Pelayanan 2026 (2)-3.xlsx`, dibaca tanpa mengubah file. Inventaris XML workbook menunjukkan **7 sheet**, termasuk **2 sheet tersembunyi** yang tidak muncul pada ekstraksi teks dokumen biasa.

| Nama sheet persis pada sumber | Keadaan | Fungsi yang terlihat |
|---|---|---|
| `JADWAL JAN - MAR ` | Tersembunyi | Jadwal Januari–Maret 2026 beserta rekap frekuensi |
| `JADWAL APR-JUN` | Tersembunyi | Jadwal April–Juni 2026, termasuk ibadah khusus |
| `ListNama` | Terlihat | Beberapa daftar nama untuk referensi |
| `JADWAL JUL-SEP` | Terlihat | Rencana Juli–September 2026 |
| `Realisasi JADWAL JUL-SEP` | Terlihat | Jadwal berlabel realisasi dengan perubahan nama, tanggal/jumlah kegiatan, dan rekap frekuensi |
| `JADWAL OCT-DES ` | Terlihat | Rencana Oktober–Desember 2026 serta ibadah 1 Januari 2027 |
| `Sheet2` | Terlihat | Kosong |

Spasi akhir pada beberapa nama sheet memang ada pada file. Penamaan sheet tidak boleh dijadikan satu-satunya sumber periode.

Temuan isi:

- `ListNama!B2:B20` memuat 19 nama pada daftar utama. Ada daftar lain untuk kelompok nama; fungsi kelayakan setiap daftar belum dinyatakan pada header, sehingga tidak boleh langsung dijadikan aturan jabatan.
- IHM pagi dan sore memakai kolom berbeda pada tanggal sama: contoh 09.00 dan 18.00 WIB.
- Jenis lain: Ibadah Keluarga, Pelkat PKLU, PKP, PKB, GP, Doa Karyawan, serta ibadah khusus hari raya/akhir-awal tahun.
- Peran meliputi Pelayan Firman, Pelayan Liturgi, P-1, `P-2, P-3`, `P-4, P-6`, `P-5, P-7`, P-8/P-9, petugas perjamuan, kolektan, dan tambahan. Ibadah khusus dapat memisahkan P-2 sampai P-7 serta menambah P-10.
- Label `Pelayan Firman`, `PF`, dan `P F` bervariasi; demikian juga `Pelayan Liturgi`, `PL`, dan `P L`.
- Baris `Kolektan`, `P. Perjamuan`, dan `Tambahan` dapat berulang. Itu slot berbeda, bukan duplikasi baris yang boleh dihapus.
- Penerima Ibadah dapat berupa keluarga, bukan individu petugas. Sel tersebut juga berisi catatan seperti `PEMBINAAN`, sehingga parser harus membedakan tuan rumah dari keterangan kegiatan.
- Pelayan Firman bukan hanya pendeta internal. Sumber memuat penatua, diaken, pendeta tamu, dan penugasan seperti `MUPEL JAKARTA PUSAT` atau `PERTUKARAN PGIW JAKARTA`.
- Penugasan kelompok muncul, misalnya `Pelkat GP` pada kolektan. Identitas kelompok tidak boleh dipaksa menjadi individu.
- Ada rumus referensi nama dan rekap `SUMPRODUCT`; nilai formula bukan data input baru yang perlu diimpor sebagai tugas tambahan.
- Contoh perubahan nyata: `JADWAL JUL-SEP!B8` berisi `Pnt. Paul L Awuy`, sedangkan `Realisasi JADWAL JUL-SEP!B8` berisi `Pnt. Louise E Suhendro - W`, untuk P-2/P-3 pada 5 Juli 2026 pukul 09.00. Kolektan terkait pada `B14` juga berbeda. Hubungan ini mendukung kebutuhan riwayat penggantian dan peran rangkap.
- Pada Juli–September, Doa Karyawan di sheet rencana dan realisasi memiliki susunan kolom berbeda. Pemasangan data tidak boleh memakai posisi kolom saja.

**Batas kesimpulan:** nama sheet “Realisasi” belum membuktikan bahwa semua isinya sudah diverifikasi sebagai kehadiran aktual. Saat migrasi, operator harus menentukan apakah sheet tersebut realisasi terverifikasi atau revisi jadwal.

### 2.2 Masalah data yang perlu ditangani saat impor

| Temuan | Bukti sumber | Perilaku sistem yang diusulkan |
|---|---|---|
| Hari dan tanggal tidak cocok | `JADWAL JUL-SEP!H16`: `Kamis, 17 Agustus 2026`; kalender menunjukkan Senin | Tampilkan peringatan; minta konfirmasi tanggal yang benar, jangan menggeser tanggal diam-diam |
| Jadwal tampak ganda | `JADWAL JUL-SEP!E92` dan `F92`: Doa Karyawan, 21 Juli 2026, 09.00 WIB | Tahan duplikasi untuk review; cek apakah salah tanggal atau kegiatan berbeda |
| Hari Natal II tidak cocok | `JADWAL OCT-DES !D101`: `Jumat, 26 Desember 2026`; kalender menunjukkan Sabtu | Review sebelum commit impor |
| Hari pada jadwal tersembunyi tidak cocok | `JADWAL APR-JUN!C71:F71`: 9, 16, 23, 30 Juni 2026 ditulis Rabu, tetapi tanggal-tanggal itu Selasa | Jangan menganggap arsip lama bersih tanpa pemeriksaan |
| Kegiatan lintas periode | `JADWAL OCT-DES !F101`: 1 Januari 2027 | Tempatkan pada periode berdasarkan tanggal aktual; simpan asal batch Oktober–Desember sebagai provenance |
| Variasi penulisan nama | Contoh `Pnt. Roy Y. Rumuat` dan `Pnt. Roy Yulius Rumuat` | Tawarkan kandidat identitas yang sama, tetapi penggabungan wajib disetujui operator |
| Kosong dan tanda `-` bercampur | Slot P-8/P-9 | Bedakan belum diisi dari tidak berlaku melalui template dan review |

Perhitungan hari diverifikasi dengan kalender programatik. Total kegiatan hasil parser eksploratif tidak dipakai sebagai jumlah final migrasi: layout antar-sheet berbeda dan semua blok harus direkonsiliasi pada dry run impor.

### 2.3 Kondisi portal sekarang

Pemeriksaan source, bukan asumsi dari label launcher:

- `resources/js/Pages/Dashboard.vue:78` mendefinisikan modul `ibadah`, URL `/ibadah`, tetapi masih `active: false`.
- `routes/web.php` belum mempunyai route modul ibadah. Provider panel terdaftar pada `bootstrap/providers.php` baru Keuangan dan Settings.
- `database/seeders/RbacSeeder.php:56` sudah mendefinisikan `module.ibadah`. Ini bukti definisi source, bukan verifikasi bahwa semua database lingkungan sudah di-seed.
- `app/Models/User.php:44` sudah mendukung akses panel berbasis `module.{panelId}` dan pengguna aktif.
- `app/Models/ActivityLog.php:45` menyediakan audit log yang dapat dipakai ulang.
- Belum ada model master jemaat atau jadwal pada `app/Models` yang diperiksa. Modul ini tidak perlu menunggu Modul Data Jemaat.
- Versi `composer.lock`: Laravel `v13.23.0`, Filament `v5.7.0`, Livewire `v4.3.3`, OpenSpout `v4.32.0`, DomPDF `v3.1.2`, Spatie Permission `8.3.0`. Kebutuhan PHP di manifest `^8.4`.
- Portal sudah memakai Vue/Inertia untuk launcher dan Filament untuk panel administrasi. Rekomendasi: panel Filament tersendiri pada `/ibadah`, autentikasi portal yang sama, bukan aplikasi terpisah.

## 3. Batas cakupan

### MVP — wajib untuk menggantikan spreadsheet

1. Master pelayan internal, tamu, kelompok, dan penugasan organisasi yang belum mempunyai nama pelayan.
2. Master jenis ibadah, template slot tugas, lokasi/tuan rumah, dan periode tiga bulanan.
3. Buat jadwal berulang dan kegiatan khusus; isi nama jauh hari melalui matriks jadwal.
4. Impor workbook lama melalui preview dan rekonsiliasi, bukan upload langsung masuk jadwal resmi.
5. Konfirmasi kesediaan yang dapat dicatat operator, ketidaktersediaan, dan penggantian petugas dengan alasan.
6. Penerbitan jadwal resmi beserta nomor revisi; revisi tidak menimpa rencana awal.
7. Pencatatan pelayan aktual dan penyelesaian ibadah.
8. PDF, Excel, teks ringkasan siap salin untuk WhatsApp, serta rekap frekuensi.
9. Hak akses per tindakan, riwayat, dan perlindungan konflik edit.

### Lanjutan — bukan syarat peluncuran

- Konfirmasi mandiri pelayan melalui akun portal.
- Pengingat dan notifikasi otomatis melalui penyedia WhatsApp/email yang disepakati; belum diasumsikan tersedia.
- Usulan rotasi berdasarkan frekuensi dan kesediaan, tetap diputuskan koordinator.
- Pengelolaan lengkap organis, kantoria, multimedia, prokes, dan lampiran tata ibadah. Slot dapat ditambahkan sejak MVP jika dibutuhkan, tetapi workbook yang diperiksa belum menjadi bukti rincian alurnya.
- Publikasi jadwal jemaat tanpa login, integrasi Warta Jemaat, Data Jemaat, Pelkat, dan kalender eksternal.
- Penyusunan liturgi, pemesanan ruangan menyeluruh, pengelolaan persembahan/honor, dan absensi umum bukan cakupan MVP.

**Batas antar-modul:** Jadwal & Ibadah menjadi sumber agenda ibadah dan penugasannya. Modul Pelkat kelak memakai agenda yang sama untuk ibadah kategorial, bukan membuat tabel jadwal kedua. Keuangan tetap sumber transaksi; penjadwalan tidak otomatis membuat voucher.

## 4. Alur operasional

### 4.1 Menyusun periode tiga bulanan

1. Koordinator membuat periode dengan tanggal awal–akhir dan label jelas, misalnya `Juli–September 2026`.
2. Pilih template dan pola: mingguan, minggu ke-n, beberapa hari tertentu, atau kegiatan satu kali. Tanggal libur/penutupan menjadi pengecualian; sistem tidak menganggap semua bulan wajib berisi kegiatan.
3. Sistem membuat daftar kegiatan, bukan otomatis menentukan semua nama.
4. Koordinator mengisi nama melalui matriks, salin pola minggu sebelumnya, atau impor. Salin hanya penugasan, bukan status konfirmasi/realisasi.
5. Sistem menampilkan slot kosong, ketidaktersediaan, potensi bentrok, dan rekap beban tugas.
6. Pejabat berwenang meninjau dan menerbitkan. Penerbitan pertama menyimpan snapshot rencana awal.

Label bulan menjadi default sampai gereja menetapkan apakah penomoran triwulan pelayanan mengikuti tahun kalender atau tahun gerejawi April–Maret. Jangan otomatis memakai penomoran fiskal Modul Keuangan.

### 4.2 Persiapan ibadah

1. Buka daftar persiapan minggu terpilih: kegiatan, tempat, semua petugas, dan status kesediaan.
2. Catat konfirmasi dari telepon/WhatsApp/rapat persiapan: bersedia, berhalangan, atau belum menjawab; simpan waktu dan pencatat.
3. Bila berhalangan, pilih pengganti. Sistem menampilkan kandidat berdasarkan kelayakan, ketidaktersediaan, bentrok, dan beban tugas, tanpa mengganti otomatis.
4. Masukkan alasan perubahan. Status konfirmasi penugasan baru kembali belum dikonfirmasi.
5. Terbitkan revisi. Detail, ekspor, dan ringkasan resmi memakai revisi terbit terbaru yang sama.
6. Perubahan langsung saat hari ibadah tetap diperbolehkan oleh petugas berizin, dengan alasan dan riwayat wajib.

Konfirmasi bukan kewajiban login semua pelayan. Pada MVP, operator sah mencatat jawaban dengan kanal sumber. Setelah konfirmasi mandiri tersedia, pelayan hanya boleh merespons penugasan miliknya; penggantian tetap wewenang koordinator.

### 4.3 Sesudah ibadah

1. Operator meninjau jadwal berlaku dan mencatat siapa yang benar-benar melayani.
2. Aksi `Salin jadwal berlaku ke realisasi` hanya mengisi usulan; operator tetap harus menyimpan konfirmasi realisasi.
3. Catat pengganti mendadak, tidak hadir, tugas tidak dilaksanakan, atau ibadah dibatalkan sesuai kejadian.
4. Tutup ibadah setelah tugas wajib berstatus jelas. Rekap realisasi hanya menghitung tugas yang dikonfirmasi terlaksana.
5. Koreksi setelah penutupan memerlukan izin khusus dan alasan; nilai lama tetap ada dalam riwayat.

## 5. Status dan versi

Pisahkan sumbu status agar “terbit” tidak berarti “semua petugas bersedia”.

| Objek | Status/keadaan yang diusulkan | Makna |
|---|---|---|
| Periode | Draft, Aktif, Arsip | Wadah penyusunan dan filter; bukan bukti semua kegiatan selesai |
| Kegiatan | Draft, Terbit, Selesai, Dibatalkan | Siklus kegiatan ibadah |
| Revisi kegiatan | Draft revisi, Terbit | Revisi yang belum terbit tidak mengganti jadwal resmi |
| Slot tugas | Wajib, Opsional, Tidak berlaku | Menentukan kelengkapan; tidak sama dengan hadir/tidak hadir |
| Konfirmasi penugasan | Belum dikonfirmasi, Bersedia, Berhalangan | Respons terhadap orang/kelompok dan revisi penugasan tertentu |
| Realisasi tugas | Belum diverifikasi, Melayani, Tidak hadir, Tidak dilaksanakan | Fakta setelah kegiatan; identitas pelayan aktual tersimpan terpisah |

Aturan:

- **Rencana awal:** snapshot penerbitan pertama, tidak diubah oleh revisi.
- **Jadwal berlaku:** snapshot revisi terbit terakhir.
- **Draft revisi:** perubahan yang sedang disiapkan, hanya terlihat bagi pengelola. Penonton biasa masih melihat jadwal berlaku.
- **Realisasi:** fakta terverifikasi, dapat berbeda dari rencana dan jadwal berlaku.
- Publish dilakukan per kegiatan, dengan aksi massal untuk satu periode. Tiap kegiatan tetap memiliki versi sendiri agar perubahan satu ibadah tidak menerbitkan ulang seluruh tiga bulan.
- Slot wajib kosong atau penugasan belum jelas memblokir publish, kecuali pejabat berizin menerbitkan sebagai sementara dengan alasan. Belum konfirmasi menimbulkan peringatan, bukan otomatis memblokir jadwal tiga bulan di muka.
- Penugasan organisasi seperti MUPEL dapat diterbitkan sebagai `Menunggu nama dari organisasi` dengan persetujuan. Itu bukan individu yang sudah bersedia dan tidak dihitung sebagai frekuensi individu.
- Pembatalan kegiatan yang sudah terbit tidak memakai hard delete. Kegiatan draft yang tidak diperlukan boleh dihapus sesuai izin.
- Revisi mencatat pelaku, waktu, alasan, perubahan sebelum/sesudah, dan versi. Export mencantumkan versi serta waktu terbit; tidak mencampur draft dengan resmi.

## 6. Aturan domain

### 6.1 Pelayan dan kelayakan

- Identitas pelayan terpisah dari `users`: nama tampil, jenis individu/kelompok/organisasi, gelar/jabatan, asal jemaat/instansi, status aktif, dan kontak opsional yang dibatasi akses.
- Tautan `user_id` nullable; seorang pelayan tidak wajib punya email/login. Jangan membuat akun fiktif dari nama di Excel.
- Kelayakan tugas diatur koordinator. Gelar Pdt./Pnt./Dkn. bukan satu-satunya aturan otomatis; sumber memperlihatkan beberapa jabatan mengisi Pelayan Firman dan variasi penugasan lain.
- Nama mentah sumber disimpan sebagai alias/provenance. Fuzzy matching hanya memberi saran, tidak menggabungkan orang secara diam-diam.
- Nama yang sudah digunakan tidak dihapus dari sejarah; gunakan penonaktifan dan snapshot nama pada revisi/realisasi.

### 6.2 Template dan peran rangkap

- Template menentukan urutan slot, label cetak, jumlah slot, kewajiban, kelayakan, serta kombinasi peran yang boleh dirangkap.
- Slot `P-2, P-3` merepresentasikan satu penugasan gabungan pada template IHM, bukan dua pelayan yang harus berbeda. Template ibadah khusus boleh memisah P-2 dan P-3.
- `Kolektan 1` dan `Kolektan 2`, atau `Tambahan 1` dan seterusnya, mempunyai identitas slot unik walaupun label cetaknya sama.
- Satu orang boleh mengisi beberapa slot dalam ibadah sama bila kombinasi disetujui. Ini tidak otomatis dianggap bentrok.
- Hubungan seperti P-1 merangkap Kolektan 1 dapat dibuat sebagai aturan template. Saat ganti petugas, sistem menawarkan pembaruan slot terkait dan meminta konfirmasi; jangan diam-diam mengganti slot independen.
- P-8/P-9 dan `P. Perjamuan` tidak otomatis disamakan. Arti dan pemetaan dikonfirmasi penanggung jawab liturgi.
- Perubahan template hanya berlaku untuk kegiatan baru. Kegiatan lama menyimpan snapshot slot agar sejarah tidak berubah.

### 6.3 Kalender dan bentrok

- Tanggal dan waktu terstruktur; hari dihitung sistem. Tampilan WIB/`Asia/Jakarta` konsisten, termasuk ekspor.
- Simpan jam mulai dan selesai, atau durasi template yang disepakati. Tanpa jam selesai, sistem hanya memberi peringatan potensi bentrok; jangan mengklaim overlap akurat.
- Bentrok lintas kegiatan diperiksa dari rentang waktu, bukan hanya hari sama. Ibadah 09.00 dan 18.00 tidak otomatis bentrok.
- Pelayan berhalangan pada rentang tertentu tidak dapat dipilih tanpa override berizin dan alasan. Tanggal cuti/ketidaktersediaan berbeda dari penolakan satu penugasan.
- Bentrok lokasi tetap menjadi peringatan sampai data ruang/tempat dan durasi cukup lengkap.
- Penugasan kelompok tidak membuktikan semua anggotanya tersedia. Jika nama individu belum dirinci, keterbatasan pemeriksaan bentrok harus terlihat.
- Reschedule menyimpan tanggal lama dan baru; cek ulang kesediaan petugas yang terdampak. Bila lintas periode, kegiatan berpindah ke periode tanggal baru tanpa menggandakan identitasnya.

### 6.4 Periode lintas tahun

- Periode utama kegiatan ditentukan tanggal aktual dan tidak tumpang tindih dengan periode lain.
- Ibadah 1 Januari 2027 masuk periode Januari–Maret 2027, walaupun diimpor dari sheet Oktober–Desember 2026. Preview meminta persetujuan pembuatan/pemilihan periode tujuan.
- Asal batch impor tetap disimpan. Ekspor paket Natal–Tahun Baru boleh memuat rentang lintas tahun tanpa membuat duplikat kegiatan.
- Jangan menerbitkan kegiatan berulang di luar batas periode tanpa preview dan pilihan eksplisit.

## 7. Halaman modul

| Menu | Isi utama |
|---|---|
| Ringkasan | Ibadah terdekat, slot wajib kosong, belum konfirmasi, petugas berhalangan, revisi belum terbit, realisasi belum dicatat |
| Jadwal Periode | Filter periode/jenis/status; matriks tugas versus tanggal dan jam; aksi isi/salin/ganti; rekap beban tugas |
| Kalender & Daftar | Kalender bulanan/mingguan dan daftar kegiatan; kegiatan khusus, pindah tanggal, pembatalan |
| Persiapan Ibadah | Detail satu kegiatan, konfirmasi, pengganti, tempat/tuan rumah, catatan, lampiran opsional, kelengkapan |
| Realisasi | Petugas aktual, catatan ketidakhadiran, tutup kegiatan, pembandingan dengan jadwal |
| Pelayan & Ketersediaan | Individu internal/tamu, kelompok/organisasi, kelayakan tugas, alias, ketidaktersediaan |
| Master Ibadah | Jenis kegiatan, template slot, lokasi, durasi, aturan peran rangkap |
| Impor & Ekspor | Preview workbook, pemetaan, error, commit batch, PDF/Excel/ringkasan teks |
| Laporan & Riwayat | Frekuensi rencana/realisasi, selisih penugasan, histori revisi dan penerbitan |

Matriks desktop mempertahankan pola yang dikenal pengguna Excel: baris tugas, kolom tanggal dan sesi, nama dapat dipilih melalui pencarian. Gunakan header tanggal dan label tugas tetap terlihat saat scroll. Di HP, default daftar per tanggal dan detail ibadah; jangan memaksa tabel tiga bulan mengecil sampai tidak terbaca. Kalender awal tidak wajib bergantung plugin baru.

## 8. Rancangan data dan teknis

Nama tabel/kelas berikut **usulan baru**, bukan klaim sudah tersedia di repository.

| Entitas/tabel usulan | Data/tanggung jawab inti |
|---|---|
| `ibadah_periods` | Label, tanggal awal/akhir, status; penomoran gerejawi opsional setelah disepakati |
| `ibadah_participants` | Individu/kelompok/organisasi, nama, jabatan/asal, aktif, kontak terbatas, `user_id` nullable |
| `ibadah_participant_aliases` | Nama sumber dan hasil pencocokan identitas yang disetujui |
| `ibadah_types` | Jenis ibadah dan template default |
| `ibadah_templates`, `ibadah_template_slots` | Susunan slot, label, kode gabungan, jumlah slot, kewajiban, aturan rangkap dan kelayakan |
| `ibadah_locations` | Tempat/ruang dan alamat terbatas; tuan rumah tetap data kegiatan, bukan dipaksa sebagai pelayan |
| `ibadah_events` | Identitas kegiatan, periode berdasarkan tanggal, jenis, waktu, lokasi/tuan rumah, tema/catatan, status, versi edit |
| `ibadah_event_slots` | Snapshot susunan tugas untuk kegiatan; kunci slot stabil dan urutan tampil |
| `ibadah_assignments` | Penugasan kerja terbaru per slot, status konfirmasi dan versinya, waktu/pencatat, catatan penggantian |
| `ibadah_revisions` | Snapshot immutable rencana awal dan setiap penerbitan kegiatan; nomor versi, penerbit, waktu, alasan |
| `ibadah_realizations` | Hasil per slot, pelayan aktual, status, pemeriksa/waktu, jejak koreksi; bukan sekadar flag kegiatan selesai |
| `ibadah_unavailabilities` | Pelayan, rentang waktu, alasan singkat terbatas akses |
| `ibadah_import_batches`, `ibadah_import_rows` | File privat, sheet/sel, data mentah, pemetaan, error, keputusan operator, hasil commit |

Rincian normalisasi, pivot kelayakan, dan representasi aturan template difinalkan pada desain skema. Jangan membuat tabel kosong untuk integrasi masa depan yang belum dibutuhkan.

Ketetapan teknis:

- Tambah `IbadahPanelProvider` dengan ID/path `ibadah`; namespace resource/page khusus `App\Filament\Ibadah`. Daftarkan di `bootstrap/providers.php`.
- Provider Keuangan sekarang men-discover `App\Filament\Resources` dan `App\Filament\Pages` secara luas. Pastikan resource ibadah tidak ikut terdaftar di Keuangan: namespace terpisah, registrasi eksplisit atau pengecualian panel, lalu uji navigasi kedua panel. Jangan refactor Keuangan tanpa kebutuhan.
- Pakai session/guard `web`, login portal, profil/logo, pola UI dan RBAC yang sudah ada. Aktifkan launcher `/ibadah` hanya setelah route dan permission teruji.
- Pisahkan service penjadwalan, penggantian/konfirmasi, penerbitan, realisasi, impor, dan report; UI/export tidak menggandakan aturan bisnis.
- Snapshot revisi memuat tanggal, sesi, slot, identitas/nama tampil, lokasi serta catatan publik yang diperlukan. Membuka revisi lama tidak mengambil nama master terkini sebagai pengganti snapshot.
- `ActivityLog` dipakai ulang untuk audit tindakan, tetapi bukan pengganti snapshot rencana/revisi dan relasi realisasi.
- Penggantian, publish, dan penutupan dalam transaksi DB. Gunakan kontrol versi optimistis dan lock saat commit agar dua operator tidak saling menimpa atau menerbitkan nomor revisi sama.
- Constraint unik slot per kegiatan, satu penugasan kerja per slot, satu realisasi per slot, serta nomor revisi unik per kegiatan. Kandidat duplikat kegiatan berdasarkan jenis+waktu+lokasi wajib ditinjau, bukan digabung otomatis bila identitas kegiatan berbeda.
- Indeks pada tanggal/status kegiatan, periode, participant penugasan, kegiatan+slot, dan rentang ketidaktersediaan sesuai query yang dibuktikan.
- Gunakan query terpaginasikan/eager loading untuk matriks dan laporan. Cache belum wajib untuk skala MVP; jika ditambah, invalidasi harus spesifik modul, bukan `Cache::flush()`.
- OpenSpout sudah tersedia melalui dependency tree dan dipakai ekspor saat ini. Lakukan spike pembacaan workbook bermacam layout/merged cells sebelum memilih parser. Jika butuh dependency baru seperti PhpSpreadsheet, dokumentasikan kebutuhan dan cek kompatibilitas PHP/Laravel; jangan menganggap sudah terpasang.
- PDF memakai DomPDF yang sudah ada. File impor/lampiran disimpan privat, validasi ukuran/tipe, akses unduh berizin. Jangan menaruh daftar kontak/alamat keluarga pada URL publik.

## 9. Hak akses

Usulan peran operasional; nama pejabat/pemegang peran ditetapkan gereja.

| Peran | Hak default |
|---|---|
| Pengelola Jadwal | Master, draft periode, impor preview/commit yang diizinkan, penugasan, catat jawaban, penggantian, realisasi |
| Penyetuju/Penerbit | Review, publish/revisi resmi, override dengan alasan, koreksi arsip/realisasi |
| Pembaca Internal | Hanya jadwal terbit dan ekspor yang diizinkan, tanpa kontak/alasan pribadi |
| Pelayan — tahap lanjutan | Jadwal terbit dan respons penugasan sendiri; tidak dapat mengganti petugas lain |
| Super Admin | Mengikuti mekanisme portal, tetap tercatat dalam audit |

Pertahankan `module.ibadah`; tambahkan permission terpisah, misalnya `ibadah.jadwal.view`, `ibadah.jadwal.manage`, `ibadah.jadwal.publish`, `ibadah.konfirmasi.manage`, `ibadah.realisasi.manage`, `ibadah.arsip.correct`, `ibadah.master.manage`, `ibadah.import`, `ibadah.export`, dan `ibadah.history.view`.

Izin ditegakkan pada page/resource/action dan endpoint ekspor/impor, bukan hanya menyembunyikan menu. Pengguna tidak aktif tetap ditolak. Menambah izin modul ini tidak boleh mengubah role pengguna atau menambah hak Keuangan.

## 10. Migrasi spreadsheet

### Tahap transisi

1. Sepakati tanggal cutover. Sebelum cutover, workbook menjadi sumber kerja; sesudah cutover, portal menjadi sumber resmi untuk kegiatan yang disepakati.
2. Mulai dari satu periode yang akan dipakai operasional. Sheet historis dan tersembunyi menjadi pilihan eksplisit, tidak otomatis diimpor semua.
3. Upload file ke staging privat. Tampilkan seluruh sheet beserta status tersembunyi/kosong dan pilihan rencana/revisi/realisasi.
4. Parse blok tanggal, jam, kategori, slot dan nama. Isi tanggal sel gabungan hanya dalam rentang merge yang sah; jangan menyebarkan tanggal ke kolom lain dengan forward-fill global.
5. Normalisasi label peran melalui kamus yang disetujui. Simpan teks asli dan posisi sel. Baris tanpa label di bawah Pelayan Firman merupakan keterangan asal/jabatan pada beberapa blok, bukan petugas kedua otomatis.
6. Cocokkan identitas dengan master/alias; orang baru, kelompok, dan organisasi yang belum pasti masuk antrean review. Jangan otomatis menerima fuzzy match.
7. Tampilkan error/potensi duplikat/tanggal lintas periode/kosong versus tidak berlaku. Peringatan hari-tanggal tidak boleh diperbaiki dengan tebakan.
8. Hubungkan rencana dan realisasi berdasarkan identitas kegiatan: tanggal, waktu, jenis, lokasi jika tersedia; kemudian kunci slot. Jangan berdasarkan koordinat kolom. Kegiatan yang hanya muncul di salah satu sheet perlu review sebagai tambahan/pembatalan, bukan hilang diam-diam.
9. Tampilkan rekonsiliasi: jumlah blok kegiatan ditemukan, berhasil dipetakan, dikecualikan dengan alasan, belum cocok, dan gagal. Tidak ada baris/bagian terisi yang boleh dibuang tanpa laporan.
10. Commit hasil yang sudah direview secara transaksional dan idempoten. Batch/row fingerprint mencegah upload ulang membuat duplikat; impor ulang berbeda menghasilkan preview selisih, tidak menimpa jadwal resmi/realisasi tanpa persetujuan.
11. Cocokkan sampel seluruh jenis ibadah dan rekap sebelum menerbitkan. Simpan file asli dan laporan batch sesuai kebijakan retensi.

### Formula dan format lama

- Formula tidak dieksekusi sebagai kode; macros dan referensi eksternal tidak dijalankan.
- Gunakan nilai cache hanya sebagai kandidat dan beri tanda jika belum dapat diverifikasi. Referensi sederhana internal dapat diurai terbatas bila parser mendukung; formula tanpa nilai yang dapat dipercaya masuk review, bukan kosong diam-diam.
- Rekap `SUMPRODUCT` menjadi pembanding migrasi, bukan sumber jumlah tugas baru atau jaminan angka benar. Rumus workbook tidak diasumsikan mencakup semua baris dengan tepat.
- Ekspor awal bersifat satu arah untuk distribusi. Mengedit hasil ekspor tidak menyinkronkan portal otomatis; impor ulang harus melalui staging dan review konflik.

## 11. Output dan laporan

### Distribusi jadwal

- PDF per ibadah, mingguan, bulanan, atau periode; susunan tugas mudah dibaca dan header tabel berulang. Pecah per bulan/jenis ibadah, bukan satu halaman tiga bulan yang terlalu kecil.
- Excel dengan jadwal, realisasi jika diizinkan, serta rekap. Label peran dan urutan mengikuti template.
- Ringkasan teks siap salin ke WhatsApp untuk satu ibadah/minggu, tanpa asumsi tersedia API pengiriman.
- Jadwal pribadi per pelayan untuk dibagikan operator. Pada tahap akun pelayan, tampilan ini terfilter otomatis.
- Draft jelas berlabel DRAFT; resmi mencantumkan revisi dan waktu terbit. PDF/Excel/ringkasan yang dibuat dengan versi sama harus identik isinya.
- Pilihan tampilan rencana awal, jadwal berlaku, dan realisasi selalu berlabel jelas; draft tidak bocor kepada pembaca internal.

### Rekap beban pelayanan

Tampilkan dua ukuran berbeda:

1. **Jumlah ibadah dilayani:** jumlah kegiatan unik per pelayan; orang dengan dua tugas dalam ibadah sama dihitung satu kali.
2. **Jumlah penugasan per slot/jenis tugas:** jumlah tugas yang diemban; peran gabungan satu slot tetap satu penugasan gabungan, dan dapat dirinci label komponennya tanpa dijumlahkan ulang sebagai beban.

Pisahkan rencana awal, jadwal berlaku, realisasi terverifikasi, dan belum diverifikasi. Tambahkan jumlah penggantian/pembatalan untuk evaluasi proses, bukan skor otomatis kualitas pelayan. Kelompok dan placeholder organisasi memiliki rekap terpisah, bukan dianggap individu. Kegiatan batal dan tugas tidak dilaksanakan tidak masuk jumlah ibadah aktual.

## 12. Tahapan implementasi dan gerbang selesai

| Tahap | Deliverable | Gerbang sebelum lanjut |
|---|---|---|
| 0. Validasi aturan & spike | Pemetaan template, peran rangkap, periode, pemegang izin, dry run parser workbook | Semua blok sheet terpilih terjelaskan; anomali dan keputusan migrasi tercatat |
| 1. Fondasi modul | Skema/migration/factory, panel `/ibadah`, RBAC, master pelayan/template/tempat | Pengguna sah masuk; tanpa izin ditolak; navigasi Keuangan/Settings tidak berubah |
| 2. Penyusunan & penerbitan | Periode, generator kegiatan, matriks, cek kelengkapan, snapshot rencana/revisi | Satu periode dapat disusun dan diterbitkan; rencana awal tidak tertimpa revisi |
| 3. Persiapan & penggantian | Konfirmasi operator, ketidaktersediaan, kandidat pengganti, alasan, kontrol konflik | Skenario petugas berhalangan sampai revisi resmi teruji, termasuk dua operator bersamaan |
| 4. Realisasi & laporan | Pelayan aktual, penutupan/koreksi, rekap, PDF/Excel/ringkasan | Angka rencana dan aktual terpisah; semua output memakai versi yang dipilih |
| 5. Migrasi & pilot | Import staging, rekonsiliasi sumber, satu periode pilot, panduan operator, backup/rollback | Tidak ada silent drop/duplikat, operator menyetujui data, jalur rollback diuji |
| 6. Pengembangan lanjutan | Konfirmasi mandiri, reminder, saran rotasi, integrasi Warta/Pelkat | Hanya setelah alur pengelola stabil dan kanal notifikasi disepakati |

Tahap 1–5 membentuk MVP operasional; selesai panel saja belum berarti modul siap menggantikan Excel. Estimasi kalender belum ditetapkan sebelum tahap 0 mengunci aturan dan kapasitas tim.

## 13. Kriteria penerimaan / pengujian

- [ ] Operator membuat jadwal tiga bulan di muka dengan sesi pagi/sore, kegiatan keluarga, Pelkat, Doa Karyawan, dan kegiatan khusus.
- [ ] Generator menangani minggu ke-n, pengecualian, penutupan kegiatan, dan tanggal lintas tahun tanpa duplikat.
- [ ] Matriks membedakan slot gabungan, slot berulang, opsional, tidak berlaku, dan wajib kosong.
- [ ] Penggantian contoh P-2/P-3 5 Juli 2026 pukul 09.00 tidak menghapus rencana awal; riwayat dan alasan terlihat.
- [ ] Peran rangkap dalam kegiatan sama yang diizinkan tidak ditolak sebagai bentrok; kegiatan overlap lintas ibadah terdeteksi berdasarkan interval.
- [ ] Konfirmasi melekat pada penugasan tertentu; pengganti/reschedule tidak mewarisi kesediaan lama secara keliru.
- [ ] Publish pertama menyimpan baseline; draft revisi tidak mengubah keluaran resmi; revisi terbit memperbarui keluaran resmi secara atomik.
- [ ] Slot belum terisi dan organisasi menunggu nama mempunyai status yang berbeda; override publish memerlukan izin dan alasan.
- [ ] Realisasi tidak terisi otomatis hanya karena waktu ibadah lewat. Pembatalan, tidak hadir, pengganti aktual, dan koreksi arsip tercatat.
- [ ] Rekap membedakan jumlah kegiatan unik dan jumlah tugas, tanpa hitung ganda kolektan/peran gabungan.
- [ ] Impor menampilkan 7 sheet beserta 2 sheet tersembunyi; `Sheet2` dilewati dengan laporan kosong, bukan dianggap data hilang.
- [ ] Error hari 17 Agustus dan 26 Desember, duplikasi 21 Juli, variasi nama, formula tanpa cache, dan lintas tahun tampil pada preview.
- [ ] Rencana dan realisasi yang kolomnya bergeser tetap dipetakan lewat identitas kegiatan; kegiatan yang tidak berpasangan masuk review.
- [ ] Upload/commit batch sama dua kali tidak membuat kegiatan/tugas ganda; batch gagal tidak meninggalkan data sebagian.
- [ ] Nama baru/tamu/kelompok, catatan `PEMBINAAN`, dan keterangan asal pendeta tidak salah menjadi akun pengguna atau slot tugas.
- [ ] Dua operator mengedit/publish kegiatan sama memperoleh konflik versi yang jelas, bukan last-write-wins diam-diam.
- [ ] Riwayat tetap utuh sesudah master/template berubah atau pelayan dinonaktifkan.
- [ ] Pembaca tanpa izin tidak melihat draft, kontak, alasan pribadi, maupun file impor melalui URL langsung.
- [ ] Semua endpoint/action diuji RBAC, termasuk pengguna tidak aktif; role Keuangan tidak berubah.
- [ ] PDF/Excel/ringkasan sesuai snapshot dan revisi, terbaca saat dicetak, serta tampilan HP dapat dipakai.
- [ ] Jalankan feature/unit test relevan, uji impor fixture dan regresi panel lain. Jika UI berubah, lakukan lint/build dan uji alur browser; belum ada tes aplikasi yang dijalankan pada tahap dokumen ini.

## 14. Keputusan yang perlu disahkan sebelum coding

1. **Pemegang wewenang:** siapa menyusun, menerbitkan, dan boleh mengganti petugas pada hari ibadah? Rekomendasi: pisahkan izin, boleh ditugaskan ke orang sama bila tim kecil.
2. **Penamaan periode:** bulan saja atau nomor triwulan pelayanan mengikuti tahun gerejawi? Rekomendasi awal: label bulan eksplisit, jangan menebak.
3. **Template liturgi:** arti P-1 hingga P-10, gabungan, perjamuan, tambahan, dan kombinasi rangkap yang diizinkan. Jangan menebak dari gelar atau posisi baris.
4. **Konfirmasi:** kapan mulai dikumpulkan dan siapa mencatat? Rekomendasi MVP: operator mencatat; tidak wajib akun semua pelayan.
5. **Migrasi:** periode pertama dan apakah sheet “Realisasi” benar-benar aktual terverifikasi atau revisi penugasan. Rekomendasi: pilot satu periode; histori hanya diimpor setelah review.
6. **Distribusi:** siapa menerima jadwal dan apakah memuat alamat keluarga/kontak? Rekomendasi: internal berizin, kontak/alamat tidak masuk ekspor umum, WhatsApp manual terlebih dulu.

Keputusan tersebut tidak menghalangi review plan ini, tetapi harus tercatat sebelum skema/status/import mapping dibekukan. Tidak ada nama pejabat, jadwal cutover, kanal API, atau aturan gerejawi baru yang diasumsikan sudah disepakati.

## 15. Strategi peluncuran dan rollback

- Pilot memakai periode yang disetujui operator. Lakukan pencocokan portal-versus-sumber dan simulasi penggantian serta cetak sebelum cutover.
- Ambil backup database dan workbook sumber; uji pemulihan pada lingkungan non-produksi.
- Tetapkan satu sumber resmi per kegiatan saat cutover. Bila Excel masih dipakai sementara untuk pembanding, tandai read-only secara operasional; jangan jalankan dua sumber edit tanpa aturan rekonsiliasi.
- Jika pilot gagal, nonaktifkan akses modul/launcher untuk cakupan pilot dan gunakan salinan terakhir yang disetujui sambil merekonsiliasi perubahan sejak snapshot. Jangan menghapus migration/data operasional sebagai rollback pertama.
- Setelah stabil, arsipkan workbook sebagai sumber historis. Pengembangan notifikasi/rotasi dilakukan terpisah agar tidak menunda penggantian proses utama.

---

Dokumen ini disusun dari workbook terlampir dan source portal yang diperiksa. Belum mengubah aplikasi, menjalankan migration, mengimpor data ke database, atau menganggap aturan usulan sudah mendapat persetujuan gereja.
