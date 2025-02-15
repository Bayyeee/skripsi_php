<?php
include('db_koneksi.php');

$response = array();

// Ambil status relay 2 dari control_relay
$queryRelay2 = "SELECT relay2_status FROM control_relay WHERE id = 1";
$resultRelay2 = $conn->query($queryRelay2);

if ($resultRelay2->num_rows > 0) {
    $rowRelay2 = $resultRelay2->fetch_assoc();
    $response['relay2_status'] = $rowRelay2['relay2_status'];
} else {
    $response['relay2_status'] = "unknown"; // Jika tidak ada data
}

// Ambil keputusan terbaru untuk relay 1 dari tabel hasil & keputusan
$queryRelay1 = "
    SELECT k.keputusan 
    FROM hasil h
    JOIN keputusan k ON h.id_keputusan = k.id_keputusan
    ORDER BY h.id_hasil DESC 
    LIMIT 1
";

$resultRelay1 = $conn->query($queryRelay1);

if ($resultRelay1->num_rows > 0) {
    $rowRelay1 = $resultRelay1->fetch_assoc();
    $response['keputusan'] = $rowRelay1['keputusan'];

    // Menentukan tindakan berdasarkan keputusan
    $response['relay1_status'] = ($rowRelay1['keputusan'] === "Bahaya") ? "ON" : "OFF";
} else {
    $response['keputusan'] = "unknown";
    $response['relay1_status'] = "unknown";
}

// Output dalam format JSON
header('Content-Type: application/json');
echo json_encode($response);

$conn->close();
?>
