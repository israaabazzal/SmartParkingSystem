<?php
session_start();
if (isset($_SESSION['admin_id'])) {
    header('Location: admin.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Access — Smart Parking</title>
  <link rel="stylesheet" href="css/admin_login.css">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=DM+Sans:wght@300;400;500&family=DM+Mono:wght@400;500&display=swap" rel="stylesheet">
</head>
<body>

  <!-- Video background -->
  <div class="video-wrap">
    <video class="video-bg" autoplay muted loop playsinline>
      <source src="image/bg.mp4" type="video/mp4">
    </video>
    <div class="video-overlay"></div>
  </div>

  <!-- Back to home -->
  <a href="index.php" class="btn-back">
    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 12H5"/><path d="M12 19l-7-7 7-7"/></svg>
    Back to Home
  </a>

  <!-- Login card -->
  <main class="login-wrap">
    <div class="login-card">

      <div class="login-card__header">
        <div class="login-card__icon"><span>P</span></div>
        <h1 class="login-card__title">ADMIN ACCESS</h1>
        <p class="login-card__sub">Sign in to manage the parking, monitor slots and billing</p>
      </div>

      <div class="login-error" id="loginError" style="display:none;">
        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
        <span id="loginErrorText"></span>
      </div>

      <div class="login-card__form">

        <div class="field-group" id="fieldUsername">
          <label class="field-group__label" for="username">Username</label>
          <div class="field-group__wrap">
            <svg class="field-group__icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
            <input type="text" id="username" name="username" class="field-group__input" placeholder="Enter username" autocomplete="username" spellcheck="false">
          </div>
        </div>

        <div class="field-group" id="fieldPassword">
          <label class="field-group__label" for="password">Password</label>
          <div class="field-group__wrap">
            <svg class="field-group__icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
            <input type="password" id="password" name="password" class="field-group__input" placeholder="Enter password" autocomplete="current-password">
            <button type="button" class="field-group__toggle" id="togglePwd" aria-label="Toggle password visibility">
              <svg id="eyeIcon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
            </button>
          </div>
        </div>

        <button class="btn-login" id="loginBtn" type="button">
          <span class="btn-login__text">LOGIN</span>
          <span class="btn-login__loader" style="display:none;">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" class="spin"><path d="M12 2v4M12 18v4M4.93 4.93l2.83 2.83M16.24 16.24l2.83 2.83M2 12h4M18 12h4M4.93 19.07l2.83-2.83M16.24 7.76l2.83-2.83"/></svg>
          </span>
        </button>

      </div>
    </div>
  </main>



<script src="js/admin_login.js"></script>

</body>
</html>