<?php
include 'db_koneksi.php';

// Function untuk menyimpan data sensor mentah
function simpanDataSensor($conn, $analog_value) {
    $stmt = $conn->prepare("INSERT INTO sensor (analog_value) VALUES (?)");
    $stmt->bind_param("i", $analog_value);
    if ($stmt->execute()) {
        return $conn->insert_id;
    }
    return false;
}

// Function untuk melakukan perhitungan voltage dan menyimpannya
function simpanPerhitungan($conn, $id_sensor, $analog_value) {
    $ADC_MAX = 4095;
    $VREF = 3.3;
    $voltage = ($analog_value / $ADC_MAX) * $VREF;

    $stmt = $conn->prepare("INSERT INTO sensor_perhitungan (id_sensor, analog_value, voltage) VALUES (?, ?, ?)");
    $stmt->bind_param("iid", $id_sensor, $analog_value, $voltage);
    if ($stmt->execute()) {
        return [$conn->insert_id, $voltage];
    }
    return false;
}

// Function untuk menyimpan keputusan berdasarkan voltage
function simpanKeputusan($conn, $id_perhitungan, $voltage) {
    $keputusan = tentukanKeputusan($voltage);
    $stmt = $conn->prepare("INSERT INTO keputusan (id_perhitungan, keputusan) VALUES (?, ?)");
    $stmt->bind_param("is", $id_perhitungan, $keputusan);
    if ($stmt->execute()) {
        return [$conn->insert_id, $keputusan];
    }
    return false;
}

// Function untuk menentukan keputusan berdasarkan voltage
function tentukanKeputusan($voltage) {
    if ($voltage >= 2.0) {
        return "Bahaya";
    } elseif ($voltage >= 1.0) {
        return "Waspada";
    } else {
        return "Aman";
    }
}

// Function untuk menyimpan hasil akhir ke tabel hasil
function simpanHasil($conn, $id_perhitungan, $id_keputusan) {
    $stmt = $conn->prepare("INSERT INTO hasil (id_perhitungan, id_keputusan) VALUES (?, ?)");
    $stmt->bind_param("ii", $id_perhitungan, $id_keputusan);
    return $stmt->execute();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['analog_data'])) {
        $analog_value = intval($_POST['analog_data']);
        
        $id_sensor = simpanDataSensor($conn, $analog_value);
        if (!$id_sensor) {
            die("Error saat menyimpan data sensor!");
=======
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

        list($id_perhitungan, $voltage) = simpanPerhitungan($conn, $id_sensor, $analog_value);
        if (!$id_perhitungan) {
            die("Error saat menyimpan perhitungan!");
        }

        list($id_keputusan, $keputusan) = simpanKeputusan($conn, $id_perhitungan, $voltage);
        if (!$id_keputusan) {
            die("Error saat menyimpan keputusan!");
        }

        if (simpanHasil($conn, $id_perhitungan, $id_keputusan)) {
            echo "Data berhasil disimpan ke semua tabel dengan keputusan: $keputusan";
        } else {
            echo "Error saat menyimpan ke tabel hasil!";
        }
    } else {
        echo "Parameter analog_data tidak ditemukan!";
    }
} else {
    echo "NGAPAINNN!!!";
}

$conn->close();
?>
