<?php
function active(string $page): string {
    $current = basename($_SERVER['PHP_SELF'], '.php');
    $dir     = basename(dirname($_SERVER['PHP_SELF']));
    if ($page === 'dashboard' && $current === 'index' && $dir === 'html') return 'active';
    if ($page === $dir) return 'active';
    if ($page === $current) return 'active';
    return '';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= htmlspecialchars($pageTitle ?? 'Doctor Management') ?></title>
  <link rel="stylesheet" href="/css/style.css">
</head>
<body>
<div class="wrapper">
  <aside class="sidebar">
    <div class="sidebar-brand">
      <h1>&#x1F3E5; MediCare</h1>
      <span>Doctor Management</span>
    </div>
    <nav class="sidebar-nav">
      <div class="section-label">Main</div>
      <a href="/" class="<?= active('dashboard') ?>">
        <span class="icon">&#x1F4CA;</span> Dashboard
      </a>
      <div class="section-label">Management</div>
      <a href="/doctors/" class="<?= active('doctors') ?>">
        <span class="icon">&#x1F9D1;&#x200D;&#x2695;&#xFE0F;</span> Doctors
      </a>
      <a href="/schedules/" class="<?= active('schedules') ?>">
        <span class="icon">&#x1F4C5;</span> Schedules
      </a>
      <a href="/appointments/" class="<?= active('appointments') ?>">
        <span class="icon">&#x1F4CB;</span> Appointments
      </a>
    </nav>
  </aside>
  <div class="main">
    <div class="topbar">
      <div>
        <h2><?= htmlspecialchars($pageTitle ?? 'Dashboard') ?></h2>
        <div class="breadcrumb">
          <a href="/">Home</a>
          <?php if (!empty($breadcrumb)): foreach ($breadcrumb as $label => $url): ?>
            &rsaquo; <?= $url ? '<a href="' . htmlspecialchars($url) . '">' . htmlspecialchars($label) . '</a>' : htmlspecialchars($label) ?>
          <?php endforeach; endif; ?>
        </div>
      </div>
    </div>
    <div class="content">
<?php
if (!empty($_SESSION['flash_success'])) {
    echo '<div class="alert alert-success">&#x2714; ' . htmlspecialchars($_SESSION['flash_success']) . '</div>';
    unset($_SESSION['flash_success']);
}
if (!empty($_SESSION['flash_error'])) {
    echo '<div class="alert alert-danger">&#x26A0; ' . htmlspecialchars($_SESSION['flash_error']) . '</div>';
    unset($_SESSION['flash_error']);
}
?>
