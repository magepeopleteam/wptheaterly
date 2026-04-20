<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<style>
/* ── Reset & Base ── */
* { box-sizing: border-box; margin: 0; padding: 0; }
body {
  font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
  font-size: 15px;
  line-height: 1.7;
  color: #2d3142;
  background: #fff;
  max-width: 860px;
  margin: 0 auto;
  padding: 0 0 40px;
}

/* ── Intro Banner ── */
.intro-banner {
  background: linear-gradient(135deg, #0f1f3d 0%, #1a3a6b 60%, #1e4d8c 100%);
  color: #fff;
  padding: 48px 40px;
  border-radius: 8px;
  margin-bottom: 36px;
  position: relative;
  overflow: hidden;
}
.intro-banner::after {
  content: '🎬';
  position: absolute;
  right: 30px; top: 20px;
  font-size: 90px;
  opacity: 0.08;
  line-height: 1;
}
.intro-banner h1 {
  font-size: 26px;
  font-weight: 700;
  margin-bottom: 10px;
  letter-spacing: -0.3px;
}
.intro-banner p {
  font-size: 15px;
  color: rgba(255,255,255,0.78);
  max-width: 580px;
  line-height: 1.7;
}
.badge-row {
  display: flex;
  flex-wrap: wrap;
  gap: 8px;
  margin-top: 20px;
}
.badge {
  background: rgba(255,255,255,0.12);
  border: 1px solid rgba(255,255,255,0.2);
  color: #fff;
  font-size: 12px;
  font-weight: 600;
  padding: 4px 12px;
  border-radius: 100px;
  letter-spacing: 0.02em;
}

/* ── Section ── */
.section { margin-bottom: 36px; }
.section-title {
  font-size: 18px;
  font-weight: 700;
  color: #0f1f3d;
  padding-bottom: 10px;
  border-bottom: 3px solid #1a6ef5;
  margin-bottom: 20px;
  display: flex;
  align-items: center;
  gap: 8px;
}

/* ── Feature Grid ── */
.feature-grid {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 12px;
}
.feature-item {
  display: flex;
  align-items: flex-start;
  gap: 12px;
  background: #f7f9fc;
  border: 1px solid #e8edf5;
  border-radius: 6px;
  padding: 14px 16px;
}
.feature-icon {
  font-size: 20px;
  flex-shrink: 0;
  margin-top: 1px;
}
.feature-text strong {
  display: block;
  font-size: 13.5px;
  font-weight: 700;
  color: #0f1f3d;
  margin-bottom: 3px;
}
.feature-text span {
  font-size: 13px;
  color: #5a6280;
  line-height: 1.5;
}

/* ── Steps ── */
.steps {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 12px;
}
.step {
  text-align: center;
  padding: 20px 12px;
  border: 1px solid #e8edf5;
  border-radius: 6px;
  background: #f7f9fc;
}
.step-num {
  width: 36px; height: 36px;
  background: #1a6ef5;
  color: #fff;
  border-radius: 50%;
  font-weight: 700;
  font-size: 15px;
  display: flex;
  align-items: center;
  justify-content: center;
  margin: 0 auto 10px;
}
.step strong { display: block; font-size: 13px; font-weight: 700; color: #0f1f3d; margin-bottom: 4px; }
.step span { font-size: 12.5px; color: #5a6280; line-height: 1.5; }

/* ── Shortcodes ── */
.sc-block {
  background: #f0f5ff;
  border-left: 4px solid #1a6ef5;
  border-radius: 0 6px 6px 0;
  padding: 16px 20px;
  margin-bottom: 14px;
}
.sc-code {
  font-family: 'Courier New', Courier, monospace;
  font-size: 13px;
  font-weight: 700;
  color: #1a3a8c;
  background: #dce8ff;
  padding: 4px 10px;
  border-radius: 4px;
  display: inline-block;
  margin-bottom: 8px;
  word-break: break-all;
}
.sc-block p { font-size: 13.5px; color: #3a4460; line-height: 1.6; }
.sc-block ul { margin: 8px 0 0 18px; }
.sc-block ul li { font-size: 13px; color: #5a6280; margin-bottom: 3px; }

/* ── Compat ── */
.compat-list {
  display: flex;
  flex-wrap: wrap;
  gap: 8px;
}
.compat-tag {
  display: flex;
  align-items: center;
  gap: 6px;
  background: #f0f5ff;
  border: 1px solid #c8d8f8;
  border-radius: 100px;
  padding: 5px 14px;
  font-size: 13px;
  font-weight: 600;
  color: #1a3a8c;
}
.compat-dot { width: 7px; height: 7px; background: #22c55e; border-radius: 50%; flex-shrink: 0; }

/* ── Note box ── */
.note-box {
  background: #fffbeb;
  border: 1px solid #fcd34d;
  border-radius: 6px;
  padding: 14px 18px;
  font-size: 13.5px;
  color: #7a5c00;
  line-height: 1.6;
}
.note-box strong { color: #5a3e00; }

/* ── Changelog ── */
.changelog { list-style: none; }
.changelog li {
  padding: 8px 0;
  border-bottom: 1px solid #eef0f6;
  font-size: 13.5px;
  color: #3a4460;
  display: flex;
  gap: 10px;
  align-items: flex-start;
}
.changelog li:last-child { border-bottom: none; }
.cl-ver {
  font-size: 11px;
  font-weight: 700;
  background: #1a6ef5;
  color: #fff;
  padding: 2px 8px;
  border-radius: 3px;
  flex-shrink: 0;
  margin-top: 2px;
}

/* ── Support banner ── */
.support-banner {
  background: linear-gradient(135deg, #0f1f3d, #1e4d8c);
  color: #fff;
  border-radius: 8px;
  padding: 28px 32px;
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 20px;
  flex-wrap: wrap;
}
.support-banner p { font-size: 14px; color: rgba(255,255,255,0.75); margin-top: 4px; }
.support-banner h3 { font-size: 17px; font-weight: 700; }
.support-btn {
  background: #1a6ef5;
  color: #fff;
  font-size: 13px;
  font-weight: 700;
  padding: 10px 24px;
  border-radius: 5px;
  text-decoration: none;
  white-space: nowrap;
}

/* ── responsive ── */
@media (max-width: 580px) {
  .feature-grid { grid-template-columns: 1fr; }
  .steps { grid-template-columns: repeat(2, 1fr); }
  .intro-banner { padding: 32px 24px; }
}
</style>
</head>
<body>

<!-- ── INTRO BANNER ── -->
<div class="intro-banner">
  <h1>Online Movie &amp; Theater Reservation System</h1>
  <p>A complete WordPress plugin for cinema and event ticket booking — with interactive seat selection, showtime scheduling, QR e-tickets, and secure WooCommerce payments. No coding required.</p>
  <div class="badge-row">
    <span class="badge">WordPress Plugin</span>
    <span class="badge">WooCommerce Ready</span>
    <span class="badge">QR Ticketing</span>
    <span class="badge">Multi-Language</span>
    <span class="badge">Mobile Friendly</span>
  </div>
</div>

<!-- ── FEATURES ── -->
<div class="section">
  <div class="section-title">✨ Features</div>
  <div class="feature-grid">

    <div class="feature-item">
      <span class="feature-icon">🎬</span>
      <div class="feature-text">
        <strong>Movie &amp; Showtime Management</strong>
        <span>Add movies, set schedules, and manage recurring showtimes across multiple halls.</span>
      </div>
    </div>

    <div class="feature-item">
      <span class="feature-icon">🪑</span>
      <div class="feature-text">
        <strong>Interactive Seat Selection</strong>
        <span>Visual seat map with real-time availability — customers pick their exact seat.</span>
      </div>
    </div>

    <div class="feature-item">
      <span class="feature-icon">🎟️</span>
      <div class="feature-text">
        <strong>Multiple Ticket Types</strong>
        <span>Support for Regular, Premium, VIP, and fully custom ticket categories.</span>
      </div>
    </div>

    <div class="feature-item">
      <span class="feature-icon">💰</span>
      <div class="feature-text">
        <strong>Flexible Pricing Rules</strong>
        <span>Seat-based, time-based, or special show pricing with discount support.</span>
      </div>
    </div>

    <div class="feature-item">
      <span class="feature-icon">🏢</span>
      <div class="feature-text">
        <strong>Multi-Screen Theater Management</strong>
        <span>Manage unlimited halls, screens, and venues from one admin panel.</span>
      </div>
    </div>

    <div class="feature-item">
      <span class="feature-icon">💳</span>
      <div class="feature-text">
        <strong>WooCommerce Payments</strong>
        <span>Integrates with any payment gateway you already use — Stripe, PayPal, and more.</span>
      </div>
    </div>

    <div class="feature-item">
      <span class="feature-icon">🎫</span>
      <div class="feature-text">
        <strong>QR Code &amp; Barcode Tickets</strong>
        <span>Auto-generate scannable tickets with built-in door validation tools.</span>
      </div>
    </div>

    <div class="feature-item">
      <span class="feature-icon">📩</span>
      <div class="feature-text">
        <strong>Automated Email &amp; E-Tickets</strong>
        <span>Instant confirmation emails with e-ticket attachments sent on booking.</span>
      </div>
    </div>

    <div class="feature-item">
      <span class="feature-icon">📊</span>
      <div class="feature-text">
        <strong>Analytics &amp; Sales Reports</strong>
        <span>Visual dashboard with booking trends, revenue, and occupancy data.</span>
      </div>
    </div>

    <div class="feature-item">
      <span class="feature-icon">📅</span>
      <div class="feature-text">
        <strong>Advanced Scheduling</strong>
        <span>Date, time, and recurring show scheduling with seasonal event support.</span>
      </div>
    </div>

    <div class="feature-item">
      <span class="feature-icon">📱</span>
      <div class="feature-text">
        <strong>Mobile-Responsive Interface</strong>
        <span>Seamless booking experience on phones, tablets, and desktops.</span>
      </div>
    </div>

    <div class="feature-item">
      <span class="feature-icon">🌐</span>
      <div class="feature-text">
        <strong>Multi-Language &amp; WPML Ready</strong>
        <span>Fully translation-ready to reach international audiences.</span>
      </div>
    </div>

  </div>
</div>

<!-- ── HOW IT WORKS ── -->
<div class="section">
  <div class="section-title">🚀 How It Works</div>
  <div class="steps">
    <div class="step">
      <div class="step-num">1</div>
      <strong>Install Plugin</strong>
      <span>Upload and activate like any standard WordPress plugin.</span>
    </div>
    <div class="step">
      <div class="step-num">2</div>
      <strong>Add Movies &amp; Theaters</strong>
      <span>Create hall layouts, add movies, and schedule showtimes.</span>
    </div>
    <div class="step">
      <div class="step-num">3</div>
      <strong>Connect WooCommerce</strong>
      <span>Link your store to enable secure online payments.</span>
    </div>
    <div class="step">
      <div class="step-num">4</div>
      <strong>Go Live</strong>
      <span>Drop a shortcode on any page — customers can start booking instantly.</span>
    </div>
  </div>
</div>

<!-- ── SHORTCODES ── -->
<div class="section">
  <div class="section-title">⚙️ Shortcode Guide</div>

  <div class="sc-block">
    <div class="sc-code">[wtbm_ticket_booking]</div>
    <p>The full booking widget. Users pick a date, select a movie, choose a showtime, pick their seats, and complete payment — all in one seamless flow.</p>
  </div>

  <div class="sc-block">
    <div class="sc-code">[wtbm_display_running_movie view="grid" list_grid_btn="yes" column="3"]</div>
    <p>Displays currently running movies in a modern grid or list layout. Supports view toggle and configurable columns.</p>
    <ul>
      <li><strong>view</strong> — <code>grid</code> or <code>list</code> (default: grid)</li>
      <li><strong>list_grid_btn</strong> — show toggle button: <code>yes</code> / <code>no</code></li>
      <li><strong>column</strong> — number of columns in grid layout (default: 3)</li>
    </ul>
  </div>

  <div class="sc-block">
    <div class="sc-code">[wtbm_display_upcoming_movie]</div>
    <p>Shows your upcoming movie list. Great for building anticipation before ticket sales open.</p>
  </div>

  <div class="sc-block">
    <div class="sc-code">[wtbm_single_movie_booking movie_id=571 show_header='yes']</div>
    <p>Embeds the full booking interface for one specific movie. Ideal for dedicated landing or promotional pages.</p>
    <ul>
      <li><strong>movie_id</strong> — WordPress post ID of the movie</li>
      <li><strong>show_header</strong> — show or hide movie header: <code>yes</code> / <code>no</code></li>
    </ul>
  </div>
</div>

<!-- ── COMPATIBILITY ── -->
<div class="section">
  <div class="section-title">✅ Compatibility</div>
  <div class="compat-list">
    <span class="compat-tag"><span class="compat-dot"></span>WordPress 5.0+</span>
    <span class="compat-tag"><span class="compat-dot"></span>WooCommerce</span>
    <span class="compat-tag"><span class="compat-dot"></span>WPML</span>
    <span class="compat-tag"><span class="compat-dot"></span>Elementor</span>
    <span class="compat-tag"><span class="compat-dot"></span>Gutenberg</span>
    <span class="compat-tag"><span class="compat-dot"></span>Classic Editor</span>
    <span class="compat-tag"><span class="compat-dot"></span>Stripe</span>
    <span class="compat-tag"><span class="compat-dot"></span>PayPal</span>
    <span class="compat-tag"><span class="compat-dot"></span>PHP 7.4+</span>
    <span class="compat-tag"><span class="compat-dot"></span>MySQL 5.6+</span>
    <span class="compat-tag"><span class="compat-dot"></span>Any WordPress Theme</span>
  </div>
</div>

<!-- ── NOTE ── -->
<div class="section">
  <div class="note-box">
    <strong>⚠️ Requirements:</strong> This plugin requires <strong>WordPress 5.0+</strong> and <strong>WooCommerce</strong> to be installed and active for payment processing. PHP 7.4 or higher is recommended for best performance.
  </div>
</div>

<!-- ── CHANGELOG ── -->
<div class="section">
  <div class="section-title">📋 Changelog</div>
  <ul class="changelog">
    <li><span class="cl-ver">v1.0</span>Initial release — movie management, seat selection, WooCommerce integration, QR tickets, email notifications, analytics dashboard.</li>
  </ul>
</div>

<!-- ── SUPPORT ── -->
<div class="support-banner">
  <div>
    <h3>💬 Need Help?</h3>
    <p>We provide dedicated support via the Codecanyon comments section. Response within 24–48 hours on business days.</p>
  </div>
  <a class="support-btn" href="#">Contact Support</a>
</div>

</body>
</html>
