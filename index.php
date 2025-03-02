<html>

<head>
    <title>Log Data Sensor</title>
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

    <table border="0" cellspacing="0" cellpadding="4">
        <tr>
            <td class="table_titles">ID</td>
            <td class="table_titles">Analog_Value</td>
            <!-- <td class="table_titles">Value Jarak Api</td> -->
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
    </table>

    <div style="margin-top: 10px;">
        <a href="controller_api.php" style="text-decoration: none;">
            <button type="button"
                style="cursor: pointer; background-color: #007bff; color: #fff; border: none; padding: 10px 20px; border-radius: 5px;">api controller alat</button>
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

</body>

</html>