<html>

<head>
    <title>Log Data Sensor</title>
    <meta http-equiv="refresh" content="60">
    <style type="text/css">
        .table_titles,
        .table_cells_odd,
        .table_cells_even {
            padding-right: 20px;
            padding-left: 20px;
            color: #000;
        }

        .table_titles {
            color: #FFF;
            background-color: #666;
        }

        .table_cells_odd {
            background-color: #CCC;
        }

        .table_cells_even {
            background-color: #FAFAFA;
        }

        table {
            border: 2px solid #333;
        }

        body {
            font-family: "Trebuchet MS", Arial;
        }
    </style>
</head>

<body>
    <h1>Log Data</h1>

    <!-- <button><a href="add_data.php"></a>Pindah halaman</button> -->

    <!-- <table border="0" cellspacing="0" cellpadding="4">
        <tr>
            <td class="table_titles">ID</td>
            <td class="table_titles">Analog_Value</td>
            <td class="table_titles">Voltage</td>
            <td class="table_titles">Waktu Input</td>
        </tr>
        <?php
        // Start MySQL connection
        include('db_koneksi.php');

        // Retrieve all records and display them
        $result = $conn->query("SELECT * FROM sensor ORDER BY id_sensor DESC LIMIT 10");

        // Used for row color toggle
        $oddrow = true;

        // process every record
        while ($row = mysqli_fetch_array($result)) {
            if ($oddrow) {
                $css_class = ' class="table_cells_odd"';
            } else {
                $css_class = ' class="table_cells_even"';
            }

            $oddrow = !$oddrow;

            echo '<tr>';
            echo '   <td' . $css_class . '>' . $row["id_sensor"] . '</td>';
            echo '   <td' . $css_class . '>' . $row["analog_value"] . '</td>';
            // echo '   <td' . $css_class . '>' . $row["jarak_api"] . ' cm' . '</td>';
            echo '   <td' . $css_class . '>' . $row["timestamp"] . '</td>';
            echo '</tr>';
        }
        ?>
    </table> -->

    <?php
    // Start MySQL connection
    include('db_koneksi.php');

    // Tentukan jumlah data per halaman
    $limit = 10;

    // Ambil halaman saat ini dari parameter URL, default ke halaman 1
    $page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
    $offset = ($page - 1) * $limit;

    // Hitung total data
    $total_result = $conn->query("SELECT COUNT(*) AS total FROM sensor");
    $total_row = $total_result->fetch_assoc();
    $total_data = $total_row['total'];

    // Hitung total halaman
    $total_pages = ceil($total_data / $limit);

    // Ambil data dari sensor dan sensor_perhitungan dengan limit dan offset
    $result = $conn->query("
        SELECT s.id_sensor, s.analog_value, sp.voltage, s.timestamp
        FROM sensor s
        JOIN sensor_perhitungan sp ON s.id_sensor = sp.id_sensor
        ORDER BY s.id_sensor DESC 
        LIMIT $limit OFFSET $offset
    ");
    ?>

    <table border="0" cellspacing="0" cellpadding="4">
        <tr>
            <td class="table_titles">ID</td>
            <td class="table_titles">Analog Value</td>
            <td class="table_titles">Voltage</td>
            <td class="table_titles">Waktu Input</td>
        </tr>
        <?php
        // Toggle warna baris
        $oddrow = true;

        while ($row = mysqli_fetch_array($result)) {
            $css_class = $oddrow ? ' class="table_cells_odd"' : ' class="table_cells_even"';
            $oddrow = !$oddrow;

            echo '<tr>';
            echo '   <td' . $css_class . '>' . $row["id_sensor"] . '</td>';
            echo '   <td' . $css_class . '>' . $row["analog_value"] . '</td>';
            echo '   <td' . $css_class . '>' . $row["voltage"] . ' V</td>';
            echo '   <td' . $css_class . '>' . $row["timestamp"] . '</td>';
            echo '</tr>';
        }
        ?>
    </table>

    <div style="margin-top: 10px;">
        <?php
        // Logika untuk menampilkan pagination dengan angka terbatas
        $range = 3; // Jumlah angka sebelum dan sesudah halaman saat ini
        $start = max(1, $page - $range);
        $end = min($total_pages, $page + $range);

        if ($start > 1) {
            echo '<a href="?page=1" style="text-decoration: none; margin: 0 5px;">
                    <button type="button" style="cursor: pointer; padding: 5px 10px;">1</button>
                </a>';
            if ($start > 2) {
                echo '<span style="margin: 0 5px;">...</span>';
            }
        }

        for ($i = $start; $i <= $end; $i++) {
            echo '<a href="?page=' . $i . '" style="text-decoration: none; margin: 0 5px;">
                    <button type="button" style="cursor: pointer; padding: 5px 10px; ' . ($i == $page ? 'background-color: #007bff; color: white;' : '') . '">' . $i . '</button>
                </a>';
        }

        if ($end < $total_pages) {
            if ($end < $total_pages - 1) {
                echo '<span style="margin: 0 5px;">...</span>';
            }
            echo '<a href="?page=' . $total_pages . '" style="text-decoration: none; margin: 0 5px;">
                    <button type="button" style="cursor: pointer; padding: 5px 10px;">' . $total_pages . '</button>
                </a>';
        }
        ?>
    </div>

    <div style="margin-top: 10px;">
        <a href="controller_api.php" style="text-decoration: none;">
            <button type="button"
                style="cursor: pointer; background-color: #007bff; color: #fff; border: none; padding: 10px 20px; border-radius: 5px;">api
                controller alat</button>
        </a>
        <a href="get_api.php">
            <button type="submit" name="relay_status" value="OFF"
                style="background-color: red; color: white; padding: 10px; border-radius: 5px;">get data api</button>
        </a>
    </div>

    <?php
    include('db_koneksi.php'); // Pastikan koneksi ke database
    
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $relay_status = $_POST["relay_status"]; // Menerima data dari tombol
        $update_query = "UPDATE control_relay SET relay2_status = '$relay_status' WHERE id = 1";
        if ($conn->query($update_query) === TRUE) {
            echo "<script>alert('Relay berhasil diperbarui!');</script>";
        } else {
            echo "Error: " . $conn->error;
        }
    }
    ?>

    <div style="margin-top: 20px;">
        <form method="POST">
            <button type="submit" name="relay_status" value="ON"
                style="background-color: green; color: white; padding: 10px; border-radius: 5px;">Nyalakan Relay
                2</button>
            <button type="submit" name="relay_status" value="OFF"
                style="background-color: red; color: white; padding: 10px; border-radius: 5px;">Matikan Relay 2</button>
        </form>
    </div>

    <div style="margin-top: 20px; background-color: black; padding: 20px; border-radius: 10px;">
        <h2 style="color: white;">Chart Data Sensor</h2>
        <canvas id="sensorChart" width="400" height="200"></canvas>
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <script>
            document.addEventListener("DOMContentLoaded", function () {
                const ctx = document.getElementById('sensorChart').getContext('2d');

                // Fetch data from the server
                fetch('get_chart_data.php?filter=1day') // Ensure the server-side script filters data for 1 day
                    .then(response => response.json())
                    .then(data => {
                        if (data.length === 0) {
                            console.error('No data available for the selected filter.');
                            return;
                        }

                        const labels = data.map(item => item.timestamp);
                        const analogValues = data.map(item => item.analog_value);

                        // Create the chart
                        new Chart(ctx, {
                            type: 'line',
                            data: {
                                labels: labels,
                                datasets: [
                                    {
                                        label: 'Analog Value',
                                        data: analogValues,
                                        backgroundColor: 'rgba(12, 180, 20, 0.13)', // Black box background
                                        borderColor: 'rgba(0, 255, 0, 1)', // Bright green line
                                        borderWidth: 2,
                                        fill: true, // Fill the area under the line
                                        tension: 0.4, // Smooth the line
                                        pointRadius: 0 // Remove points from the line
                                    }
                                ]
                            },
                            options: {
                                responsive: true,
                                plugins: {
                                    legend: {
                                        labels: {
                                            color: 'white' // Set legend text color to white
                                        },
                                        position: 'top',
                                    },
                                    tooltip: {
                                        enabled: true, // Enable tooltips to show data on hover
                                        mode: 'index', // Show tooltip for the nearest point
                                        intersect: false, // Allow tooltip to appear even if not directly over a point
                                        callbacks: {
                                            label: function (context) {
                                                return `Analog Value: ${context.raw}`;
                                            }
                                        }
                                    }
                                },
                                hover: {
                                    mode: 'nearest', // Highlight the nearest point
                                    intersect: false
                                },
                                scales: {
                                    x: {
                                        title: {
                                            display: true,
                                            text: 'Timestamp',
                                            color: 'white' // Set x-axis title color to white
                                        },
                                        ticks: {
                                            color: 'white' // Set x-axis tick color to white
                                        }
                                    },
                                    y: {
                                        title: {
                                            display: true,
                                            text: 'Values',
                                            color: 'white' // Set y-axis title color to white
                                        },
                                        ticks: {
                                            color: 'white' // Set y-axis tick color to white
                                        },
                                        max: 4095 // Set the maximum value for the y-axis
                                    }
                                }
                            }
                        });
                    })
                    .catch(error => console.error('Error fetching chart data:', error));
            });
        </script>
    </div>
</body>

</html>