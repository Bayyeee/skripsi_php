<?php
include 'db_koneksi.php';

$result = $conn->query("SELECT * FROM sensor WHERE id_sensor NOT IN (SELECT id_perhitungan FROM sensor_perhitungan)");

if ($result->num_rows > 0) {
    $ADC_MAX = 4095;
    $VREF = 3.3;
    // $AMBANG_BATAS = 1600;

    while ($row = $result->fetch_assoc()) {
        $id = $row['id_perhitungan'];
        $analog_value = $row['analog_value'];
        $voltage = ($analog_value / $ADC_MAX) * $VREF;
        // $status_api = ($analog_value >= $AMBANG_BATAS) ? "True" : "False";

        $stmt = $conn->prepare("INSERT INTO sensor_perhitungan (id_perhitungan, analog_value, voltage) VALUES (?, ?, ?)");
        $stmt->bind_param("iids", $id, $analog_value, $voltage);

        if ($stmt->execute()) {
            echo "Data ID: $id - Analog: $analog_value, Volt: " . number_format($voltage, 2) . "V, berhasil disimpan.<br>";
        } else {
            echo "Error: " . $stmt->error;
        }

        $stmt->close();
    }
} else {
    echo "Tidak ada data baru untuk dihitung.";
}

$conn->close();
?>
