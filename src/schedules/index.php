<?php
session_start();
require_once __DIR__ . '/../config/database.php';

$pageTitle = 'Schedules';
$breadcrumb = ['Schedules' => null];
$db = getDB();

$filterDoctor = (int)($_GET['doctor_id'] ?? 0);

$doctors = $db->query('SELECT id, name, specialty FROM doctors ORDER BY name')->fetchAll();

if ($filterDoctor) {
    $stmt = $db->prepare("
        SELECT s.*, d.name AS doctor_name, d.specialty
        FROM schedules s
        JOIN doctors d ON d.id = s.doctor_id
        WHERE s.doctor_id = ?
        ORDER BY FIELD(s.day_of_week,'Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday')
    ");
    $stmt->execute([$filterDoctor]);
} else {
    $stmt = $db->query("
        SELECT s.*, d.name AS doctor_name, d.specialty
        FROM schedules s
        JOIN doctors d ON d.id = s.doctor_id
        ORDER BY d.name, FIELD(s.day_of_week,'Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday')
    ");
}
$schedules = $stmt->fetchAll();

$grouped = [];
foreach ($schedules as $s) {
    $grouped[$s['doctor_id']]['doctor_name']  = $s['doctor_name'];
    $grouped[$s['doctor_id']]['specialty']    = $s['specialty'];
    $grouped[$s['doctor_id']]['schedules'][]  = $s;
}

include __DIR__ . '/../includes/header.php';
?>

<div class="page-header">
  <h2>&#x1F4C5; Schedules</h2>
  <a href="/schedules/create.php" class="btn btn-primary">+ Add Schedule</a>
</div>

<div class="card" style="margin-bottom:20px">
  <div class="card-body" style="padding:12px 20px">
    <form method="GET" style="display:flex;align-items:center;gap:12px;flex-wrap:wrap">
      <label style="margin:0;font-weight:600">Filter by Doctor:</label>
      <select name="doctor_id" style="width:240px" onchange="this.form.submit()">
        <option value="">All Doctors</option>
        <?php foreach ($doctors as $d): ?>
          <option value="<?= $d['id'] ?>" <?= $filterDoctor === $d['id'] ? 'selected' : '' ?>>
            <?= htmlspecialchars($d['name']) ?> — <?= htmlspecialchars($d['specialty']) ?>
          </option>
        <?php endforeach; ?>
      </select>
      <?php if ($filterDoctor): ?><a href="/schedules/" class="btn btn-secondary btn-sm">Clear</a><?php endif; ?>
    </form>
  </div>
</div>

<?php if ($grouped): ?>
  <?php foreach ($grouped as $docId => $group): ?>
  <div class="card" style="margin-bottom:16px">
    <div class="card-header">
      <div>
        <h3><?= htmlspecialchars($group['doctor_name']) ?></h3>
        <small style="color:var(--text-muted)"><?= htmlspecialchars($group['specialty']) ?></small>
      </div>
      <div class="btn-group">
        <a href="/schedules/create.php?doctor_id=<?= $docId ?>" class="btn btn-primary btn-sm">+ Add Day</a>
        <a href="/appointments/create.php?doctor_id=<?= $docId ?>" class="btn btn-secondary btn-sm">Book Appointment</a>
      </div>
    </div>
    <div class="card-body">
      <?php foreach ($group['schedules'] as $s): ?>
        <div class="schedule-day">
          <span class="day-name"><?= $s['day_of_week'] ?></span>
          <span class="day-time">
            <?= date('g:i A', strtotime($s['start_time'])) ?> &ndash; <?= date('g:i A', strtotime($s['end_time'])) ?>
          </span>
          <span style="color:var(--text-muted);font-size:.82rem">Max: <?= $s['max_appointments'] ?> appts</span>
          <div class="btn-group">
            <a href="/schedules/edit.php?id=<?= $s['id'] ?>" class="btn btn-warning btn-sm">Edit</a>
            <a href="/schedules/delete.php?id=<?= $s['id'] ?>"
               class="btn btn-danger btn-sm"
               onclick="return confirm('Delete this schedule?')">Delete</a>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
  <?php endforeach; ?>
<?php else: ?>
  <div class="card">
    <div class="empty-state">
      <div class="icon">&#x1F4C5;</div>
      <p>No schedules found<?= $filterDoctor ? ' for this doctor' : '' ?>.</p>
      <a href="/schedules/create.php<?= $filterDoctor ? '?doctor_id='.$filterDoctor : '' ?>" class="btn btn-primary">Add Schedule</a>
    </div>
  </div>
<?php endif; ?>

<?php include __DIR__ . '/../includes/footer.php'; ?>
