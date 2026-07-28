// ── Timer ─────────────────────────────────────
const display = document.getElementById('timerDisplay');

if (display && window.SESSION) {
  const status      = window.SESSION.status;
  const timeStart   = window.SESSION.timeStart;
  const durationSecs = window.SESSION.durationSecs;

  if (status === 'active' && timeStart) {
    // Live ticking — calculate elapsed from time_start
    const startMs = new Date(timeStart.replace(' ', 'T')).getTime();

    function tick() {
      const elapsed = Math.floor((Date.now() - startMs) / 1000);
      display.textContent = formatTime(elapsed);
    }
    tick();
    setInterval(tick, 1000);

  } else if (status === 'awaiting_payment') {
    // Timer stopped — show fixed duration
    display.textContent = formatTime(durationSecs);
    display.classList.add('timer-card__display--stopped');
  }
}

function formatTime(totalSeconds) {
  const h = Math.floor(totalSeconds / 3600);
  const m = Math.floor((totalSeconds % 3600) / 60);
  const s = totalSeconds % 60;
  return [h, m, s].map(v => String(v).padStart(2, '0')).join(':');
}

// ── Payment method accordion ──────────────────
function toggleMethod(method) {
  const allMethods = ['credit', 'mobile', 'cash'];
  allMethods.forEach(m => {
    const body = document.getElementById('body-' + m);
    const card = document.getElementById('method-' + m);
    if (!body || !card) return;
    if (m === method) {
      const isOpen = body.classList.contains('open');
      body.classList.toggle('open', !isOpen);
      card.classList.toggle('pay-method--active', !isOpen);
    } else {
      body.classList.remove('open');
      card.classList.remove('pay-method--active');
    }
  });
}

// ── Submit payment ────────────────────────────
async function submitPayment(method) {
  const sessionId = window.SESSION.sessionId;
  const msgBox    = document.getElementById('paymentMsg');

  // Basic validation
  if (method === 'credit_card') {
    const num    = document.getElementById('cardNumber').value.replace(/\s/g, '');
    const expiry = document.getElementById('cardExpiry').value;
    const cvv    = document.getElementById('cardCvv').value;
    if (num.length < 12 || !expiry || cvv.length < 3) {
      showMsg('error', 'Please fill in all card details correctly.');
      return;
    }
  }
  if (method === 'mobile_pay') {
    const phone = document.getElementById('mobilePhone').value.trim();
    if (phone.length < 8) {
      showMsg('error', 'Please enter a valid phone number.');
      return;
    }
  }

  // Disable all pay buttons
  document.querySelectorAll('.btn-pay').forEach(b => b.disabled = true);

  try {
    const res  = await fetch('api/submit_payment.php', {
      method:  'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body:    new URLSearchParams({ session_id: sessionId, payment_method: method })
    });
    const data = await res.json();

    if (data.success) {
      showMsg('success', '✓ Payment submitted. Waiting for admin confirmation.');
      document.querySelectorAll('.btn-pay').forEach(b => b.disabled = true);
    } else {
      showMsg('error', data.error || 'Payment failed. Please try again.');
      document.querySelectorAll('.btn-pay').forEach(b => b.disabled = false);
    }
  } catch (e) {
    showMsg('error', 'Connection error. Please try again.');
    document.querySelectorAll('.btn-pay').forEach(b => b.disabled = false);
  }
}

function showMsg(type, text) {
  const box = document.getElementById('paymentMsg');
  if (!box) return;
  box.className = 'payment-msg payment-msg--' + type;
  box.textContent = text;
  box.style.display = 'flex';
  box.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
}

// ── Credit card number formatting ─────────────
const cardInput = document.getElementById('cardNumber');
if (cardInput) {
  cardInput.addEventListener('input', function () {
    let val = this.value.replace(/\D/g, '').slice(0, 16);
    this.value = val.replace(/(.{4})/g, '$1 ').trim();
  });
}

// ── Expiry formatting ─────────────────────────
const expiryInput = document.getElementById('cardExpiry');
if (expiryInput) {
  expiryInput.addEventListener('input', function () {
    let val = this.value.replace(/\D/g, '').slice(0, 4);
    if (val.length >= 3) val = val.slice(0, 2) + '/' + val.slice(2);
    this.value = val;
  });
}

// ═══════════════════════════════════════════════
// MAP — slot highlight + path animation
// ═══════════════════════════════════════════════

// Slot X centers in the 800×500 viewBox (matching image layout)
// S1=142, S2=310, S3=478, S4=646  (center of each slot rect)
const SLOT_CONFIG = {
  1: { cx: 162, topY: 305 },   // cx: 91  + 143/2 = 162.5 | bottom: 120 + 185 = 305
  2: { cx: 324, topY: 305 },   // cx: 253 + 143/2 = 324.5 | bottom: 120 + 185 = 305
  3: { cx: 486, topY: 305 },   // cx: 415 + 143/2 = 486.5 | bottom: 120 + 185 = 305
  4: { cx: 648, topY: 305 }    // cx: 577 + 143/2 = 648.5 | bottom: 120 + 185 = 305
};

// Road Y position (center of the road lane in viewBox)
const ROAD_Y = 440;
const ENTRY_X   = 1;   // where the entry arrow starts
const SLOT_MID_Y = 277;  // 182 + 190/2 = 277  (not used in path but good to keep accurate)
function initMap() {
  const session = window.SESSION;
  if (!session) return;

  // Parse slot code e.g. "L1-S3" → level=1, slotNum=3
  // slotCode is injected from PHP into window.SESSION
  const slotCode = window.SLOT_CODE; // set below
  if (!slotCode) return;

  const match = slotCode.match(/L(\d+)-S(\d+)/i);
  if (!match) return;

  const levelNum = parseInt(match[1]);
  const slotNum  = parseInt(match[2]);

  // 1. Set correct map image
  const mapImg = document.getElementById('mapImage');
  if (mapImg) {
    mapImg.src = 'image/LEVEL ' + levelNum + '.png';
  }

  // 2. Update slot tag label
  const tag = document.getElementById('mapSlotTag');
  if (tag) tag.textContent = slotCode.toUpperCase();

  // 3. Show correct slot highlight
  const slotEl = document.getElementById('slot-' + slotNum);
  if (slotEl) slotEl.setAttribute('display', 'block');

  // 4. Animate path after short delay
  const cfg = SLOT_CONFIG[slotNum];
  if (!cfg) return;

  setTimeout(() => animatePath(cfg.cx, ROAD_Y, cfg.topY, slotNum), 400);
}

function animatePath(slotCx, roadY, slotTopY, slotNum) {
  const pathH = document.getElementById('pathH');
  const pathV = document.getElementById('pathV');
  const dot   = document.getElementById('arrivalDot');

  if (!pathH || !pathV) return;

  // Horizontal: from ENTRY_X along the road to slotCx
  pathH.setAttribute('x1', ENTRY_X);
  pathH.setAttribute('y1', roadY);
  pathH.setAttribute('x2', ENTRY_X); // start collapsed
  pathH.setAttribute('y2', roadY);
  pathH.classList.add('visible');

  // Animate horizontal expansion
  const hDist   = slotCx - ENTRY_X;
  const hSteps  = 40;
  const hStep   = hDist / hSteps;
  let   hCurrent = ENTRY_X;
  let   hCount   = 0;

  const hTimer = setInterval(() => {
    hCurrent += hStep;
    hCount++;
    pathH.setAttribute('x2', hCurrent);
    if (hCount >= hSteps) {
      clearInterval(hTimer);
      pathH.setAttribute('x2', slotCx);
      // Then animate vertical
      animateVertical(pathV, dot, slotCx, roadY, slotTopY);
    }
  }, 18);
}

function animateVertical(pathV, dot, slotCx, roadY, slotTopY) {
  pathV.setAttribute('x1', slotCx);
  pathV.setAttribute('y1', roadY);
  pathV.setAttribute('x2', slotCx);
  pathV.setAttribute('y2', roadY); // start collapsed
  pathV.classList.add('visible');

  const vDist   = roadY - slotTopY;
  const vSteps  = 35;
  const vStep   = vDist / vSteps;
  let   vCurrent = roadY;
  let   vCount   = 0;

  const vTimer = setInterval(() => {
    vCurrent -= vStep;
    vCount++;
    pathV.setAttribute('y2', vCurrent);
    if (vCount >= vSteps) {
      clearInterval(vTimer);
      pathV.setAttribute('y2', slotTopY);
      // Show arrival dot at slot center
      if (dot) {
        dot.setAttribute('cx', slotCx);
        dot.setAttribute('cy', slotTopY);
        dot.setAttribute('opacity', '1');
      }
    }
  }, 18);
}

// Inject SLOT_CODE from PHP via the page
// (added as a global in user.php <script> block)
document.addEventListener('DOMContentLoaded', initMap);