<?php
// ayelectronic_store/live_search.php

header('Content-Type: application/json');
include_once 'config/database.php';

$term = $_GET['term'] ?? '';
$results = [];

if (strlen($term) >= 2) {
    $conn = new mysqli($host, $user, $pass, $db_name);
    if (!$conn->connect_error) {
        $search_term = "%" . $term . "%";
        
        // --- RINCIAN PERUBAHAN 1: Mengambil ID dan Nama Produk ---
        $sql = "SELECT id, nama_produk FROM products WHERE nama_produk LIKE ? LIMIT 5";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $search_term);
        $stmt->execute();
        $result = $stmt->get_result();
        
        // --- RINCIAN PERUBAHAN 2: Menyimpan data sebagai object dengan id dan name ---
        while ($row = $result->fetch_assoc()) {
            $results[] = ['id' => $row['id'], 'name' => $row['nama_produk']];
        }
        $stmt->close();
        $conn->close();
    }
}

echo json_encode($results);
exit();