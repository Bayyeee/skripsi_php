<?php

// TODO DEBUG ERROR DISPLAY DI JSON NYA LANGSUNG BIAR KADA PUSING
error_reporting(E_ALL);
ini_set('display_errors', 1);

include('db_koneksi.php');

header('Content-Type: application/json');

$response = array();

// TODO NAME ARRAY
$response['data'] = array();

// TODO ambil data sensor, perhitungan, dan keputusan
$query = "
    SELECT 
        s.analog_value, 
        s.timestamp, 
        p.analog_value AS hasil_perhitungan, 
        k.keputusan
    FROM hasil h
    JOIN sensor_perhitungan p ON h.id_perhitungan = p.id_perhitungan
    JOIN sensor s ON p.id_sensor = s.id_sensor
    JOIN keputusan k ON h.id_keputusan = k.id_keputusan
    ORDER BY h.id_hasil DESC
";

$result = $conn->query($query);

// TODO DEBUGING ERROR QUERY
if (!$result) {
    die(json_encode(["error" => "Query error: " . $conn->error]));
}

// TODO Ambil semua data
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $data = array(
            "analog_value" => $row['analog_value'], 
            "timestamp" => $row['timestamp'], 
            "hasil_perhitungan" => $row['hasil_perhitungan'], 
            "keputusan" => $row['keputusan'], 
            "relay1_status" => ($row['keputusan'] === "Bahaya") ? "ON" : "OFF" 
        );
        array_push(array: $response['data'], values: $data);
    }
} else {
    $response['error'] = "Data tidak ditemukan";
}

echo json_encode(value: $response, flags: JSON_PRETTY_PRINT);

$conn->close();
?>
