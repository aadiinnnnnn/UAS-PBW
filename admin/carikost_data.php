<?php
header('Content-Type: application/json');
require '../koneksi.php';

$response = ['success' => false, 'message' => 'Terjadi kesalahan.', 'data' => []];

try {
    $base_query = "SELECT id_kos_plk, nama, lokasi, fasilitas, gambar_url, deskripsi, tipe, jumlah, harga_sewa, periode_sewa FROM pengelolaan_kost WHERE tersedia = 1";
    $params = [];
    $types = '';

    // Filter by Lokasi
    if (!empty($_GET['lokasi'])) {
        $base_query .= " AND (nama LIKE ? OR lokasi LIKE ?)";
        $location_param = '%' . $_GET['lokasi'] . '%';
        $params[] = $location_param;
        $params[] = $location_param;
        $types .= 'ss';
    }

    // Filter by Tipe
    if (!empty($_GET['tipe'])) {
        $base_query .= " AND tipe = ?";
        $params[] = $_GET['tipe'];
        $types .= 's';
    }

    // Filter by Harga Maks
    if (!empty($_GET['harga_maks']) && is_numeric($_GET['harga_maks'])) {
        $base_query .= " AND harga_sewa <= ?";
        $params[] = (float)$_GET['harga_maks'];
        $types .= 'd';
    }

    // Filter by Fasilitas
    if (!empty($_GET['fasilitas']) && is_array($_GET['fasilitas'])) {
        foreach ($_GET['fasilitas'] as $fasilitas) {
            $base_query .= " AND fasilitas LIKE ?";
            $params[] = '%' . $fasilitas . '%';
            $types .= 's';
        }
    }
    
    // Filter by Durasi Sewa
    if (!empty($_GET['durasi_sewa'])) {
        $base_query .= " AND periode_sewa = ?";
        $params[] = $_GET['durasi_sewa'];
        $types .= 's';
    }

    // Sorting
    $order_by = " ORDER BY nama ASC"; // Default sort
    if (!empty($_GET['urutkan'])) {
        if ($_GET['urutkan'] == 'harga_terendah') {
            $order_by = " ORDER BY harga_sewa ASC";
        } elseif ($_GET['urutkan'] == 'harga_tertinggi') {
            $order_by = " ORDER BY harga_sewa DESC";
        }
    }
    $base_query .= $order_by;

    $stmt = $conn->prepare($base_query);

    if ($stmt) {
        if (!empty($types) && !empty($params)) {
            $stmt->bind_param($types, ...$params);
        }
        
        $stmt->execute();
        $result = $stmt->get_result();
        $kost_data = $result->fetch_all(MYSQLI_ASSOC);
        
        $response['success'] = true;
        $response['message'] = 'Data berhasil diambil.';
        $response['data'] = $kost_data;
        
        $stmt->close();
    } else {
        $response['message'] = 'Gagal menyiapkan query: ' . $conn->error;
    }

} catch (Exception $e) {
    $response['message'] = 'Exception: ' . $e->getMessage();
}

echo json_encode($response);
$conn->close();
?>