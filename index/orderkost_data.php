<?php
header('Content-Type: application/json');
require 'session.php'; // Memastikan pengguna sudah login
require '../koneksi.php'; // Koneksi ke database

$response = ['success' => false, 'message' => 'Terjadi kesalahan.'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $inputJSON = file_get_contents('php://input');
    $input = json_decode($inputJSON, TRUE); // Konversi JSON ke array asosiatif

    // Ambil data dari input
    $id_kos_plk = $input['idKost'] ?? null;
    $id_user = $_SESSION['user_id'] ?? null; // Ambil dari session
    $nama_pemesan = $input['fullName'] ?? '';
    $email_pemesan = $input['email'] ?? '';
    $telepon_pemesan = $input['phoneNumber'] ?? '';
    $tanggal_check_in = $input['checkInDate'] ?? '';
    
    // Untuk durasi, kita simpan teks deskriptifnya
    $duration_value = $input['duration'] ?? ''; // ini value dari select (1, 3, 6, 12)
    $durasi_sewa_pilihan_text = '';
    if ($duration_value) {
        // Dapatkan teks dari opsi yang dipilih (perlu disesuaikan jika JS tidak mengirim teksnya)
        // Contoh sederhana:
        switch ($duration_value) {
            case "1": $durasi_sewa_pilihan_text = "1 Bulan"; break;
            case "3": $durasi_sewa_pilihan_text = "3 Bulan (Diskon)"; break;
            case "6": $durasi_sewa_pilihan_text = "6 Bulan (Diskon)"; break;
            case "12": $durasi_sewa_pilihan_text = "1 Tahun (Diskon Besar)"; break;
            default: $durasi_sewa_pilihan_text = $duration_value . " Bulan (Lainnya)";
        }
    }
    
    $total_harga = $input['totalPrice'] ?? 0;
    $metode_pembayaran = $input['paymentMethod'] ?? '';
    $catatan_pemesan = $input['notes'] ?? null;
    $status_pemesanan = 'Menunggu Konfirmasi'; // Status awal

    // Validasi Sederhana (Tambahkan validasi lebih lanjut sesuai kebutuhan)
    if (empty($id_kos_plk) || empty($id_user) || empty($nama_pemesan) || empty($email_pemesan) || empty($telepon_pemesan) || empty($tanggal_check_in) || empty($durasi_sewa_pilihan_text) || empty($metode_pembayaran) || $total_harga <= 0) {
        $response['message'] = 'Data tidak lengkap atau tidak valid.';
        echo json_encode($response);
        exit;
    }

    // Simpan ke database
    $stmt = $conn->prepare("INSERT INTO order_sewa_kost (id_kos_plk, id_user, nama_pemesan, email_pemesan, telepon_pemesan, tanggal_check_in, durasi_sewa_pilihan, total_harga, metode_pembayaran, catatan_pemesan, status_pemesanan) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    
    if ($stmt) {
        $stmt->bind_param("sssssssssss", 
            $id_kos_plk, 
            $id_user, 
            $nama_pemesan, 
            $email_pemesan, 
            $telepon_pemesan, 
            $tanggal_check_in, 
            $durasi_sewa_pilihan_text, 
            $total_harga, 
            $metode_pembayaran, 
            $catatan_pemesan,
            $status_pemesanan
        );

        if ($stmt->execute()) {
            $new_order_id = $stmt->insert_id; // Dapatkan ID pesanan baru
            $response['success'] = true;
            $response['message'] = 'Pemesanan kost berhasil! ID Pesanan: ' . $new_order_id;
            $response['orderId'] = $new_order_id;
            
            // Siapkan data untuk halaman sukses (disimpan di session)
            $_SESSION['latestKostOrderDetails'] = [
                'orderId' => $new_order_id,
                'kostName' => $input['kostName'] ?? 'N/A',
                'kostAddress' => $input['kostAddress'] ?? 'N/A',
                'totalPrice' => $total_harga,
                'paymentMethod' => $metode_pembayaran,
                'checkInDate' => $tanggal_check_in,
                'durationText' => $durasi_sewa_pilihan_text,
                'notes' => $catatan_pemesan
            ];

        } else {
            $response['message'] = 'Gagal menyimpan pesanan: ' . $stmt->error;
        }
        $stmt->close();
    } else {
        $response['message'] = 'Gagal menyiapkan statement database: ' . $conn->error;
    }
} else {
    $response['message'] = 'Metode request tidak valid.';
}

echo json_encode($response);
$conn->close();
?>