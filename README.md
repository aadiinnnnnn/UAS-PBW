-----

# MOVER - Solusi Pindahan dan Pencarian Kost

**MOVER** adalah aplikasi web berbasis PHP yang dirancang sebagai platform terintegrasi untuk memenuhi kebutuhan anak kost. Aplikasi ini menyediakan tiga layanan utama: Jasa Pindahan Barang, Jasa Kebersihan Profesional, dan platform untuk Mencari dan Memesan Kost.

Aplikasi ini memiliki sistem otentikasi dengan dua peran pengguna yang berbeda: **Pengguna Biasa** (yang memesan layanan dan mencari kost) dan **Pemilik Kost** (yang dapat mengelola dan mendaftarkan properti mereka).

## 🖼️ Tampilan Aplikasi

Disarankan untuk menambahkan beberapa tangkapan layar di sini untuk menampilkan fitur-fitur utama aplikasi. Beberapa halaman yang bagus untuk ditampilkan adalah:

  * Halaman Utama (Landing Page)
  * Halaman Pemilihan Layanan
  * Formulir Pemesanan Jasa Pindahan (dengan kalkulasi biaya dinamis)
  * Halaman Pencarian Kost (dengan filter)
  * Dasbor Pemilik Kost

## ✨ Fitur Utama

### Untuk Pengguna (Pencari Jasa & Kost)

  - **Registrasi & Login**: Sistem akun yang aman untuk pengguna.
  - **Tiga Layanan Utama**:
    1.  **Jasa Pindahan**: Memesan layanan pindahan barang dengan kalkulasi biaya dinamis berdasarkan jarak dan jumlah barang.
    2.  **Jasa Kebersihan**: Memilih dari berbagai paket layanan kebersihan dengan diskon yang menarik.
    3.  **Cari Kost**: Mencari properti kost dengan fitur filter berdasarkan lokasi, tipe, harga, dan fasilitas.
  - **Pemesanan Mudah**: Formulir pemesanan yang intuitif untuk setiap layanan.
  - **Halaman Sukses**: Halaman konfirmasi setelah berhasil melakukan pemesanan.
  - **Ulasan & Rating**: Memberikan ulasan dan peringkat untuk layanan yang telah digunakan.

### Untuk Pemilik Kost

  - **Peran Pemilik**: Registrasi khusus sebagai pemilik properti.
  - **Dasbor Pemilik**: Halaman dasbor khusus yang menampilkan ringkasan properti, jumlah kamar tersedia, dan statistik lainnya.
  - **Manajemen Properti**: Fitur CRUD (Create, Read, Update, Delete) untuk mengelola daftar kost.
  - **Unggah Foto**: Mengunggah beberapa foto untuk setiap properti (foto utama, foto kamar, foto kamar mandi).

## 🛠️ Teknologi yang Digunakan

  * **Backend**: PHP
  * **Database**: MySQL
  * **Frontend**: HTML, CSS, JavaScript
  * **Framework/Library**:
      * [Bootstrap 5](https://getbootstrap.com/) - Untuk desain antarmuka yang responsif.
      * [jQuery](https://jquery.com/) - Untuk manipulasi DOM dan AJAX.
      * [Font Awesome](https://fontawesome.com/) - Untuk ikon.
  * **Server**: Direkomendasikan menggunakan XAMPP (Apache, MySQL, PHP).

## 🚀 Memulai Proyek

Untuk menjalankan proyek ini di lingkungan lokal Anda, ikuti langkah-langkah berikut.

### Prasyarat

Pastikan Anda telah menginstal XAMPP atau tumpukan server lokal lainnya yang mencakup Apache, MySQL, dan PHP.

### Instalasi

1.  **Clone Repositori**
    Clone repositori ini ke dalam direktori `htdocs` di dalam folder instalasi XAMPP Anda.

    ```sh
    git clone https://github.com/aadiinnnnnn/uas-pbw.git
    ```

2.  **Nyalakan Server**
    Buka XAMPP Control Panel dan nyalakan modul **Apache** dan **MySQL**.

3.  **Setup Database**

      * Buka browser dan navigasi ke `http://localhost/phpmyadmin`.
      * Buat database baru dengan nama `mover`.
      * Pilih database `mover`, lalu buka tab **Import**.
      * Impor file `mover.sql` (Anda perlu membuat file ini dari database Anda) atau jalankan query SQL berikut di tab **SQL** untuk membuat tabel-tabel yang diperlukan.

    ```sql
    -- Tabel untuk pengguna dan pemilik
    CREATE TABLE `user` (
      `id_user` varchar(10) NOT NULL PRIMARY KEY,
      `nama_user` varchar(255) NOT NULL,
      `email` varchar(255) NOT NULL UNIQUE,
      `no_hp` varchar(15) NOT NULL,
      `username` varchar(50) NOT NULL UNIQUE,
      `password` varchar(255) NOT NULL,
      `role` enum('user','owner') NOT NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

    -- Tabel untuk pengelolaan kost oleh pemilik
    CREATE TABLE `pengelolaan_kost` (
      `id_kos_plk` varchar(15) NOT NULL PRIMARY KEY,
      `id_user` varchar(10) NOT NULL,
      `nama` varchar(255) NOT NULL,
      `lokasi` text NOT NULL,
      `no_telp` varchar(15) DEFAULT NULL,
      `nama_kontak_person` varchar(100) DEFAULT NULL,
      `fasilitas` text DEFAULT NULL,
      `gambar_url` varchar(255) DEFAULT NULL,
      `gambar_dalam_kamar_url` varchar(255) DEFAULT NULL,
      `gambar_kamar_mandi_url` varchar(255) DEFAULT NULL,
      `deskripsi` text DEFAULT NULL,
      `tipe` enum('Putra','Putri','Campur') NOT NULL,
      `jumlah` int(11) NOT NULL,
      `harga_sewa` float NOT NULL,
      `periode_sewa` varchar(20) DEFAULT 'Bulan',
      `tersedia` tinyint(1) DEFAULT 1,
      FOREIGN KEY (`id_user`) REFERENCES `user`(`id_user`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

    -- Tabel untuk order layanan kebersihan
    CREATE TABLE `order_layanan_bersih_kos` (
      `id_order_bk` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
      `id_bk` varchar(10) NOT NULL,
      `id_user` varchar(10) NOT NULL,
      `jenis_paket_bk` varchar(255) NOT NULL,
      `tanggal_order_bk` date NOT NULL,
      `tanggal_datang_bk` date NOT NULL,
      `total_harga_bk` float NOT NULL,
      `metode_pembayaran_bk` varchar(50) NOT NULL,
      FOREIGN KEY (`id_user`) REFERENCES `user`(`id_user`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

    -- Tabel untuk order layanan pindahan
    CREATE TABLE `order_layanan_pindahan_barang_kos` (
      `id_order_pk` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
      `id_user` varchar(10) NOT NULL,
      `alamat_jemput` text NOT NULL,
      `alamat_tujuan` text NOT NULL,
      `jarak_km` float NOT NULL,
      `tanggal_datang_pk` date NOT NULL,
      `jenis_barang` text NOT NULL,
      `total_harga_pk` float NOT NULL,
      `metode_pembayaran_pk` varchar(50) NOT NULL,
      `catatan_tambahan` text DEFAULT NULL,
      `status_pesanan` varchar(50) DEFAULT 'Menunggu Pembayaran',
       FOREIGN KEY (`id_user`) REFERENCES `user`(`id_user`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

    -- Tabel untuk order sewa kost
    CREATE TABLE `order_sewa_kost` (
      `id_order_sewa` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
      `id_kos_plk` varchar(15) NOT NULL,
      `id_user` varchar(10) NOT NULL,
      `nama_pemesan` varchar(255) NOT NULL,
      `email_pemesan` varchar(255) NOT NULL,
      `telepon_pemesan` varchar(15) NOT NULL,
      `tanggal_check_in` date NOT NULL,
      `durasi_sewa_pilihan` varchar(100) NOT NULL,
      `total_harga` float NOT NULL,
      `metode_pembayaran` varchar(50) NOT NULL,
      `catatan_pemesan` text DEFAULT NULL,
      `status_pemesanan` varchar(50) DEFAULT 'Menunggu Konfirmasi',
      FOREIGN KEY (`id_kos_plk`) REFERENCES `pengelolaan_kost`(`id_kos_plk`),
      FOREIGN KEY (`id_user`) REFERENCES `user`(`id_user`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

    -- Tabel untuk ulasan
    CREATE TABLE `ulasan_layanan` (
      `id_ulasan` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
      `id_user` varchar(10) NOT NULL,
      `id_order` varchar(20) NOT NULL,
      `jenis_layanan` enum('kost','pindahan','bersih') NOT NULL,
      `rating` int(1) NOT NULL,
      `komentar` text DEFAULT NULL,
      `tanggal_ulasan` timestamp NOT NULL DEFAULT current_timestamp(),
      FOREIGN KEY (`id_user`) REFERENCES `user`(`id_user`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ```

    \</details\>

4.  **Konfigurasi Koneksi**
    Pastikan file `koneksi.php` memiliki kredensial yang benar untuk database Anda. Pengaturan default adalah:

    ```php
    $host = 'localhost';
    $user = 'root';
    $pass = '';
    $dbname = 'mover';
    ```

5.  **Akses Aplikasi**
    Buka browser Anda dan navigasi ke `http://localhost/UAS-PBW-b02997d22e9649026e40cc797a1c19eb9df83793/index/index.php`.

## 📂 Struktur Proyek

```
/
├── css/                  # Semua file CSS
├── image/                # Gambar statis seperti logo
├── index/                # File inti PHP (halaman dan logika)
├── javascript/           # Semua file JavaScript kustom
├── uploads/              # Direktori untuk file yang diunggah pengguna (foto kost)
│   └── kost_images/
├── koneksi.php           # File konfigurasi koneksi database
└── README.md             # Anda sedang membacanya
```

## 🧑‍💻 Cara Menggunakan

1.  Buka halaman registrasi untuk membuat akun baru. Anda dapat memilih peran sebagai **Pengguna** atau **Pemilik Kost**.
2.  Login menggunakan akun yang telah dibuat.
3.  Jika Anda login sebagai **Pengguna**, Anda akan diarahkan ke dasbor pengguna di mana Anda dapat mulai memilih layanan.
4.  Jika Anda login sebagai **Pemilik Kost**, Anda akan diarahkan ke dasbor pemilik untuk mengelola properti Anda.

-----
Dibuat oleh Kelompok 4 Sistem Informasi Unsika angkatan 23
- Muhammad Nugrah Adinda
- Hana Nabila
- Dandi Permana
- Charina Olivia Tarigan
- Fakhri Yudistra
- Nayaka Alfikri Januar
