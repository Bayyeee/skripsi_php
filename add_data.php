<?php
include 'db_koneksi.php'; 

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['analog_data'])) {
        $analog_value = intval($_POST['analog_data']);

        // ** simpan data
        $stmt = $conn->prepare("INSERT INTO sensor (analog_value) VALUES (?)");
        $stmt->bind_param("i", $analog_value);

        if ($stmt->execute()) {
            // ** ambil ID terakhir dari tabel sensor
            $id_sensor = $conn->insert_id;

            // ** Perhitung
            $ADC_MAX = 4095;
            $VREF = 3.3;
            $voltage = ($analog_value / $ADC_MAX) * $VREF;

            // ** simpan hasil perhitungan
            $stmt2 = $conn->prepare("INSERT INTO sensor_perhitungan (id_sensor, analog_value, voltage) VALUES (?, ?, ?)");
            $stmt2->bind_param("iid", $id_sensor, $analog_value, $voltage);

            if ($stmt2->execute()) {
                echo "Data berhasil disimpan dan dihitung dengan ID Sensor: $id_sensor";
            } else {
                echo "Error saat menyimpan hasil perhitungan: " . $stmt2->error;
            }

            $stmt2->close();
        } else {
            echo "Error saat menyimpan data: " . $stmt->error;
        }

        $stmt->close();
    } else {
        echo "Parameter analog_data tidak ditemukan!";
    }
} else {
    echo "NGAPAINNN!!!";
}

$conn->close();
?>
