<?php
session_start();
require_once __DIR__ . '/../config/database.php';

$pageTitle = 'Add Schedule';
$breadcrumb = ['Schedules' => '/schedules/', 'Add Schedule' => null];
$db = getDB();

$doctors = $db->query('SELECT id, name, specialty FROM doctors ORDER BY name')->fetchAll();
if (!$doctors) {
    $_SESSION['flash_error'] = 'Please add a doctor first.';
    header('Location: /doctors/create.php');
    exit;
}

$days = ['Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday'];
$errors = [];
$values = [
    'doctor_id'        => (int)($_GET['doctor_id'] ?? 0),
    'day_of_week'      => '',
    'start_time'       => '09:00',
    'end_time'         => '17:00',
    'max_appointments' => 10,
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $values['doctor_id']        = (int)($_POST['doctor_id'] ?? 0);
    $values['day_of_week']      = $_POST['day_of_week'] ?? '';
    $values['start_time']       = $_POST['start_time'] ?? '';
    $values['end_time']         = $_POST['end_time'] ?? '';
    $values['max_appointments'] = (int)($_POST['max_appointments'] ?? 10);

    if (!$values['doctor_id'])                    $errors['doctor_id']   = 'Select a doctor.';
    if (!in_array($values['day_of_week'], $days)) $errors['day_of_week'] = 'Select a valid day.';
    if (!$values['start_time'])                   $errors['start_time']  = 'Start time is required.';
    if (!$values['end_time'])                     $errors['end_time']    = 'End time is required.';
    if ($values['start_time'] >= $values['end_time']) $errors['end_time'] = 'End time must be after start time.';
    if ($values['max_appointments'] < 1)          $errors['max_appointments'] = 'Must be at least 1.';

    if (empty($errors)) {
        $check = $db->prepare('SELECT id FROM schedules WHERE doctor_id = ? AND day_of_week = ?');
        $check->execute([$values['doctor_id'], $values['day_of_week']]);
        if ($check->fetch()) {
            $errors['day_of_week'] = 'This doctor already has a schedule for ' . $values['day_of_week'] . '.';
        } else {
            $stmt = $db->prepare('INSERT INTO schedules (doctor_id, day_of_week, start_time, end_time, max_appointments) VALUES (?,?,?,?,?)');
            $stmt->execute([$values['doctor_id'], $values['day_of_week'], $values['start_time'], $values['end_time'], $values['max_appointments']]);
            $_SESSION['flash_success'] = 'Schedule added for ' . $values['day_of_week'] . '.';
            header('Location: /schedules/?doctor_id=' . $values['doctor_id']);
            exit;
        }
    }
}

include __DIR__ . '/../includes/header.php';
?>

<div class="page-header">
  <h2>Add Schedule</h2>
  <a href="/schedules/" class="btn btn-secondary">&#x2190; Back</a>
</div>

<div class="card" style="max-width:600px">
  <div class="card-header"><h3>Schedule Details</h3></div>
  <div class="card-body">
    <form method="POST" novalidate>
      <div class="form-grid">
        <div class="form-group full">
          <label for="doctor_id">Doctor *</label>
          <select id="doctor_id" name="doctor_id" required>
            <option value="">Select doctor...</option>
            <?php foreach ($doctors as $d): ?>
              <option value="<?= $d['id'] ?>" <?= $values['doctor_id'] === $d['id'] ? 'selected' : '' ?>>
                <?= htmlspecialchars($d['name']) ?> — <?= htmlspecialchars($d['specialty']) ?>
              </option>
            <?php endforeach; ?>
          </select>
          <?php if (!empty($errors['doctor_id'])): ?><small style="color:var(--danger)"><?= $errors['doctor_id'] ?></small><?php endif; ?>
        </div>
        <div class="form-group full">
          <label for="day_of_week">Day of Week *</label>
          <select id="day_of_week" name="day_of_week" required>
            <option value="">Select day...</option>
            <?php foreach ($days as $d): ?>
              <option value="<?= $d ?>" <?= $values['day_of_week'] === $d ? 'selected' : '' ?>><?= $d ?></option>
            <?php endforeach; ?>
          </select>
          <?php if (!empty($errors['day_of_week'])): ?><small style="color:var(--danger)"><?= $errors['day_of_week'] ?></small><?php endif; ?>
        </div>
        <div class="form-group">
          <label for="start_time">Start Time *</label>
          <input id="start_time" name="start_time" type="time" value="<?= htmlspecialchars($values['start_time']) ?>" required>
          <?php if (!empty($errors['start_time'])): ?><small style="color:var(--danger)"><?= $errors['start_time'] ?></small><?php endif; ?>
        </div>
        <div class="form-group">
          <label for="end_time">End Time *</label>
          <input id="end_time" name="end_time" type="time" value="<?= htmlspecialchars($values['end_time']) ?>" required>
          <?php if (!empty($errors['end_time'])): ?><small style="color:var(--danger)"><?= $errors['end_time'] ?></small><?php endif; ?>
        </div>
        <div class="form-group">
          <label for="max_appointments">Max Appointments</label>
          <input id="max_appointments" name="max_appointments" type="number" min="1" max="100"
                 value="<?= $values['max_appointments'] ?>">
          <?php if (!empty($errors['max_appointments'])): ?><small style="color:var(--danger)"><?= $errors['max_appointments'] ?></small><?php endif; ?>
        </div>
      </div>
      <div class="form-actions" style="margin-top:16px">
        <button type="submit" class="btn btn-primary">Save Schedule</button>
        <a href="/schedules/" class="btn btn-secondary">Cancel</a>
      </div>
    </form>
  </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
