<?php
include 'db_koneksi.php'; 

// Function untuk menentukan keputusan
function tentukanKeputusan($voltage) {
    if ($voltage >= 2.0) {
        return "Bahaya";
    } elseif ($voltage >= 1.0) {
        return "Waspada";
    } else {
        return "Aman";
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['analog_data'])) {
        $analog_value = intval($_POST['analog_data']);

        // Simpan data sensor
        $stmt = $conn->prepare("INSERT INTO sensor (analog_value) VALUES (?)");
        $stmt->bind_param("i", $analog_value);

        if ($stmt->execute()) {
            $id_sensor = $conn->insert_id;

            // Perhitungan voltage
            $ADC_MAX = 4095;
            $VREF = 3.3;
            $voltage = ($analog_value / $ADC_MAX) * $VREF;

            // Simpan hasil perhitungan ke sensor_perhitungan
            $stmt2 = $conn->prepare("INSERT INTO sensor_perhitungan (id_sensor, analog_value, voltage) VALUES (?, ?, ?)");
            $stmt2->bind_param("iid", $id_sensor, $analog_value, $voltage);

            if ($stmt2->execute()) {
                // Ambil ID terakhir dari sensor_perhitungan
                $id_perhitungan = $conn->insert_id;

                // Tentukan keputusan berdasarkan voltage
                $keputusan = tentukanKeputusan($voltage);

                // Simpan hasil keputusan ke tabel keputusan
                $stmt3 = $conn->prepare("INSERT INTO keputusan (id_perhitungan, keputusan) VALUES (?, ?)");
                $stmt3->bind_param("is", $id_perhitungan, $keputusan);

                if ($stmt3->execute()) {
                    echo "Data berhasil disimpan dan keputusan: $keputusan";
                } else {
                    echo "Error saat menyimpan keputusan: " . $stmt3->error;
                }

                $stmt3->close();
            } else {
                echo "Error saat menyimpan perhitungan: " . $stmt2->error;
            }

            $stmt2->close();
        } else {
            echo "Error saat menyimpan data sensor: " . $stmt->error;
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
