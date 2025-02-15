<?php
$servername = "localhost";
$username = "jack";
$password = "sayangbunda0098";
$dbname = "skripsi";

// TODO coba koneksi
$conn = new mysqli($servername, $username, $password, $dbname);

// TODO cek koneksi
if ($conn->connect_error) {
die("Connection failed: " . $conn->connect_error);
}