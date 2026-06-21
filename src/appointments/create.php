<?php
session_start();
require_once __DIR__ . '/../config/database.php';

$pageTitle = 'Book Appointment';
$breadcrumb = ['Appointments' => '/appointments/', 'Book' => null];
$db = getDB();

$doctors = $db->query('SELECT id, name, specialty FROM doctors ORDER BY name')->fetchAll();
if (!$doctors) {
    $_SESSION['flash_error'] = 'Please add a doctor first.';
    header('Location: /doctors/create.php');
    exit;
}

$statuses = ['pending','confirmed','cancelled','completed'];
$errors = [];
$values = [
    'doctor_id'        => (int)($_GET['doctor_id'] ?? 0),
    'patient_name'     => '',
    'patient_email'    => '',
    'patient_phone'    => '',
    'appointment_date' => date('Y-m-d'),
    'appointment_time' => '09:00',
    'reason'           => '',
    'notes'            => '',
    'status'           => 'pending',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $values['doctor_id']        = (int)($_POST['doctor_id'] ?? 0);
    $values['patient_name']     = trim($_POST['patient_name'] ?? '');
    $values['patient_email']    = trim($_POST['patient_email'] ?? '');
    $values['patient_phone']    = trim($_POST['patient_phone'] ?? '');
    $values['appointment_date'] = $_POST['appointment_date'] ?? '';
    $values['appointment_time'] = $_POST['appointment_time'] ?? '';
    $values['reason']           = trim($_POST['reason'] ?? '');
    $values['notes']            = trim($_POST['notes'] ?? '');
    $values['status']           = $_POST['status'] ?? 'pending';

    if (!$values['doctor_id'])                              $errors['doctor_id']        = 'Select a doctor.';
    if ($values['patient_name'] === '')                     $errors['patient_name']     = 'Patient name is required.';
    if (!filter_var($values['patient_email'], FILTER_VALIDATE_EMAIL)) $errors['patient_email'] = 'Valid email is required.';
    if ($values['patient_phone'] === '')                    $errors['patient_phone']    = 'Phone is required.';
    if (!$values['appointment_date'])                       $errors['appointment_date'] = 'Date is required.';
    if (!$values['appointment_time'])                       $errors['appointment_time'] = 'Time is required.';
    if (!in_array($values['status'], $statuses))            $errors['status']           = 'Invalid status.';

    if (empty($errors)) {
        $stmt = $db->prepare('
            INSERT INTO appointments (doctor_id, patient_name, patient_email, patient_phone, appointment_date, appointment_time, reason, notes, status)
            VALUES (?,?,?,?,?,?,?,?,?)
        ');
        $stmt->execute([
            $values['doctor_id'], $values['patient_name'], $values['patient_email'],
            $values['patient_phone'], $values['appointment_date'], $values['appointment_time'],
            $values['reason'], $values['notes'], $values['status'],
        ]);
        $_SESSION['flash_success'] = 'Appointment booked for ' . $values['patient_name'] . '.';
        header('Location: /appointments/');
        exit;
    }
}

include __DIR__ . '/../includes/header.php';
?>

<div class="page-header">
  <h2>Book Appointment</h2>
  <a href="/appointments/" class="btn btn-secondary">&#x2190; Back</a>
</div>

<div class="card" style="max-width:750px">
  <div class="card-header"><h3>Appointment Details</h3></div>
  <div class="card-body">
    <form method="POST" novalidate>

      <div style="margin-bottom:16px;padding-bottom:16px;border-bottom:1px solid var(--border)">
        <div style="font-size:.8rem;font-weight:700;text-transform:uppercase;color:var(--text-muted);margin-bottom:12px">Doctor &amp; Scheduling</div>
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
          <div class="form-group">
            <label for="appointment_date">Date *</label>
            <input id="appointment_date" name="appointment_date" type="date"
                   value="<?= htmlspecialchars($values['appointment_date']) ?>" required>
            <?php if (!empty($errors['appointment_date'])): ?><small style="color:var(--danger)"><?= $errors['appointment_date'] ?></small><?php endif; ?>
          </div>
          <div class="form-group">
            <label for="appointment_time">Time *</label>
            <input id="appointment_time" name="appointment_time" type="time"
                   value="<?= htmlspecialchars($values['appointment_time']) ?>" required>
            <?php if (!empty($errors['appointment_time'])): ?><small style="color:var(--danger)"><?= $errors['appointment_time'] ?></small><?php endif; ?>
          </div>
        </div>
      </div>

      <div style="margin-bottom:16px;padding-bottom:16px;border-bottom:1px solid var(--border)">
        <div style="font-size:.8rem;font-weight:700;text-transform:uppercase;color:var(--text-muted);margin-bottom:12px">Patient Information</div>
        <div class="form-grid">
          <div class="form-group">
            <label for="patient_name">Patient Name *</label>
            <input id="patient_name" name="patient_name" type="text"
                   value="<?= htmlspecialchars($values['patient_name']) ?>" placeholder="Full name" required>
            <?php if (!empty($errors['patient_name'])): ?><small style="color:var(--danger)"><?= $errors['patient_name'] ?></small><?php endif; ?>
          </div>
          <div class="form-group">
            <label for="patient_phone">Patient Phone *</label>
            <input id="patient_phone" name="patient_phone" type="tel"
                   value="<?= htmlspecialchars($values['patient_phone']) ?>" placeholder="+1-555-0000" required>
            <?php if (!empty($errors['patient_phone'])): ?><small style="color:var(--danger)"><?= $errors['patient_phone'] ?></small><?php endif; ?>
          </div>
          <div class="form-group full">
            <label for="patient_email">Patient Email *</label>
            <input id="patient_email" name="patient_email" type="email"
                   value="<?= htmlspecialchars($values['patient_email']) ?>" placeholder="patient@email.com" required>
            <?php if (!empty($errors['patient_email'])): ?><small style="color:var(--danger)"><?= $errors['patient_email'] ?></small><?php endif; ?>
          </div>
        </div>
      </div>

      <div class="form-grid">
        <div class="form-group">
          <label for="reason">Reason for Visit</label>
          <input id="reason" name="reason" type="text"
                 value="<?= htmlspecialchars($values['reason']) ?>" placeholder="Brief reason...">
        </div>
        <div class="form-group">
          <label for="status">Status</label>
          <select id="status" name="status">
            <?php foreach ($statuses as $s): ?>
              <option value="<?= $s ?>" <?= $values['status'] === $s ? 'selected' : '' ?>><?= ucfirst($s) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="form-group full">
          <label for="notes">Additional Notes</label>
          <textarea id="notes" name="notes" placeholder="Any additional notes..."><?= htmlspecialchars($values['notes']) ?></textarea>
        </div>
      </div>

      <div class="form-actions" style="margin-top:16px">
        <button type="submit" class="btn btn-primary">Book Appointment</button>
        <a href="/appointments/" class="btn btn-secondary">Cancel</a>
      </div>
    </form>
  </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
