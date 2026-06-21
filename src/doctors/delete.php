<?php
session_start();
require_once __DIR__ . '/../config/database.php';

$db = getDB();
$id = (int)($_GET['id'] ?? 0);

$stmt = $db->prepare('SELECT name FROM doctors WHERE id = ?');
$stmt->execute([$id]);
$doctor = $stmt->fetch();

if ($doctor) {
    $db->prepare('DELETE FROM doctors WHERE id = ?')->execute([$id]);
    $_SESSION['flash_success'] = 'Doctor "' . $doctor['name'] . '" has been deleted.';
} else {
    $_SESSION['flash_error'] = 'Doctor not found.';
}

header('Location: /doctors/');
exit;
