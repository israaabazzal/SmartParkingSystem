// ── Auto-uppercase plate input ────────────────
const plateInput = document.getElementById('plateInput');
const fieldWrap  = document.getElementById('fieldWrap');
const errorMsg   = document.getElementById('errorMsg');
const findBtn    = document.getElementById('findBtn');

plateInput.addEventListener('input', function () {
  const pos = this.selectionStart;
  this.value = this.value.toUpperCase();
  this.setSelectionRange(pos, pos);
  fieldWrap.classList.remove('search-form__field--error');
  errorMsg.style.display = 'none';
});

// ── Enter key submits ─────────────────────────
plateInput.addEventListener('keydown', function (e) {
  if (e.key === 'Enter') {
    e.preventDefault();
    document.getElementById('searchForm').submit();
  }
});

// ── Loading state on submit ───────────────────
document.getElementById('searchForm').addEventListener('submit', function () {
  findBtn.querySelector('.btn-find__text').style.display  = 'none';
  findBtn.querySelector('.btn-find__arrow').style.display = 'none';
  findBtn.querySelector('.btn-find__loader').style.display = 'inline-flex';
  findBtn.disabled = true;
});

// ── Level availability cards ──────────────────
async function fetchLevels() {
  const dot = document.getElementById('liveDot');
  dot.classList.add('pulse');
  try {
    const res  = await fetch('api/get_stats.php');
    const data = await res.json();
    renderLevels(data.levels);
  } catch (e) {
    console.error('Could not fetch levels:', e);
  } finally {
    dot.classList.remove('pulse');
  }
}

function renderLevels(levels) {
  const list = document.getElementById('levelsList');
  if (!levels || levels.length === 0) {
    list.innerHTML = '<p class="levels__empty">No level data available.</p>';
    return;
  }

  list.innerHTML = levels.map(lvl => {
    const available  = parseInt(lvl.available);
    const total      = parseInt(lvl.total);
    const occupied   = total - available;
    const pct        = total > 0 ? Math.round((occupied / total) * 100) : 0;
    const isFull     = available === 0;
    const badgeClass = isFull ? 'badge--full' : 'badge--open';
    const badgeText  = isFull ? 'FULL' : 'OPEN';

    return `
      <div class="level-card ${isFull ? 'level-card--full' : ''}">
        <div class="level-card__top">
          <div class="level-badge">L${escHtml(String(lvl.level_id))}</div>
          <span class="level-card__name">${escHtml(lvl.level_name)}</span>
          <span class="badge ${badgeClass}">${badgeText}</span>
        </div>
        <div class="level-card__counts">
          <span class="level-card__avail">${available}</span>
          <span class="level-card__sep">/</span>
          <span class="level-card__total">${total}</span>
          <span class="level-card__unit">slots free</span>
        </div>
        <div class="level-card__bar">
          <div class="level-card__bar-fill" style="width:${pct}%"></div>
        </div>
      </div>`;
  }).join('');
}

function escHtml(str) {
  const d = document.createElement('div');
  d.textContent = str;
  return d.innerHTML;
}

// Fetch on load + every 10 seconds
fetchLevels();
setInterval(fetchLevels, 10000);
