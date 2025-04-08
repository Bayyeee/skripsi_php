<?php
include('db_koneksi.php');

// Query to fetch today's data based on the current date
$query = "SELECT timestamp, analog_value, voltage 
    FROM sensor 
    JOIN sensor_perhitungan ON sensor.id_sensor = sensor_perhitungan.id_sensor 
    WHERE DATE(timestamp) = CURDATE() 
    ORDER BY timestamp DESC 
    LIMIT 100";
$result = $conn->query($query);

$data = array();
if ($result) {
    while ($row = $result->fetch_assoc()) {
    $data[] = $row;
    }
}

// Reverse the data to maintain ascending order
$data = array_reverse($data);

// Return JSON response
header('Content-Type: application/json');
echo json_encode($data);
