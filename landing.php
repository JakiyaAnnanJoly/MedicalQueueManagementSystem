<?php

declare(strict_types=1);

require __DIR__ . '/auth.php';

if (is_logged_in()) {
    header('Location: index.php');
    exit;
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>MediQueue AI | Clinic Management</title>
  <link rel="icon" type="image/svg+xml" href="assets/favicon.svg" />
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Manrope:wght@500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="assets/landing.css" />
</head>
<body>
  <main class="landing-shell">
    <section class="layout-grid">
      <div class="left-column">
        <article class="panel hero-panel">
          <header class="hero-topbar">
            <div class="brand-row">
              <img class="brand-logo" src="assets/logo.svg" alt="MediQueue AI logo" />
              <strong>MediQueue AI</strong>
            </div>
            <nav class="hero-nav" aria-label="Landing navigation">
              <a href="#features">Feature</a>
              <a href="#insights">Insights</a>
              <a href="#cta">Get Started</a>
            </nav>
            <a class="btn dark" href="login.php">Start Free</a>
          </header>

          <div class="hero-content">
            <h1>Clinic management simplified for modern healthcare</h1>
            <p>Powerful software that helps medical teams manage appointments, queue operations, staff workflow, and branch-based scheduling.</p>
            <div class="hero-actions">
              <a class="btn dark" href="login.php">Start Free</a>
              <a class="btn light" href="login.php">Watch Demo</a>
            </div>
          </div>

          <div class="hero-illustration" aria-hidden="true">
            <svg viewBox="0 0 900 340" role="presentation" focusable="false">
              <rect x="12" y="12" width="876" height="316" rx="18" fill="#ffffff" stroke="#e6ecee"/>
              <rect x="28" y="28" width="170" height="284" rx="12" fill="#f6f9fa" stroke="#e8eef0"/>
              <rect x="44" y="52" width="138" height="22" rx="11" fill="#1f2b2f"/>
              <rect x="44" y="90" width="112" height="12" rx="6" fill="#d5dee1"/>
              <rect x="44" y="116" width="126" height="12" rx="6" fill="#dce5e8"/>
              <rect x="44" y="142" width="98" height="12" rx="6" fill="#e4ecef"/>

              <rect x="220" y="40" width="652" height="34" rx="10" fill="#f8fbfb" stroke="#e8eef0"/>
              <rect x="238" y="50" width="240" height="14" rx="7" fill="#e1eaed"/>

              <rect x="220" y="90" width="200" height="82" rx="12" fill="#fbfdfd" stroke="#e8eef0"/>
              <rect x="436" y="90" width="200" height="82" rx="12" fill="#fbfdfd" stroke="#e8eef0"/>
              <rect x="652" y="90" width="200" height="82" rx="12" fill="#fbfdfd" stroke="#e8eef0"/>
              <rect x="238" y="128" width="64" height="28" rx="7" fill="#2f9a8e"/>
              <rect x="454" y="132" width="72" height="24" rx="7" fill="#1f2b2f"/>
              <rect x="670" y="132" width="72" height="24" rx="7" fill="#e4ecef"/>

              <rect x="220" y="186" width="632" height="126" rx="12" fill="#f9fbfb" stroke="#e8eef0"/>
              <rect x="238" y="206" width="16" height="86" rx="6" fill="#8bcfbe"/>
              <rect x="264" y="226" width="16" height="66" rx="6" fill="#9ed9ca"/>
              <rect x="290" y="214" width="16" height="78" rx="6" fill="#76c4b3"/>
              <rect x="316" y="240" width="16" height="52" rx="6" fill="#b4e2d7"/>
              <path d="M360 274h470" stroke="#e4ecef" stroke-width="4"/>
            </svg>
          </div>
        </article>

        <article id="features" class="panel feature-panel">
          <p class="mini-label">Features</p>
          <h2>Powerful tools for seamless clinic management</h2>
          <p class="muted-copy">Designed for healthcare teams who need practical control over queue, booking, staff, and branch operations.</p>
          <div class="feature-split">
            <div class="feature-list">
              <button type="button" class="feature-item active">Smart appointment scheduling</button>
              <button type="button" class="feature-item">Intelligent billing and receipt flow</button>
              <button type="button" class="feature-item">Comprehensive reporting and audits</button>
            </div>
            <div class="feature-preview" aria-hidden="true">
              <div class="preview-line"></div>
              <div class="preview-line short"></div>
              <div class="preview-user"></div>
              <div class="preview-line"></div>
              <div class="preview-line short"></div>
              <div class="preview-user"></div>
              <div class="preview-line"></div>
            </div>
          </div>
        </article>
      </div>

      <aside class="right-column">
        <article class="panel side-panel">
          <p class="mini-label">Workflow</p>
          <h3>Designed for doctors and clinic owners</h3>
          <div class="mini-card-grid" aria-hidden="true">
            <div class="mini-card"><strong>Appointments</strong><span>Queue and slot control</span></div>
            <div class="mini-card"><strong>Financial</strong><span>Token and billing records</span></div>
            <div class="mini-card"><strong>Patient Records</strong><span>Visit and status history</span></div>
          </div>
        </article>

        <article id="insights" class="panel insight-panel">
          <p class="mini-label">Insights</p>
          <h3>Transform your clinic with data-driven insights</h3>
          <div class="stats-row">
            <div><strong>10k+</strong><span>Increase in retention</span></div>
            <div><strong>2.5x</strong><span>Faster billing cycles</span></div>
            <div><strong>35%</strong><span>Lower admin time</span></div>
          </div>
        </article>

        <article id="cta" class="panel cta-panel">
          <h3>Ready to modernize your practice?</h3>
          <p>Start with your role account and manage booking, queue, and doctor schedules from one place.</p>
          <a class="btn dark full" href="login.php">Go To Login</a>
        </article>
      </aside>
    </section>
  </main>
</body>
</html>
