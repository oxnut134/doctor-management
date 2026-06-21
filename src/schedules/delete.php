<?php
session_start();
require_once __DIR__ . '/../config/database.php';

$db = getDB();
$id = (int)($_GET['id'] ?? 0);

$stmt = $db->prepare('SELECT s.*, d.id AS did FROM schedules s JOIN doctors d ON d.id = s.doctor_id WHERE s.id = ?');
$stmt->execute([$id]);
$schedule = $stmt->fetch();

if ($schedule) {
    $db->prepare('DELETE FROM schedules WHERE id = ?')->execute([$id]);
    $_SESSION['flash_success'] = 'Schedule for ' . $schedule['day_of_week'] . ' deleted.';
    header('Location: /schedules/?doctor_id=' . $schedule['did']);
} else {
    $_SESSION['flash_error'] = 'Schedule not found.';
    header('Location: /schedules/');
}
exit;
