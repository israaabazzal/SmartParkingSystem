<?php
require_once 'api/db.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $plate = strtoupper(trim($_POST['plate'] ?? ''));
    if ($plate === '') {
        $error = 'Please enter a plate number.';
    } else {
        $stmt = $pdo->prepare("SELECT vehicle_id FROM vehicles WHERE plate_number = ?");
        $stmt->execute([$plate]);
        if ($stmt->rowCount() === 0) {
            $error = 'Plate not found. Please check your number and try again.';
        } else {
            header("Location: user.php?plate=" . urlencode($plate));
            exit;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Smart Parking System</title>
  <link rel="stylesheet" href="css/style.css">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=DM+Sans:wght@300;400;500&family=DM+Mono:wght@400;500&display=swap" rel="stylesheet">
</head>
<body>

<!-- Background layers -->
  <div class="bg-grid"></div>
  <div class="bg-glow bg-glow--1"></div>
  <div class="bg-glow bg-glow--2"></div>

  <!-- Top bar -->
  <header class="topbar">
    <div class="topbar__logo">
      <span class="topbar__logo-icon">P</span>
      <span class="topbar__logo-text">SMART PARKING</span>
    </div>
    <a href="admin_login.php" class="topbar__admin-link">Admin Panel</a>
  </header>

  <!-- Main layout -->
  <main class="main">

    <!-- Left: hero + form -->
    <section class="hero">
      <div class="hero__eyebrow">Find your spot instantly</div>

      <h1 class="hero__title">
        <span class="hero__title-line">FIND</span>
        <span class="hero__title-line hero__title-line--accent">YOUR</span>
        <span class="hero__title-line">SLOT</span>
      </h1>

      <p class="hero__sub">Enter your plate number below to locate your parking slot, view your session details, and handle payment — all in one place.</p>

      <!-- Search form -->
      <form class="search-form" method="POST" action="" id="searchForm" novalidate>

        <?php if ($error): ?>
          <div class="search-form__error" id="errorMsg">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
            <?= htmlspecialchars($error) ?>
          </div>
        <?php else: ?>
          <div class="search-form__error" id="errorMsg" style="display:none;"></div>
        <?php endif; ?>

        <div class="search-form__field <?= $error ? 'search-form__field--error' : '' ?>" id="fieldWrap">
          <span class="plate-flag">🇱🇧</span>
            <div class="plate-divider"></div>
          <input
            type="text"
            name="plate"
            id="plateInput"
            class="search-form__input"
            placeholder="ENTER PLATE NUMBER"
            maxlength="20"
            autocomplete="off"
            value="<?= htmlspecialchars($_POST['plate'] ?? '') ?>"
            spellcheck="false"
          >
          <span class="plate-hint">LB</span>
        </div>
        
        <button type="submit" class="btn-find" id="findBtn">
          <span class="btn-find__text">FIND MY PARKING SLOT</span>
          <span class="btn-find__arrow">→</span>
          <span class="btn-find__loader" style="display:none;">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" class="spin"><path d="M12 2v4M12 18v4M4.93 4.93l2.83 2.83M16.24 16.24l2.83 2.83M2 12h4M18 12h4M4.93 19.07l2.83-2.83M16.24 7.76l2.83-2.83"/></svg>
          </span>
        </button>

      </form>

      
      </div>
    </section>

    <!-- Right: level availability -->
    <aside class="levels" id="levelsPanel">
      <div class="levels__header">
        <span class="levels__label">Live Availability</span>
        <span class="levels__dot" id="liveDot"></span>
      </div>

      <div class="levels__list" id="levelsList">
        <!-- Injected by JS via get_stats.php -->
        <div class="levels__updated">Level 1 — Disabled</div>
        <div class="level-card level-card--skeleton"></div>
        <div class="levels__updated">Level 2 — Small & Med</div>
        <div class="level-card level-card--skeleton"></div>
        <div class="levels__updated">Level 3 — Large</div>
        <div class="level-card level-card--skeleton"></div>
      </div>

      <div class="levels__footer">
        <div class="levels__updated">Updating every 10s</div>
        <div class="levels__updated">Rate: $0.05 / min </div>
        <div class="levels__updated"> Violation: ×2 on breach</div>
      </div>
    </aside>

  </main>

<script src="js/index.js"></script>

</body>
</html>
