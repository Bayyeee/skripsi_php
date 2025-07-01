<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

include('db_koneksi.php');

header('Content-Type: application/json');

$response = [
    "status" => false,
    "message" => "",
    "data" => [],
    "total_data" => 0,
    "limit" => 10,
    "offset" => 0
];

// Ambil dan validasi parameter limit, offset, dan date
$limit = isset($_GET['limit']) ? max(1, intval($_GET['limit'])) : 10;
$offset = isset($_GET['offset']) ? max(0, intval($_GET['offset'])) : 0;
$date = isset($_GET['date']) ? $_GET['date'] : ''; // Parameter tanggal

$response['limit'] = $limit;
$response['offset'] = $offset;

// Ambil total data keseluruhan dengan filter tanggal jika ada
$whereClause = "";
if (!empty($date)) {
    // Validasi format tanggal (YYYY-MM-DD)
    if (DateTime::createFromFormat('Y-m-d', $date) !== false) {
        $whereClause = " WHERE DATE(s.timestamp) = '$date' ";
    } else {
        $response['message'] = "Format tanggal tidak valid.";
        echo json_encode($response, JSON_PRETTY_PRINT);
        exit;
    }
}

$totalResult = $conn->query("SELECT COUNT(*) AS total FROM hasil h
    JOIN sensor_perhitungan p ON h.id_perhitungan = p.id_perhitungan
    JOIN sensor s ON p.id_sensor = s.id_sensor
    JOIN keputusan k ON h.id_keputusan = k.id_keputusan
    $whereClause");

if ($totalResult && $totalRow = $totalResult->fetch_assoc()) {
    $response['total_data'] = intval($totalRow['total']);
} else {
    echo json_encode([
        "status" => false,
        "message" => "Gagal menghitung total data.",
        "data" => [],
        "total_data" => 0
    ], JSON_PRETTY_PRINT);
    exit;
}

// Query utama dengan filter tanggal (jika ada) dan pagination
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
    $whereClause
    ORDER BY h.id_hasil DESC
    LIMIT $limit OFFSET $offset
";

$result = $conn->query($query);

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $utc_time = new DateTime($row['timestamp'], new DateTimeZone('UTC'));
        $utc_time->setTimezone(new DateTimeZone('Asia/Makassar'));

        $data = [
            "id_hasil" => $row['id_hasil'],
            "analog_value" => $row['analog_value'], 
            "timestamp" => $utc_time->format('Y-m-d H:i:s'),
            "hasil_perhitungan" => $row['hasil_perhitungan'], 
            "keputusan" => $row['keputusan'], 
            "relay1_status" => ($row['keputusan'] === "Bahaya") ? "ON" : "OFF"
        ];
        $response['data'][] = $data;
    }

    $response['status'] = true;
    $response['message'] = "Data berhasil diambil.";
} else {
    $response['message'] = "Query error: " . $conn->error;
}

echo json_encode($response, JSON_PRETTY_PRINT);

$conn->close();
?>
