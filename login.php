<?php

declare(strict_types=1);

require __DIR__ . '/db.php';
require __DIR__ . '/auth.php';
require __DIR__ . '/audit.php';

if (is_logged_in()) {
    header('Location: index.php');
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim((string) ($_POST['username'] ?? ''));
    $password = (string) ($_POST['password'] ?? '');

    if ($username === '' || $password === '') {
        $error = 'Username and password are required.';
    } else {
        $stmt = db()->prepare('SELECT id, username, password_hash, full_name, role, profile_image_path, is_active FROM users WHERE username = :u LIMIT 1');
        $stmt->execute([':u' => $username]);
        $user = $stmt->fetch();

        if ($user && (int) $user['is_active'] === 1 && password_verify($password, (string) $user['password_hash'])) {
            login_user($user);
            write_audit_log('login_success', 'user', (int) $user['id'], ['username' => $username]);
            header('Location: index.php');
            exit;
        }

        $error = 'Invalid credentials.';
    }
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Login | MediQueue AI</title>
  <link rel="icon" type="image/svg+xml" href="assets/favicon.svg" />
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Manrope:wght@600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="assets/login.css" />
</head>
<body>
  <main class="login-shell">
    <section class="login-showcase">
      <p class="showcase-tag">Clinic Operations Suite</p>
      <h2>Welcome back to MediQueue AI</h2>
      <p class="showcase-copy">Manage appointments, queue movement, and staff coordination in one secure dashboard.</p>

      <div class="showcase-grid" aria-hidden="true">
        <article class="showcase-card">
          <strong>Live Queue</strong>
          <span>Track waiting, checked-in, and in-consultation status.</span>
        </article>
        <article class="showcase-card">
          <strong>Smart Booking</strong>
          <span>Role-based booking with availability and branch rules.</span>
        </article>
        <article class="showcase-card">
          <strong>Doctor Flow</strong>
          <span>Monthly appointment calendar with day-level details.</span>
        </article>
      </div>
    </section>

    <section class="login-card">
      <div class="login-brand">
        <img class="login-brand-logo" src="assets/logo.svg" alt="MediQueue AI logo" />
        <h1>MediQueue AI</h1>
      </div>
      <p>Sign in with your role account.</p>

      <?php if ($error !== ''): ?>
        <div class="error"><?= htmlspecialchars($error, ENT_QUOTES) ?></div>
      <?php endif; ?>

      <form method="post">
        <label>Username<input name="username" type="text" required /></label>
        <label>Password
          <div class="password-wrap">
            <input id="loginPasswordInput" name="password" type="password" required />
            <button type="button" class="password-toggle-btn" aria-label="Toggle password visibility" title="Show or hide password" data-toggle-password="#loginPasswordInput"></button>
          </div>
        </label>
        <button type="submit">Login</button>
        <a class="alt-link-btn" href="landing.php">Go to Landing Page</a>
      </form>
    </section>
  </main>
  <script>
    document.addEventListener('click', function (event) {
      const target = event.target;
      if (!(target instanceof HTMLElement)) return;
      if (!target.classList.contains('password-toggle-btn')) return;
      const selector = target.getAttribute('data-toggle-password');
      if (!selector) return;
      const input = document.querySelector(selector);
      if (!(input instanceof HTMLInputElement)) return;
      const isPassword = input.type === 'password';
      input.type = isPassword ? 'text' : 'password';
      target.classList.toggle('is-visible', isPassword);
      target.setAttribute('aria-label', isPassword ? 'Hide password' : 'Show password');
    });
  </script>
</body>
</html>
