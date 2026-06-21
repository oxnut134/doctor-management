<?php
session_start();
require_once __DIR__ . '/../config/database.php';

$pageTitle = 'Appointments';
$breadcrumb = ['Appointments' => null];
$db = getDB();

$filterDoctor = (int)($_GET['doctor_id'] ?? 0);
$filterStatus = $_GET['status'] ?? '';
$filterDate   = $_GET['date'] ?? '';

$doctors  = $db->query('SELECT id, name FROM doctors ORDER BY name')->fetchAll();
$statuses = ['pending','confirmed','cancelled','completed'];

$where  = ['1=1'];
$params = [];

if ($filterDoctor) { $where[] = 'a.doctor_id = ?'; $params[] = $filterDoctor; }
if ($filterStatus) { $where[] = 'a.status = ?';    $params[] = $filterStatus; }
if ($filterDate)   { $where[] = 'a.appointment_date = ?'; $params[] = $filterDate; }

$sql = "
    SELECT a.*, d.name AS doctor_name, d.specialty
    FROM appointments a
    JOIN doctors d ON d.id = a.doctor_id
    WHERE " . implode(' AND ', $where) . "
    ORDER BY a.appointment_date DESC, a.appointment_time ASC
";
$stmt = $db->prepare($sql);
$stmt->execute($params);
$appointments = $stmt->fetchAll();

include __DIR__ . '/../includes/header.php';
?>

<div class="page-header">
  <h2>&#x1F4CB; Appointments</h2>
  <a href="/appointments/create.php" class="btn btn-primary">+ Book Appointment</a>
</div>

<!-- Filters -->
<div class="card" style="margin-bottom:20px">
  <div class="card-body" style="padding:12px 20px">
    <form method="GET" style="display:flex;gap:10px;flex-wrap:wrap;align-items:flex-end">
      <div class="form-group" style="margin:0">
        <label>Doctor</label>
        <select name="doctor_id" style="width:200px">
          <option value="">All Doctors</option>
          <?php foreach ($doctors as $d): ?>
            <option value="<?= $d['id'] ?>" <?= $filterDoctor === $d['id'] ? 'selected' : '' ?>>
              <?= htmlspecialchars($d['name']) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="form-group" style="margin:0">
        <label>Status</label>
        <select name="status" style="width:150px">
          <option value="">All Statuses</option>
          <?php foreach ($statuses as $s): ?>
            <option value="<?= $s ?>" <?= $filterStatus === $s ? 'selected' : '' ?>><?= ucfirst($s) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="form-group" style="margin:0">
        <label>Date</label>
        <input type="date" name="date" value="<?= htmlspecialchars($filterDate) ?>" style="width:160px">
      </div>
      <button class="btn btn-primary btn-sm">Filter</button>
      <?php if ($filterDoctor || $filterStatus || $filterDate): ?>
        <a href="/appointments/" class="btn btn-secondary btn-sm">Clear</a>
      <?php endif; ?>
    </form>
  </div>
</div>

<div class="card">
  <div class="card-header">
    <h3>Appointments (<?= count($appointments) ?>)</h3>
  </div>
  <div class="table-wrapper">
    <?php if ($appointments): ?>
    <table>
      <thead>
        <tr>
          <th>#</th>
          <th>Patient</th>
          <th>Doctor</th>
          <th>Date</th>
          <th>Time</th>
          <th>Reason</th>
          <th>Status</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
      <?php foreach ($appointments as $i => $appt): ?>
        <tr>
          <td style="color:var(--text-muted)"><?= $i + 1 ?></td>
          <td>
            <div class="doctor-meta">
              <span><?= htmlspecialchars($appt['patient_name']) ?></span>
              <small><?= htmlspecialchars($appt['patient_phone']) ?></small>
            </div>
          </td>
          <td>
            <div class="doctor-meta">
              <span><?= htmlspecialchars($appt['doctor_name']) ?></span>
              <small><?= htmlspecialchars($appt['specialty']) ?></small>
            </div>
          </td>
          <td><?= date('M j, Y', strtotime($appt['appointment_date'])) ?></td>
          <td><?= date('g:i A', strtotime($appt['appointment_time'])) ?></td>
          <td style="max-width:180px">
            <span title="<?= htmlspecialchars($appt['reason'] ?? '') ?>">
              <?= htmlspecialchars(mb_strimwidth($appt['reason'] ?? '—', 0, 40, '...')) ?>
            </span>
          </td>
          <td><span class="badge badge-<?= $appt['status'] ?>"><?= $appt['status'] ?></span></td>
          <td>
            <div class="btn-group">
              <a href="/appointments/edit.php?id=<?= $appt['id'] ?>" class="btn btn-warning btn-sm">Edit</a>
              <a href="/appointments/delete.php?id=<?= $appt['id'] ?>"
                 class="btn btn-danger btn-sm"
                 onclick="return confirm('Delete this appointment?')">Delete</a>
            </div>
          </td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
    <?php else: ?>
      <div class="empty-state">
        <div class="icon">&#x1F4CB;</div>
        <p>No appointments found.</p>
        <a href="/appointments/create.php" class="btn btn-primary">Book Appointment</a>
      </div>
    <?php endif; ?>
  </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
