<?php
session_start();
require_once __DIR__ . '/../config/database.php';

$pageTitle = 'Add Doctor';
$breadcrumb = ['Doctors' => '/doctors/', 'Add Doctor' => null];
$errors = [];
$values = ['name' => '', 'specialty' => '', 'email' => '', 'phone' => '', 'bio' => ''];

$specialties = [
    'Cardiology','Dermatology','Emergency Medicine','Endocrinology',
    'Gastroenterology','General Practice','Geriatrics','Hematology',
    'Infectious Disease','Internal Medicine','Nephrology','Neurology',
    'Obstetrics & Gynecology','Oncology','Ophthalmology','Orthopedics',
    'Otolaryngology','Pediatrics','Psychiatry','Pulmonology',
    'Radiology','Rheumatology','Surgery','Urology',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $values['name']      = trim($_POST['name'] ?? '');
    $values['specialty'] = trim($_POST['specialty'] ?? '');
    $values['email']     = trim($_POST['email'] ?? '');
    $values['phone']     = trim($_POST['phone'] ?? '');
    $values['bio']       = trim($_POST['bio'] ?? '');

    if ($values['name'] === '')      $errors['name']      = 'Name is required.';
    if ($values['specialty'] === '') $errors['specialty'] = 'Specialty is required.';
    if (!filter_var($values['email'], FILTER_VALIDATE_EMAIL)) $errors['email'] = 'Valid email is required.';
    if ($values['phone'] === '')     $errors['phone']     = 'Phone is required.';

    if (empty($errors)) {
        $db = getDB();
        $check = $db->prepare('SELECT id FROM doctors WHERE email = ?');
        $check->execute([$values['email']]);
        if ($check->fetch()) {
            $errors['email'] = 'This email is already registered.';
        } else {
            $stmt = $db->prepare('INSERT INTO doctors (name, specialty, email, phone, bio) VALUES (?,?,?,?,?)');
            $stmt->execute([$values['name'], $values['specialty'], $values['email'], $values['phone'], $values['bio']]);
            $_SESSION['flash_success'] = 'Doctor ' . $values['name'] . ' added successfully.';
            header('Location: /doctors/');
            exit;
        }
    }
}

include __DIR__ . '/../includes/header.php';
?>

<div class="page-header">
  <h2>Add New Doctor</h2>
  <a href="/doctors/" class="btn btn-secondary">&#x2190; Back</a>
</div>

<div class="card" style="max-width:700px">
  <div class="card-header"><h3>Doctor Information</h3></div>
  <div class="card-body">
    <form method="POST" novalidate>
      <div class="form-grid">
        <div class="form-group">
          <label for="name">Full Name *</label>
          <input id="name" name="name" type="text" value="<?= htmlspecialchars($values['name']) ?>"
                 placeholder="Dr. John Smith" required>
          <?php if (!empty($errors['name'])): ?><small style="color:var(--danger)"><?= $errors['name'] ?></small><?php endif; ?>
        </div>
        <div class="form-group">
          <label for="specialty">Specialty *</label>
          <select id="specialty" name="specialty" required>
            <option value="">Select specialty...</option>
            <?php foreach ($specialties as $sp): ?>
              <option value="<?= $sp ?>" <?= $values['specialty'] === $sp ? 'selected' : '' ?>><?= $sp ?></option>
            <?php endforeach; ?>
          </select>
          <?php if (!empty($errors['specialty'])): ?><small style="color:var(--danger)"><?= $errors['specialty'] ?></small><?php endif; ?>
        </div>
        <div class="form-group">
          <label for="email">Email Address *</label>
          <input id="email" name="email" type="email" value="<?= htmlspecialchars($values['email']) ?>"
                 placeholder="doctor@clinic.com" required>
          <?php if (!empty($errors['email'])): ?><small style="color:var(--danger)"><?= $errors['email'] ?></small><?php endif; ?>
        </div>
        <div class="form-group">
          <label for="phone">Phone Number *</label>
          <input id="phone" name="phone" type="tel" value="<?= htmlspecialchars($values['phone']) ?>"
                 placeholder="+1-555-0000" required>
          <?php if (!empty($errors['phone'])): ?><small style="color:var(--danger)"><?= $errors['phone'] ?></small><?php endif; ?>
        </div>
        <div class="form-group full">
          <label for="bio">Bio / Notes</label>
          <textarea id="bio" name="bio" placeholder="Brief description about the doctor..."><?= htmlspecialchars($values['bio']) ?></textarea>
        </div>
      </div>
      <div class="form-actions" style="margin-top:16px">
        <button type="submit" class="btn btn-primary">Save Doctor</button>
        <a href="/doctors/" class="btn btn-secondary">Cancel</a>
      </div>
    </form>
  </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
