<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

include('db_koneksi.php');

header('Content-Type: application/json');

$response = array();
$response['data'] = array();

$limit = isset($_GET['limit']) ? intval($_GET['limit']) : 10;  
$offset = isset($_GET['offset']) ? intval($_GET['offset']) : 0; 

$query = "
    SELECT 
        h.id_hasil,
        s.analog_value, 
        s.timestamp, 
        p.voltage AS hasil_perhitungan, 
        k.keputusan
    FROM hasil h
    JOIN sensor_perhitungan p ON h.id_perhitungan = p.id_perhitungan
    JOIN sensor s ON p.id_sensor = s.id_sensor
    JOIN keputusan k ON h.id_keputusan = k.id_keputusan
    ORDER BY h.id_hasil DESC
    LIMIT $limit OFFSET $offset
";

$result = $conn->query($query);

if (!$result) {
    die(json_encode(["error" => "Query error: " . $conn->error]));
}

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $data = array(
            "id_hasil" => $row['id_hasil'],
            "analog_value" => $row['analog_value'], 
            "timestamp" => $row['timestamp'], 
            "hasil_perhitungan" => $row['hasil_perhitungan'], 
            "keputusan" => $row['keputusan'], 
            "relay1_status" => ($row['keputusan'] === "Bahaya") ? "ON" : "OFF"
        );
        array_push($response['data'], $data);
    }
} else {
    $response['error'] = "Data tidak ditemukan";
}

echo json_encode($response, JSON_PRETTY_PRINT);

$conn->close();
?>
