<?php
session_start();
require_once __DIR__ . '/../config/database.php';

$pageTitle = 'Doctors';
$breadcrumb = ['Doctors' => null];
$db = getDB();

$search = trim($_GET['search'] ?? '');
if ($search !== '') {
    $stmt = $db->prepare("
        SELECT d.*, COUNT(a.id) AS appt_count
        FROM doctors d
        LEFT JOIN appointments a ON a.doctor_id = d.id
        WHERE d.name LIKE ? OR d.specialty LIKE ? OR d.email LIKE ?
        GROUP BY d.id ORDER BY d.name
    ");
    $like = '%' . $search . '%';
    $stmt->execute([$like, $like, $like]);
} else {
    $stmt = $db->query("
        SELECT d.*, COUNT(a.id) AS appt_count
        FROM doctors d
        LEFT JOIN appointments a ON a.doctor_id = d.id
        GROUP BY d.id ORDER BY d.name
    ");
}
$doctors = $stmt->fetchAll();
$avatarColors = ['#2563eb','#16a34a','#d97706','#0891b2','#7c3aed','#db2777'];

include __DIR__ . '/../includes/header.php';
?>

<div class="page-header">
  <h2>&#x1F9D1;&#x200D;&#x2695;&#xFE0F; Doctors</h2>
  <a href="/doctors/create.php" class="btn btn-primary">+ Add Doctor</a>
</div>

<div class="card">
  <div class="card-header">
    <h3>All Doctors (<?= count($doctors) ?>)</h3>
    <form method="GET" style="display:flex;gap:8px">
      <input type="search" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Search name, specialty..." style="width:220px">
      <button class="btn btn-secondary btn-sm">Search</button>
      <?php if ($search): ?><a href="/doctors/" class="btn btn-secondary btn-sm">Clear</a><?php endif; ?>
    </form>
  </div>
  <div class="table-wrapper">
    <?php if ($doctors): ?>
    <table>
      <thead>
        <tr>
          <th>#</th>
          <th>Doctor</th>
          <th>Specialty</th>
          <th>Email</th>
          <th>Phone</th>
          <th>Appointments</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
      <?php foreach ($doctors as $i => $doc): ?>
        <tr>
          <td style="color:var(--text-muted)"><?= $i + 1 ?></td>
          <td>
            <div style="display:flex;align-items:center;gap:10px">
              <div class="avatar" style="background:<?= $avatarColors[$i % count($avatarColors)] ?>">
                <?= strtoupper(substr($doc['name'], 3, 1)) ?>
              </div>
              <div class="doctor-meta">
                <span><?= htmlspecialchars($doc['name']) ?></span>
                <?php if ($doc['bio']): ?>
                  <small><?= htmlspecialchars(mb_strimwidth($doc['bio'], 0, 50, '...')) ?></small>
                <?php endif; ?>
              </div>
            </div>
          </td>
          <td><?= htmlspecialchars($doc['specialty']) ?></td>
          <td><a href="mailto:<?= htmlspecialchars($doc['email']) ?>"><?= htmlspecialchars($doc['email']) ?></a></td>
          <td><?= htmlspecialchars($doc['phone']) ?></td>
          <td><span class="badge badge-confirmed"><?= $doc['appt_count'] ?></span></td>
          <td>
            <div class="btn-group">
              <a href="/doctors/edit.php?id=<?= $doc['id'] ?>" class="btn btn-warning btn-sm">Edit</a>
              <a href="/schedules/?doctor_id=<?= $doc['id'] ?>" class="btn btn-secondary btn-sm">Schedule</a>
              <a href="/doctors/delete.php?id=<?= $doc['id'] ?>"
                 class="btn btn-danger btn-sm"
                 onclick="return confirm('Delete <?= htmlspecialchars(addslashes($doc['name'])) ?>? This will also remove their schedules and appointments.')">Delete</a>
            </div>
          </td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
    <?php else: ?>
      <div class="empty-state">
        <div class="icon">&#x1F9D1;&#x200D;&#x2695;&#xFE0F;</div>
        <p><?= $search ? 'No doctors matched your search.' : 'No doctors added yet.' ?></p>
        <a href="/doctors/create.php" class="btn btn-primary">Add First Doctor</a>
      </div>
    <?php endif; ?>
  </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
