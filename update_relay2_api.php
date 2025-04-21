<?php
include('db_koneksi.php');

$response = array();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $relay_status = $_POST['relay_status'] ?? null;

    if ($relay_status !== null && ($relay_status === 'ON' || $relay_status === 'OFF')) {
        $update_query = "UPDATE control_relay SET relay2_status = '$relay_status' WHERE id = 1";
        
        if ($conn->query($update_query) === TRUE) {
            $response['success'] = true;
            $response['message'] = "Relay 2 berhasil diupdate menjadi $relay_status.";
        } else {
            $response['success'] = false;
            $response['message'] = "Gagal update relay: " . $conn->error;
        }
    } else {
        $response['success'] = false;
        $response['message'] = "Data relay_status tidak valid.";
    }
} else {
    $response['success'] = false;
    $response['message'] = "Metode harus POST.";
}

header('Content-Type: application/json');
echo json_encode($response);

$conn->close();
?>
