<?php
$koneksi = mysqli_connect("localhost", "root", "", "web_pbl");

if (!$koneksi) {
    die("Koneksi gagal: " . mysqli_connect_error());
}
?>