<?php
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function isAdmin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

function isMember() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'member';
}

function redirect($url) {
    header("Location: " . $url);
    exit();
}

function generateKodeMember() {
    $db = new Database();
    $conn = $db->getConnection();
    
    $query = "SELECT MAX(id) as max_id FROM member";
    $stmt = $conn->prepare($query);
    $stmt->execute();
    
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    $next_id = $row['max_id'] + 1;
    
    return "MBR" . str_pad($next_id, 3, '0', STR_PAD_LEFT);
}

function formatDate($date) {
    return date('d M Y', strtotime($date));
}

function formatRupiah($angka) {
    if (!is_numeric($angka)) {
        return 'Rp 0';
    }
    return 'Rp ' . number_format($angka, 0, ',', '.');
}
?>