<?php
require_once 'config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    $id = $_POST['id'];

    $stmt = $pdo->prepare("DELETE FROM transaksi WHERE id = ?");
    if ($stmt->execute([$id])) {
        header("Location: index.php");
        exit;
    } else {
        echo "Gagal menghapus data.";
    }
} else {
    echo "Permintaan tidak valid.";
}
