<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header('Location: admin_login.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Dashboard — Smart Parking</title>
  <link rel="stylesheet" href="css/style.css">
  <link rel="stylesheet" href="css/admin.css">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=DM+Sans:wght@300;400;500&family=DM+Mono:wght@400;500&display=swap" rel="stylesheet">
</head>
<body>

  <div class="bg-grid"></div>
  <div class="bg-glow bg-glow--1"></div>
  <div class="bg-glow bg-glow--2"></div>

  <!-- Topbar -->
  <header class="topbar">
    <div class="topbar__logo">
      <span class="topbar__logo-icon">P</span>
      <span class="topbar__logo-text">SMART PARKING — ADMIN</span>
    </div>
    <div class="topbar__right">
      <span class="topbar__admin-name"><?= htmlspecialchars($_SESSION['admin_user']) ?></span>
      <a href="api/logout.php" class="topbar__logout">Logout</a>
    </div>
  </header>

  <main class="admin-main">

    <!-- ══════════════════════════════════════════
         1. STATS BAR
    ══════════════════════════════════════════ -->
    <section class="stats-bar" id="statsBar">
      <div class="stat-card" id="stat-total">
        <div class="stat-card__val" id="statTotal">—</div>
        <div class="stat-card__label">Total Slots</div>
      </div>
      <div class="stat-card" id="stat-available">
        <div class="stat-card__val" id="statAvailable">—</div>
        <div class="stat-card__label">Available</div>
      </div>
      <div class="stat-card stat-card--occupied" id="stat-occupied">
        <div class="stat-card__val" id="statOccupied">—</div>
        <div class="stat-card__label">Occupied</div>
      </div>
    </section>

    <!-- ══════════════════════════════════════════
         2. VEHICLE ENTRY
    ══════════════════════════════════════════ -->
    <section class="admin-section">
      <div class="section-title">
        <span class="section-title__icon">🚗</span>
        Vehicle Entry
      </div>
      <div class="entry-panel">

        <!-- Plate search -->
        <div class="entry-search">
          <div class="field-group__wrap" id="entryFieldWrap">
            <span class="plate-flag">🇱🇧</span>
            <div class="plate-divider"></div>
            <input type="text" id="entryPlate" class="field-group__input plate-input" placeholder="ENTER PLATE NUMBER" maxlength="20" autocomplete="off" spellcheck="false">
            <button class="btn-check" id="btnCheck" type="button">CHECK</button>
          </div>
        </div>

        <!-- Result area -->
        <div id="entryResult" class="entry-result" style="display:none;"></div>

        <!-- Add vehicle form (shown when vehicle not found) -->
        <div id="addVehicleForm" class="add-vehicle-form" style="display:none;">
          <div class="add-vehicle-form__title">New Vehicle — Assign Slot</div>
          <div class="add-form-grid">
            <div class="field-group">
              <label class="field-group__label">Vehicle Type</label>
              <select id="vehicleType" class="field-group__select">
                <option value="car">Car</option>
                <option value="suv">SUV / Truck</option>
                <option value="motorcycle">Motorcycle</option>
                <option value="disabled">Disabled</option>
              </select>
            </div>
            <div class="field-group">
              <label class="field-group__label">Assign Slot</label>
              <select id="slotSelect" class="field-group__select">
                <option value="">Loading slots...</option>
              </select>
            </div>
          </div>
          <button class="btn-add" id="btnAddVehicle" type="button">
            Add Vehicle &amp; Open Gate
          </button>
        </div>

      </div>
    </section>

    <!-- ══════════════════════════════════════════
         3. SLOTS GRID
    ══════════════════════════════════════════ -->
    <section class="admin-section">
      <div class="section-title">
        <span class="section-title__icon">🅿️</span>
        Parking Slots
      </div>

      <!-- Level tabs -->
      <div class="level-tabs" id="levelTabs"></div>

      <!-- Slots grid -->
      <div class="slots-grid" id="slotsGrid">
        <div class="loading-pulse">Loading slots...</div>
      </div>
    </section>

    <!-- ══════════════════════════════════════════
         4. ACTIVE SESSIONS
    ══════════════════════════════════════════ -->
    <section class="admin-section">
      <div class="section-title">
        <span class="section-title__icon">⏱</span>
        Active Sessions
      </div>
      <div class="sessions-table-wrap">
        <table class="sessions-table" id="sessionsTable">
          <thead>
            <tr>
              <th>Plate</th>
              <th>Level</th>
              <th>Slot</th>
              <th>Started</th>
              <th>Duration</th>
              <th>Cost</th>
              <th>Double Bill</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody id="sessionsBody">
            <tr><td colspan="8" class="table-empty">Loading sessions...</td></tr>
          </tbody>
        </table>
      </div>
    </section>

    <!-- ══════════════════════════════════════════
         5. GATE CONTROL
    ══════════════════════════════════════════ -->
    <section class="admin-section">
      <div class="section-title">
        <span class="section-title__icon">🚧</span>
        Gate Control
      </div>
      <div class="gate-panel" id="gatePanel">
        <div class="loading-pulse">Loading gates...</div>
      </div>
    </section>

    <!-- ══════════════════════════════════════════
         6. PAYMENTS
    ══════════════════════════════════════════ -->
    <section class="admin-section">
      <div class="section-title">
        <span class="section-title__icon">💳</span>
        Pending Payments
      </div>
      <div class="payments-list" id="paymentsList">
        <div class="loading-pulse">Loading payments...</div>
      </div>
    </section>


  </main>

  <script src="js/admin.js"></script>
</body>
</html>