<?php
session_start();
require_once __DIR__ . '/../config/database.php';

$db = getDB();
$id = (int)($_GET['id'] ?? 0);

$stmt = $db->prepare('SELECT patient_name FROM appointments WHERE id = ?');
$stmt->execute([$id]);
$appt = $stmt->fetch();

if ($appt) {
    $db->prepare('DELETE FROM appointments WHERE id = ?')->execute([$id]);
    $_SESSION['flash_success'] = 'Appointment for "' . $appt['patient_name'] . '" deleted.';
} else {
    $_SESSION['flash_error'] = 'Appointment not found.';
}

header('Location: /appointments/');
exit;
