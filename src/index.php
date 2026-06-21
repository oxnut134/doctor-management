<?php
session_start();
require_once __DIR__ . '/config/database.php';

$pageTitle = 'Dashboard';
$db = getDB();

$totalDoctors       = $db->query('SELECT COUNT(*) FROM doctors')->fetchColumn();
$totalSchedules     = $db->query('SELECT COUNT(*) FROM schedules')->fetchColumn();
$totalAppointments  = $db->query('SELECT COUNT(*) FROM appointments')->fetchColumn();
$todayAppointments  = $db->query("SELECT COUNT(*) FROM appointments WHERE appointment_date = CURDATE()")->fetchColumn();

$recentAppointments = $db->query("
    SELECT a.*, d.name AS doctor_name, d.specialty
    FROM appointments a
    JOIN doctors d ON d.id = a.doctor_id
    ORDER BY a.appointment_date DESC, a.appointment_time ASC
    LIMIT 6
")->fetchAll();

$doctorsList = $db->query("
    SELECT d.*, COUNT(a.id) AS appt_count
    FROM doctors d
    LEFT JOIN appointments a ON a.doctor_id = d.id
    GROUP BY d.id
    ORDER BY d.name
    LIMIT 5
")->fetchAll();

$avatarColors = ['#2563eb','#16a34a','#d97706','#0891b2','#7c3aed','#db2777'];

include __DIR__ . '/includes/header.php';
?>

<div class="stats-grid">
  <div class="stat-card">
    <div class="stat-icon blue">&#x1F9D1;&#x200D;&#x2695;&#xFE0F;</div>
    <div>
      <div class="stat-value"><?= $totalDoctors ?></div>
      <div class="stat-label">Total Doctors</div>
    </div>
  </div>
  <div class="stat-card">
    <div class="stat-icon green">&#x1F4C5;</div>
    <div>
      <div class="stat-value"><?= $totalSchedules ?></div>
      <div class="stat-label">Active Schedules</div>
    </div>
  </div>
  <div class="stat-card">
    <div class="stat-icon yellow">&#x1F4CB;</div>
    <div>
      <div class="stat-value"><?= $totalAppointments ?></div>
      <div class="stat-label">Total Appointments</div>
    </div>
  </div>
  <div class="stat-card">
    <div class="stat-icon cyan">&#x23F0;</div>
    <div>
      <div class="stat-value"><?= $todayAppointments ?></div>
      <div class="stat-label">Today's Appointments</div>
    </div>
  </div>
</div>

<div style="display:grid;grid-template-columns:1fr 320px;gap:20px;align-items:start">

  <div class="card">
    <div class="card-header">
      <h3>&#x1F4CB; Recent Appointments</h3>
      <a href="/appointments/" class="btn btn-primary btn-sm">View All</a>
    </div>
    <div class="table-wrapper">
      <?php if ($recentAppointments): ?>
      <table>
        <thead>
          <tr>
            <th>Patient</th>
            <th>Doctor</th>
            <th>Date</th>
            <th>Time</th>
            <th>Status</th>
          </tr>
        </thead>
        <tbody>
        <?php foreach ($recentAppointments as $appt): ?>
          <tr>
            <td><?= htmlspecialchars($appt['patient_name']) ?></td>
            <td>
              <div class="doctor-meta">
                <span><?= htmlspecialchars($appt['doctor_name']) ?></span>
                <small><?= htmlspecialchars($appt['specialty']) ?></small>
              </div>
            </td>
            <td><?= date('M j, Y', strtotime($appt['appointment_date'])) ?></td>
            <td><?= date('g:i A', strtotime($appt['appointment_time'])) ?></td>
            <td><span class="badge badge-<?= $appt['status'] ?>"><?= $appt['status'] ?></span></td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
      <?php else: ?>
        <div class="empty-state"><div class="icon">&#x1F4CB;</div><p>No appointments yet.</p></div>
      <?php endif; ?>
    </div>
  </div>

  <div class="card">
    <div class="card-header">
      <h3>&#x1F9D1;&#x200D;&#x2695;&#xFE0F; Doctors</h3>
      <a href="/doctors/" class="btn btn-primary btn-sm">Manage</a>
    </div>
    <div class="card-body" style="padding:0">
      <?php foreach ($doctorsList as $i => $doc): ?>
        <div style="display:flex;align-items:center;gap:12px;padding:12px 16px;border-bottom:1px solid var(--border)">
          <div class="avatar" style="background:<?= $avatarColors[$i % count($avatarColors)] ?>">
            <?= strtoupper(substr($doc['name'], 3, 1)) ?>
          </div>
          <div class="doctor-meta" style="flex:1">
            <span><?= htmlspecialchars($doc['name']) ?></span>
            <small><?= htmlspecialchars($doc['specialty']) ?></small>
          </div>
          <small style="color:var(--text-muted)"><?= $doc['appt_count'] ?> appts</small>
        </div>
      <?php endforeach; ?>
    </div>
  </div>

</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
