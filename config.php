<?php
    $servername = "localhost";
    $username = "root"; // Ganti dengan username MySQL Anda
    $password = ""; // Ganti dengan password MySQL Anda
    $dbname = "db_logistik_punya_ervina_septiana"; // Nama database Anda

    // Buat koneksi
    $conn = new mysqli($servername, $username, $password, $dbname);

    // Cek koneksi
    if ($conn->connect_error) {
        die("Koneksi gagal: " . $conn->connect_error);
    }

    //cek koneksi
    // if (mysqli_connect_errno()) {
    //     echo "Gagal koneksi ke database";
    // } else {
    //     echo "Berhasil koneksi";
    // }

    //url induk
    $main_url = "http://localhost/logistik/";
?>