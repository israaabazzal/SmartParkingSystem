// ═══════════════════════════════════════════════
// SMART PARKING — admin.js
// ═══════════════════════════════════════════════

let currentLevel   = null;
let sessionTimers  = {};   // { session_id: intervalId }
let sessionStarts  = {};   // { session_id: timestamp_ms }

// ── Boot ──────────────────────────────────────
document.addEventListener('DOMContentLoaded', () => {
  loadStats();
  loadSlots();
  loadSessions();
  loadGates();
  loadPayments();
  

  // Auto-refresh every 15s
  setInterval(() => {
    loadStats();
    loadSessions();
    loadPayments();
  }, 15000);

  // Entry plate input
  const entryPlate = document.getElementById('entryPlate');
  if (entryPlate) {
    entryPlate.addEventListener('input', function () {
      this.value = this.value.toUpperCase();
    });
    entryPlate.addEventListener('keydown', function (e) {
      if (e.key === 'Enter') checkPlate();
    });
  }
  document.getElementById('btnCheck')?.addEventListener('click', checkPlate);
  document.getElementById('btnAddVehicle')?.addEventListener('click', addVehicle);

const vehicleTypeLevelMap = {
  'car':        2,
  'motorcycle': 2,
  'suv':        3,
  'disabled':   1
};

document.getElementById('vehicleType')?.addEventListener('change', function () {
  const levelId = vehicleTypeLevelMap[this.value] || null;
  loadAvailableSlots(levelId);
});

const defaultType = document.getElementById('vehicleType')?.value;
loadAvailableSlots(vehicleTypeLevelMap[defaultType] || null);

});

// ═══════════════════════════════════════════════
// 1. STATS  
// ═══════════════════════════════════════════════
async function loadStats() {
  try {
    const res  = await fetch('api/get_stats.php');
    const data = await res.json();
    document.getElementById('statTotal').textContent      = data.total      ?? '—';
    document.getElementById('statAvailable').textContent  = data.available  ?? '—';
    document.getElementById('statOccupied').textContent   = data.occupied   ?? '—';
    document.getElementById('statViolations').textContent = data.violations ?? '—';
  } catch (e) { console.error('Stats error:', e); }
}

// ═══════════════════════════════════════════════
// 2. VEHICLE ENTRY — check plate
// ═══════════════════════════════════════════════
async function checkPlate() {
  const plate = document.getElementById('entryPlate').value.trim();
  if (!plate) return;

  const resultBox   = document.getElementById('entryResult');
  const addForm     = document.getElementById('addVehicleForm');

  resultBox.style.display = 'none';
  addForm.style.display   = 'none';

  try {
    const res  = await fetch('api/check_vehicle.php', {
      method:  'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body:    new URLSearchParams({ plate })
    });
    const data = await res.json();

    resultBox.style.display = 'flex';

    if (data.status === 'parked') {
      resultBox.className = 'entry-result entry-result--warn';
      resultBox.innerHTML = `⚠️ <strong>${escHtml(plate)}</strong> is already parked in slot <strong>${escHtml(data.slot_code)}</strong> — ${escHtml(data.level_name)}.`;
    } else if (data.status === 'free' || data.status === 'new') {
      resultBox.className = 'entry-result entry-result--ok';
      resultBox.innerHTML = data.status === 'new'
        ? `✓ New vehicle. Fill in details below to assign a slot.`
        : `✓ Vehicle found. Fill in details below to assign a slot.`;
      addForm.style.display = 'flex';
    } else {
      resultBox.className = 'entry-result entry-result--error';
      resultBox.innerHTML = data.error || 'Unknown error.';
    }
  } catch (e) {
    resultBox.style.display = 'flex';
    resultBox.className = 'entry-result entry-result--error';
    resultBox.textContent = 'Connection error.';
  }
}

// Load available slots into the slot select
async function loadAvailableSlots(filterLevelId = null) {
  try {
    const res  = await fetch('api/get_available_slots.php');
    const data = await res.json();
    const sel  = document.getElementById('slotSelect');
    if (!sel) return;

    let slots = data.slots;
    if (filterLevelId) {
      slots = slots.filter(s => parseInt(s.level_id) === parseInt(filterLevelId));
    }

    sel.innerHTML = slots.length === 0
      ? '<option value="">No slots available for this type</option>'
      : slots.map(s => `<option value="${s.slot_id}">${escHtml(s.level_name)} — ${escHtml(s.slot_code)}</option>`).join('');
  } catch (e) { console.error('Slots load error:', e); }
}

// Add vehicle & open entry gate
async function addVehicle() {
  const plate       = document.getElementById('entryPlate').value.trim();
  const vehicleType = document.getElementById('vehicleType').value;
  const slotId      = document.getElementById('slotSelect').value;
  const btn         = document.getElementById('btnAddVehicle');

  if (!plate || !slotId) {
    showEntryResult('error', 'Please fill in all fields.');
    return;
  }

  btn.disabled = true;
  btn.textContent = 'Processing...';

  try {
    const res  = await fetch('api/add_vehicle.php', {
      method:  'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body:    new URLSearchParams({ plate, vehicle_type: vehicleType, slot_id: slotId })
    });
    const data = await res.json();

    if (data.success) {
      showEntryResult('ok', `✓ Vehicle ${escHtml(plate)} added to slot ${escHtml(data.slot_code)}. Entry gate opened.`);
      document.getElementById('entryPlate').value = '';
      document.getElementById('addVehicleForm').style.display = 'none';
      loadStats();
      loadSlots();
      loadSessions();
      loadAvailableSlots();
    } else {
      showEntryResult('error', data.error || 'Failed to add vehicle.');
    }
  } catch (e) {
    showEntryResult('error', 'Connection error.');
  } finally {
    btn.disabled = false;
    btn.textContent = 'Add Vehicle & Open Gate';
  }
}

function showEntryResult(type, msg) {
  const box = document.getElementById('entryResult');
  box.className = 'entry-result entry-result--' + (type === 'ok' ? 'ok' : 'error');
  box.innerHTML = msg;
  box.style.display = 'flex';
}

// ═══════════════════════════════════════════════
// 3. SLOTS GRID
// ═══════════════════════════════════════════════
async function loadSlots() {
  try {
    const res  = await fetch('api/get_level_slots.php');
    const data = await res.json();

    // Build level tabs
    const tabsEl = document.getElementById('levelTabs');
    if (tabsEl && tabsEl.children.length === 0) {
      data.levels.forEach((lvl, i) => {
        const btn = document.createElement('button');
        btn.className = 'level-tab' + (i === 0 ? ' level-tab--active' : '');
        btn.textContent = lvl.level_name;
        btn.dataset.levelId = lvl.level_id;
        btn.addEventListener('click', () => {
          document.querySelectorAll('.level-tab').forEach(t => t.classList.remove('level-tab--active'));
          btn.classList.add('level-tab--active');
          renderSlots(data.levels.find(l => l.level_id == btn.dataset.levelId)?.slots || []);
        });
        tabsEl.appendChild(btn);
      });
    }

    if (currentLevel === null && data.levels.length > 0) {
      currentLevel = data.levels[0].level_id;
    }

    const activeLvl = data.levels.find(l => l.level_id == currentLevel) || data.levels[0];
    if (activeLvl) renderSlots(activeLvl.slots);

  } catch (e) { console.error('Slots error:', e); }
}

function renderSlots(slots) {
  const grid = document.getElementById('slotsGrid');
  if (!slots || slots.length === 0) {
    grid.innerHTML = '<div class="empty-state">No slots found.</div>';
    return;
  }
  grid.innerHTML = slots.map(s => {
    const isOcc = s.status === 'occupied';
    return `
      <div class="slot-card ${isOcc ? 'slot-card--occupied' : 'slot-card--available'}">
        <div class="slot-card__code">${escHtml(s.slot_code)}</div>
        <div class="slot-card__status">${isOcc ? 'Occupied' : 'Available'}</div>
        ${isOcc && s.plate_number ? `<div class="slot-card__plate">${escHtml(s.plate_number)}</div>` : ''}
        <div class="slot-card__toggle">
          <span class="slot-card__toggle-label">×2 Bill</span>
          <label class="toggle-switch">
            <input type="checkbox" ${s.is_double_billing ? 'checked' : ''}
              onchange="toggleDoubleBilling(${s.slot_id}, this.checked)">
            <span class="toggle-slider"></span>
          </label>
        </div>
      </div>`;
  }).join('');
}

async function toggleDoubleBilling(slotId, enabled) {
  try {
    await fetch('api/toggle_double.php', {
      method:  'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body:    new URLSearchParams({ slot_id: slotId, enabled: enabled ? 1 : 0 })
    });
  } catch (e) { console.error('Toggle error:', e); }
}

// ═══════════════════════════════════════════════
// 4. ACTIVE SESSIONS
// ═══════════════════════════════════════════════
async function loadSessions() {
  try {
    const res  = await fetch('api/get_sessions.php');
    const data = await res.json();
    renderSessions(data.sessions);
  } catch (e) { console.error('Sessions error:', e); }
}

function renderSessions(sessions) {
  const tbody = document.getElementById('sessionsBody');
  if (!sessions || sessions.length === 0) {
    tbody.innerHTML = '<tr><td colspan="8" class="table-empty">No active sessions.</td></tr>';
    // Clear all running timers
    Object.values(sessionTimers).forEach(clearInterval);
    sessionTimers = {};
    return;
  }

  tbody.innerHTML = sessions.map(s => {
    const timerStarted = s.time_start && s.status === 'active';
    const timerStopped = s.status === 'awaiting_payment';
    return `
      <tr id="session-row-${s.session_id}">
        <td><strong>${escHtml(s.plate_number)}</strong></td>
        <td>${escHtml(s.level_name)}</td>
        <td>${escHtml(s.slot_code)}</td>
        <td>${s.time_start ? escHtml(s.time_start) : '—'}</td>
        <td>
          <span class="session-live-timer" id="stimer-${s.session_id}">
            ${timerStopped ? formatTime(s.duration_seconds) : (timerStarted ? '...' : '—')}
          </span>
        </td>
        <td><span class="session-cost" id="scost-${s.session_id}">$${parseFloat(s.total_cost).toFixed(2)}</span></td>
        <td>
          <label class="toggle-switch">
            <input type="checkbox" ${s.is_double_billing ? 'checked' : ''}
              onchange="toggleSessionDouble(${s.session_id}, ${s.slot_id}, this.checked)">
            <span class="toggle-slider"></span>
          </label>
        </td>
        <td style="display:flex;gap:6px;flex-wrap:wrap;">
          <button class="btn-timer btn-timer--start" id="btn-start-${s.session_id}"
            onclick="timerStart(${s.session_id})"
            ${timerStarted || timerStopped ? 'disabled' : ''}>
            ▶ Start
          </button>
          <button class="btn-timer btn-timer--stop" id="btn-stop-${s.session_id}"
            onclick="timerStop(${s.session_id})"
            ${!timerStarted ? 'disabled' : ''}>
            ■ Stop
          </button>
        </td>
      </tr>`;
  }).join('');

  // Start live tickers for active sessions
  sessions.forEach(s => {
    if (s.status === 'active' && s.time_start) {
      if (sessionTimers[s.session_id]) clearInterval(sessionTimers[s.session_id]);
      const startMs = new Date(s.time_start.replace(' ', 'T')).getTime();
      sessionTimers[s.session_id] = setInterval(() => {
        const el = document.getElementById('stimer-' + s.session_id);
        if (el) el.textContent = formatTime(Math.floor((Date.now() - startMs) / 1000));
      }, 1000);
    }
  });
}

async function timerStart(sessionId) {
  const btn = document.getElementById('btn-start-' + sessionId);
  if (btn) btn.disabled = true;
  try {
    const res  = await fetch('api/timer_start.php', {
      method:  'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body:    new URLSearchParams({ session_id: sessionId })
    });
    const data = await res.json();
    if (data.success) {
      loadSessions();
    } else {
      alert(data.error || 'Failed to start timer.');
      if (btn) btn.disabled = false;
    }
  } catch (e) { console.error('Timer start error:', e); if (btn) btn.disabled = false; }
}

async function timerStop(sessionId) {
  const btn = document.getElementById('btn-stop-' + sessionId);
  if (btn) btn.disabled = true;
  if (sessionTimers[sessionId]) {
    clearInterval(sessionTimers[sessionId]);
    delete sessionTimers[sessionId];
  }
  try {
    const timerEl = document.getElementById('stimer-' + sessionId);
    const elapsed = timerEl ? parseElapsed(timerEl.textContent) : 0;
    const res  = await fetch('api/timer_stop.php', {
      method:  'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body:    new URLSearchParams({ session_id: sessionId, duration_seconds: elapsed })
    });
    const data = await res.json();
    if (data.success) {
      loadSessions();
      loadStats();
      loadPayments();
    } else {
      alert(data.error || 'Failed to stop timer.');
      if (btn) btn.disabled = false;
    }
  } catch (e) { console.error('Timer stop error:', e); if (btn) btn.disabled = false; }
}

async function toggleSessionDouble(sessionId, slotId, enabled) {
  try {
    await fetch('api/toggle_double.php', {
      method:  'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body:    new URLSearchParams({ session_id: sessionId, slot_id: slotId, enabled: enabled ? 1 : 0 })
    });
  } catch (e) { console.error('Toggle error:', e); }
}

// ═══════════════════════════════════════════════
// 5. GATES
// ═══════════════════════════════════════════════
async function loadGates() {
  try {
    const res  = await fetch('api/get_gates.php');
    const data = await res.json();
    renderGates(data.gates);
  } catch (e) { console.error('Gates error:', e); }
}

function renderGates(gates) {
  const panel = document.getElementById('gatePanel');
  if (!gates || gates.length === 0) {
    panel.innerHTML = '<div class="empty-state">No gate data.</div>';
    return;
  }
  panel.innerHTML = gates.map(g => {
    const isOpen = g.status === 'open';
    return `
      <div class="gate-card">
        <div class="gate-card__header">
          <span class="gate-card__name">${escHtml(g.gate_type.toUpperCase())} GATE</span>
          <span class="gate-status ${isOpen ? 'gate-status--open' : 'gate-status--closed'}" id="gate-status-${g.gate_id}">
            ${isOpen ? 'OPEN' : 'CLOSED'}
          </span>
        </div>
        <div class="gate-card__btns">
          <button class="btn-gate btn-gate--open" onclick="controlGate(${g.gate_id}, 'open')" ${isOpen ? 'disabled' : ''}>
            Open
          </button>
          <button class="btn-gate btn-gate--close" onclick="controlGate(${g.gate_id}, 'close')" ${!isOpen ? 'disabled' : ''}>
            Close
          </button>
        </div>
      </div>`;
  }).join('');
}

async function controlGate(gateId, action) {
  try {
    const res  = await fetch('api/gate_control.php', {
      method:  'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body:    new URLSearchParams({ gate_id: gateId, action })
    });
    const data = await res.json();
    if (data.success) loadGates();
    else alert(data.error || 'Gate control failed.');
  } catch (e) { console.error('Gate error:', e); }
}

// ═══════════════════════════════════════════════
// 6. PAYMENTS
// ═══════════════════════════════════════════════
async function loadPayments() {
  try {
    const res  = await fetch('api/get_payments.php');
    const data = await res.json();
    renderPayments(data.payments);
  } catch (e) { console.error('Payments error:', e); }
}

function renderPayments(payments) {
  const list = document.getElementById('paymentsList');
  if (!payments || payments.length === 0) {
    list.innerHTML = '<div class="empty-state">No pending payments.</div>';
    return;
  }
  list.innerHTML = payments.map(p => `
    <div class="payment-item" id="payment-item-${p.payment_id}">
      <div class="payment-item__plate">${escHtml(p.plate_number)}</div>
      <div class="payment-item__info">
        <div class="payment-item__method">${escHtml(p.payment_method.replace('_', ' '))}</div>
        <div class="payment-item__meta">Slot ${escHtml(p.slot_code)} · ${escHtml(p.level_name)}</div>
      </div>
      <div class="payment-item__amount">$${parseFloat(p.amount).toFixed(2)}</div>
      <button class="btn-confirm" onclick="confirmPayment(${p.payment_id}, ${p.session_id})">
        Confirm &amp; Open Gate
      </button>
    </div>`).join('');
}

async function confirmPayment(paymentId, sessionId) {
  const btn = document.querySelector(`#payment-item-${paymentId} .btn-confirm`);
  if (btn) btn.disabled = true;
  try {
    const res  = await fetch('api/confirm_payment.php', {
      method:  'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body:    new URLSearchParams({ payment_id: paymentId, session_id: sessionId })
    });
    const data = await res.json();
    if (data.success) {
      loadPayments();
      loadStats();
      loadSlots();
      loadSessions();
      loadGates();
    } else {
      alert(data.error || 'Confirm failed.');
      if (btn) btn.disabled = false;
    }
  } catch (e) { console.error('Confirm error:', e); if (btn) btn.disabled = false; }
}

// ═══════════════════════════════════════════════
// HELPERS
// ═══════════════════════════════════════════════
function formatTime(totalSeconds) {
  const h = Math.floor(totalSeconds / 3600);
  const m = Math.floor((totalSeconds % 3600) / 60);
  const s = totalSeconds % 60;
  return [h, m, s].map(v => String(v).padStart(2, '0')).join(':');
}

function parseElapsed(str) {
  const parts = str.split(':').map(Number);
  if (parts.length === 3) return parts[0]*3600 + parts[1]*60 + parts[2];
  return 0;
}

function escHtml(str) {
  const d = document.createElement('div');
  d.textContent = str;
  return d.innerHTML;
}