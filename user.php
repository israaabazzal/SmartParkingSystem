<?php
require_once 'api/db.php';

$plate = strtoupper(trim($_GET['plate'] ?? ''));

if ($plate === '') {
    header('Location: index.php');
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM vehicles WHERE plate_number = ?");
$stmt->execute([$plate]);
$vehicle = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$vehicle) {
    header('Location: index.php');
    exit;
}

$stmt = $pdo->prepare("
    SELECT ps.*, sl.slot_code, sl.level_id, sl.is_double_billing,
           pl.level_name
    FROM parking_sessions ps
    JOIN parking_slots sl ON sl.slot_id = ps.slot_id
    JOIN parking_levels pl ON pl.level_id = sl.level_id
    WHERE ps.vehicle_id = ?
      AND ps.status IN ('active','awaiting_payment')
    ORDER BY ps.session_id DESC
    LIMIT 1
");
$stmt->execute([$vehicle['vehicle_id']]);
$session = $stmt->fetch(PDO::FETCH_ASSOC);

$stmt = $pdo->prepare("SELECT COUNT(*) FROM parking_sessions WHERE vehicle_id = ? AND status = 'completed'");
$stmt->execute([$vehicle['vehicle_id']]);
$completedCount = $stmt->fetchColumn();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>My Parking — <?= htmlspecialchars($plate) ?></title>
  <link rel="stylesheet" href="css/style.css">
  <link rel="stylesheet" href="css/user.css">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=DM+Sans:wght@300;400;500&family=DM+Mono:wght@400;500&display=swap" rel="stylesheet">
</head>
<body>

  <div class="bg-grid"></div>
  <div class="bg-glow bg-glow--1"></div>
  <div class="bg-glow bg-glow--2"></div>

  <header class="topbar">
    <div class="topbar__logo">
      <span class="topbar__logo-icon">P</span>
      <span class="topbar__logo-text">SMART PARKING</span>
    </div>
    <a href="index.php" class="topbar__admin-link">← Back</a>
  </header>

  <main class="user-main">

    <?php if (!$session): ?>
    <div class="no-session">
      <div class="no-session__icon">
        <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="12" cy="12" r="10"/><path d="M12 8v4M12 16h.01"/></svg>
      </div>
      <h2 class="no-session__title">No Active Session</h2>
      <p class="no-session__sub">No active parking session found for plate <strong><?= htmlspecialchars($plate) ?></strong>.</p>
      <a href="index.php" class="btn-back-home">Back to Home</a>
    </div>

    <?php else: ?>

    <script>
      window.SESSION = {
        sessionId:       <?= (int)$session['session_id'] ?>,
        status:          "<?= htmlspecialchars($session['status']) ?>",
        timeStart:       "<?= htmlspecialchars($session['time_start'] ?? '') ?>",
        durationSecs:    <?= (int)$session['duration_seconds'] ?>,
        totalCost:       <?= (float)$session['total_cost'] ?>,
        baseCost:        <?= (float)$session['base_cost'] ?>,
        violationCharge: <?= (float)$session['violation_charge'] ?>,
        isDoubleBilling: <?= (int)$session['is_double_billing'] ?>
      };
      window.VEHICLE = {
        plate: "<?= htmlspecialchars($vehicle['plate_number']) ?>",
        type:  "<?= htmlspecialchars($vehicle['vehicle_type']) ?>"
      };
      window.SLOT_CODE = "<?= htmlspecialchars($session['slot_code']) ?>";
    </script>

    <div class="user-layout">

      <!-- LEFT COLUMN -->
      <div class="user-left">

        <!-- Greeting card -->
        <div class="greeting-card">
          <div class="greeting-card__left">
            <div class="greeting-card__eyebrow">Welcome back</div>
            <div class="greeting-card__plate"><?= htmlspecialchars($plate) ?></div>
            <div class="greeting-card__type"><?= htmlspecialchars(ucfirst($vehicle['vehicle_type'])) ?></div>
          </div>
          <div class="greeting-card__right">
            <div class="greeting-card__stat">
              <span class="greeting-card__stat-val"><?= (int)$completedCount ?></span>
              <span class="greeting-card__stat-label">Past Sessions</span>
            </div>
            <div class="status-pill <?= $session['status'] === 'active' ? 'status-pill--active' : 'status-pill--waiting' ?>">
              <?= $session['status'] === 'active' ? 'ACTIVE' : 'AWAITING PAYMENT' ?>
            </div>
          </div>
        </div>

        <!-- Parking details -->
        <div class="info-card">
          <div class="info-card__header">Parking Details</div>
          <div class="info-card__grid">
            <div class="info-item">
              <span class="info-item__label">Level</span>
              <span class="info-item__value"><?= htmlspecialchars($session['level_name']) ?></span>
            </div>
            <div class="info-item">
              <span class="info-item__label">Slot</span>
              <span class="info-item__value info-item__value--accent"><?= htmlspecialchars($session['slot_code']) ?></span>
            </div>
            <div class="info-item">
              <span class="info-item__label">Vehicle Type</span>
              <span class="info-item__value"><?= htmlspecialchars(ucfirst($vehicle['vehicle_type'])) ?></span>
            </div>
            <div class="info-item">
              <span class="info-item__label">Double Billing</span>
              <span class="info-item__value <?= $session['is_double_billing'] ? 'info-item__value--warn' : '' ?>">
                <?= $session['is_double_billing'] ? 'YES ×2' : 'No' ?>
              </span>
            </div>
          </div>
        </div>

        <!-- Map card -->
        <div class="map-card" id="mapCard">
          <div class="map-card__header">
            <span class="map-card__label">Parking Map</span>
            <span class="map-card__slot-tag" id="mapSlotTag"></span>
          </div>
          <div class="map-card__wrap" id="mapWrap">
            <img class="map-card__img" id="mapImage" src="" alt="Parking Level Map">
            <svg class="map-overlay" id="mapOverlay" viewBox="0 0 800 500" preserveAspectRatio="xMidYMid meet">
              <line id="pathH" x1="60" y1="400" x2="60" y2="400" stroke="#54ACBF" stroke-width="4" stroke-linecap="round" stroke-dasharray="12 8"/>
              <line id="pathV" x1="60" y1="400" x2="60" y2="400" stroke="#54ACBF" stroke-width="4" stroke-linecap="round" stroke-dasharray="12 8"/>
              <rect id="slot-1" class="slot-highlight" x="91" y="120" width="143" height="185" rx="8" display="none"/>
              <rect id="slot-2" class="slot-highlight" x="253" y="120" width="143" height="185" rx="8" display="none"/>
              <rect id="slot-3" class="slot-highlight" x="415" y="120" width="143" height="185" rx="8" display="none"/>
              <rect id="slot-4" class="slot-highlight" x="577" y="120" width="143" height="185" rx="8" display="none"/>
              <circle id="arrivalDot" cx="60" cy="400" r="7" fill="#54ACBF" opacity="0"/>
            </svg>
          </div>
        </div>

        <!-- Timer card -->
        <div class="timer-card" id="timerCard">
          <div class="timer-card__label">Session Duration</div>
          <div class="timer-card__display" id="timerDisplay">00:00:00</div>
          <div class="timer-card__started">
            Started: <span id="startedAt"><?= $session['time_start'] ? date('h:i A', strtotime($session['time_start'])) : '—' ?></span>
          </div>
        </div>

        <!-- Cost card -->
        <div class="cost-card">
          <div class="cost-card__header">Billing Summary</div>
          <div class="cost-card__rows">
            <div class="cost-row">
              <span class="cost-row__label">Base Cost</span>
              <span class="cost-row__val" id="baseCost">$<?= number_format($session['base_cost'], 2) ?></span>
            </div>
            <?php if ($session['violation_charge'] > 0): ?>
            <div class="cost-row cost-row--violation">
              <span class="cost-row__label">Violation Charge</span>
              <span class="cost-row__val" id="violationCost">+$<?= number_format($session['violation_charge'], 2) ?></span>
            </div>
            <?php endif; ?>
            <div class="cost-row cost-row--total">
              <span class="cost-row__label">Total</span>
              <span class="cost-row__val" id="totalCost">$<?= number_format($session['total_cost'], 2) ?></span>
            </div>
          </div>
        </div>

      </div>

      <!-- RIGHT COLUMN -->
      <div class="user-right">

        <?php if ($session['status'] === 'awaiting_payment'): ?>
        <div class="payment-card">
          <div class="payment-card__header">
            <span class="payment-card__title">Choose Payment Method</span>
            <span class="payment-card__amount" id="payAmount">$<?= number_format($session['total_cost'], 2) ?></span>
          </div>
          <div class="payment-msg" id="paymentMsg" style="display:none;"></div>
          <div class="pay-methods">

            <!-- Credit Card -->
            <div class="pay-method" id="method-credit">
              <div class="pay-method__header" onclick="toggleMethod('credit')">
                <div class="pay-method__icon">
                  <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="1" y="4" width="22" height="16" rx="2"/><line x1="1" y1="10" x2="23" y2="10"/></svg>
                </div>
                <span class="pay-method__name">Credit Card</span>
                <span class="pay-method__chevron">›</span>
              </div>
              <div class="pay-method__body" id="body-credit">
                <div class="pay-field">
                  <label>Card Number</label>
                  <input type="text" id="cardNumber" placeholder="1234 5678 9012 3456" maxlength="19" autocomplete="off">
                </div>
                <div class="pay-field-row">
                  <div class="pay-field">
                    <label>Expiry</label>
                    <input type="text" id="cardExpiry" placeholder="MM/YY" maxlength="5">
                  </div>
                  <div class="pay-field">
                    <label>CVV</label>
                    <input type="text" id="cardCvv" placeholder="123" maxlength="4">
                  </div>
                </div>
                <button class="btn-pay" onclick="submitPayment('credit_card')">Pay $<?= number_format($session['total_cost'], 2) ?></button>
              </div>
            </div>

            <!-- Mobile Pay -->
            <div class="pay-method" id="method-mobile">
              <div class="pay-method__header" onclick="toggleMethod('mobile')">
                <div class="pay-method__icon">
                  <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="5" y="2" width="14" height="20" rx="2"/><line x1="12" y1="18" x2="12.01" y2="18"/></svg>
                </div>
                <span class="pay-method__name">Mobile Pay</span>
                <span class="pay-method__chevron">›</span>
              </div>
              <div class="pay-method__body" id="body-mobile">
                <div class="pay-field">
                  <label>Phone Number</label>
                  <input type="tel" id="mobilePhone" placeholder="+1 234 567 8900" maxlength="15">
                </div>
                <button class="btn-pay" onclick="submitPayment('mobile_pay')">Pay $<?= number_format($session['total_cost'], 2) ?></button>
              </div>
            </div>

            <!-- Cash -->
            <div class="pay-method" id="method-cash">
              <div class="pay-method__header" onclick="toggleMethod('cash')">
                <div class="pay-method__icon">
                  <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="2" y="6" width="20" height="12" rx="2"/><circle cx="12" cy="12" r="3"/><path d="M6 12h.01M18 12h.01"/></svg>
                </div>
                <span class="pay-method__name">Cash</span>
                <span class="pay-method__chevron">›</span>
              </div>
              <div class="pay-method__body" id="body-cash">
                <p class="pay-cash-note">Please proceed to the parking booth to pay <strong>$<?= number_format($session['total_cost'], 2) ?></strong> in cash. The gate will open once payment is confirmed by the admin.</p>
                <button class="btn-pay btn-pay--cash" onclick="submitPayment('cash')">Notify Admin — Cash Payment</button>
              </div>
            </div>

          </div>
        </div>

        <?php else: ?>
        <div class="waiting-card">
          <div class="waiting-card__icon">
            <svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
          </div>
          <div class="waiting-card__title">Session In Progress</div>
          <div class="waiting-card__sub">Your parking session is active. The timer is running. When you're ready to leave, the admin will stop the timer and your payment details will appear here.</div>
        </div>
        <?php endif; ?>

        <!-- Help card -->
        <div class="help-card">
          <div class="help-card__title">Need Help?</div>
          <div class="help-card__items">
            <div class="help-item">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07A19.5 19.5 0 0 1 4.69 13a19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 3.6 2h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
              Call the booth
            </div>
            <div class="help-item">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M12 8v4M12 16h.01"/></svg>
              Rate: $0.05/min · Violation ×2
            </div>
          </div>
        </div>

      </div>
    </div>
    <?php endif; ?>

  </main>

  <script src="js/user.js"></script>
</body>
</html>