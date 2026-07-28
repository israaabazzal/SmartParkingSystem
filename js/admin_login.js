const loginBtn   = document.getElementById('loginBtn');
const loginError = document.getElementById('loginError');
const errorText  = document.getElementById('loginErrorText');
const pwdInput   = document.getElementById('password');
const userInput  = document.getElementById('username');
const togglePwd  = document.getElementById('togglePwd');

// ── Show / hide error ─────────────────────────
function showError(msg) {
  errorText.textContent = msg;
  loginError.style.display = 'flex';
  loginError.classList.add('shake');
  setTimeout(() => loginError.classList.remove('shake'), 500);
}
function hideError() {
  loginError.style.display = 'none';
}

// ── Clear error on input ──────────────────────
[userInput, pwdInput].forEach(el => el.addEventListener('input', hideError));

// ── Toggle password visibility ────────────────
togglePwd.addEventListener('click', function () {
  const visible = pwdInput.type === 'text';
  pwdInput.type = visible ? 'password' : 'text';
  togglePwd.style.opacity = visible ? '0.5' : '1';
});

// ── Enter key triggers login ──────────────────
[userInput, pwdInput].forEach(el => {
  el.addEventListener('keydown', function (e) {
    if (e.key === 'Enter') doLogin();
  });
});

// ── Login button click ────────────────────────
loginBtn.addEventListener('click', doLogin);

// ── AJAX login ────────────────────────────────
async function doLogin() {
  const u = userInput.value.trim();
  const p = pwdInput.value;

  if (!u) { showError('Please enter your username.'); userInput.focus(); return; }
  if (!p) { showError('Please enter your password.'); pwdInput.focus(); return; }

  loginBtn.querySelector('.btn-login__text').style.display  = 'none';
  loginBtn.querySelector('.btn-login__loader').style.display = 'inline-flex';
  loginBtn.disabled = true;
  hideError();

  try {
    const res  = await fetch('api/admin_auth.php', {
      method:  'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body:    new URLSearchParams({ username: u, password: p })
    });
    const data = await res.json();

    if (data.success) {
      loginBtn.querySelector('.btn-login__loader').style.display = 'none';
      loginBtn.querySelector('.btn-login__text').style.display   = 'inline';
      loginBtn.querySelector('.btn-login__text').textContent      = '✓ Redirecting...';
      loginBtn.style.background = 'rgba(84,172,191,0.3)';
      setTimeout(() => { window.location.href = 'admin.php'; }, 600);
    } else {
      showError(data.error || 'Invalid credentials. Please try again.');
      resetBtn();
    }
  } catch (e) {
    showError('Connection error. Please try again.');
    resetBtn();
  }
}

function resetBtn() {
  loginBtn.querySelector('.btn-login__text').style.display   = 'inline';
  loginBtn.querySelector('.btn-login__loader').style.display = 'none';
  loginBtn.disabled = false;
}