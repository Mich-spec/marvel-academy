<?php
require_once __DIR__ . '/config.php';


// ── Handle form submission ──────────────────────────────────────────
$response = ['status' => '', 'message' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'preregister') {

    $email     = trim(filter_input(INPUT_POST, 'email',     FILTER_SANITIZE_EMAIL));
    $name      = trim(filter_input(INPUT_POST, 'name',      FILTER_SANITIZE_SPECIAL_CHARS));
    $interest  = trim(filter_input(INPUT_POST, 'interest',  FILTER_SANITIZE_SPECIAL_CHARS));
    $phone     = trim(filter_input(INPUT_POST, 'phone',     FILTER_SANITIZE_SPECIAL_CHARS));

    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $response = ['status' => 'error', 'message' => 'Please enter a valid email address.'];
    } elseif (empty($name)) {
        $response = ['status' => 'error', 'message' => 'Please enter your full name.'];
    } else {
        $file = __DIR__ . '/data/preregistrations.json';
        $records = [];

        if (file_exists($file)) {
            $records = json_decode(file_get_contents($file), true) ?? [];
        }

        // Check for duplicate email
        $duplicate = false;
        foreach ($records as $r) {
            if (strtolower($r['email']) === strtolower($email)) {
                $duplicate = true;
                break;
            }
        }

        if ($duplicate) {
            $response = ['status' => 'info', 'message' => "You're already on the list! We'll notify you at <strong>" . htmlspecialchars($email) . "</strong>."];
        } else {
            $records[] = [
                'id'        => count($records) + 1,
                'name'      => $name,
                'email'     => $email,
                'phone'     => $phone,
                'interest'  => $interest,
                'ip'        => $_SERVER['REMOTE_ADDR'] ?? '',
                'timestamp' => date('Y-m-d H:i:s'),
            ];

            file_put_contents($file, json_encode($records, JSON_PRETTY_PRINT), LOCK_EX);
            $response = ['status' => 'success', 'message' => "🎉 You're on the list, <strong>" . htmlspecialchars($name) . "</strong>! We'll email you at <strong>" . htmlspecialchars($email) . "</strong> when we launch."];
        }
    }

    // Return JSON for AJAX requests
    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
        header('Content-Type: application/json');
        echo json_encode($response);
        exit;
    }
}

// ── Stats ─────────────────────────────────────────────────────────
$prereg_file  = __DIR__ . '/data/preregistrations.json';
$prereg_count = 0;
if (file_exists($prereg_file)) {
    $data = json_decode(file_get_contents($prereg_file), true);
    $prereg_count = is_array($data) ? count($data) : 0;
}

$courses = get_courses();

// Launch date: 60 days from now (update to real date)
$launch_date = date('Y-m-d', strtotime('+60 days')) . 'T09:00:00';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Coming Soon — Marvel Academy | Pre-Register Now</title>
  <meta name="description" content="Marvel Academy is launching soon. Pre-register now to get early-bird pricing, exclusive bonuses, and priority access to our certified IT courses." />
  <meta property="og:title" content="Marvel Academy is Coming — Pre-Register for Early Access" />
  <meta property="og:description" content="Be first in line. Get early-bird pricing and exclusive bonuses when we launch." />
  <link rel="stylesheet" href="/assets/css/main.css" />
  <link rel="stylesheet" href="/assets/css/pre-register.css" />
  <link rel="preconnect" href="https://fonts.googleapis.com" />
</head>
<body>

<!-- Particle canvas background -->
<canvas id="particles-canvas"></canvas>

<!-- ════════════════════════════════════════
     NAV
════════════════════════════════════════ -->
<nav class="pr-nav">
  <div class="pr-nav-inner">
    <a href="index.php" class="pr-nav-logo">
      <div class="pr-nav-logo-icon">MA</div>
      <span class="pr-nav-logo-text">Marvel Academy</span>
    </a>
    <a href="index.php" class="pr-nav-back">
      ← Back to main site
    </a>
  </div>
</nav>


<!-- ════════════════════════════════════════
     HERO SPLIT
════════════════════════════════════════ -->
<div class="pr-wrap">

  <!-- LEFT: Copy -->
  <div class="pr-left">
    <div class="pr-eyebrow">
      <div class="live-dot"></div>
      Launching Soon
    </div>

    <h1>
      The Future of<br>
      <span class="grad">African Tech</span><br>
      Talent Starts<br>
      <span class="accent-u">Here.</span>
    </h1>

    <p class="sub">
      Marvel Academy is preparing to launch Africa's most practical, globally recognised IT certification platform. Pre-register today to lock in your early-bird pricing and exclusive bonuses before we open to the public.
    </p>

    <!-- Perks -->
    <div class="pr-perks">
      <div class="pr-perk">
        <div class="pr-perk-icon">🎟️</div>
        <div class="pr-perk-text">
          <strong>Priority Enrolment Access</strong>
          <span>Be first in line — skip the waitlist when we officially open.</span>
        </div>
      </div>
      <div class="pr-perk">
        <div class="pr-perk-icon">💰</div>
        <div class="pr-perk-text">
          <strong>Early-Bird Pricing (Up to 30% Off)</strong>
          <span>Locked-in rate, only for pre-registered members. Never expires.</span>
        </div>
      </div>
      <div class="pr-perk">
        <div class="pr-perk-icon">📦</div>
        <div class="pr-perk-text">
          <strong>Exclusive Starter Pack</strong>
          <span>Free resources, career roadmaps and prep materials sent to your inbox.</span>
        </div>
      </div>
      <div class="pr-perk">
        <div class="pr-perk-icon">🔔</div>
        <div class="pr-perk-text">
          <strong>Launch Day Notification</strong>
          <span>Be the first to know — get notified the moment doors open.</span>
        </div>
      </div>
    </div>

    <!-- Social proof -->
    <div class="pr-social-proof">
      <div class="pr-avatars">
        <?php for($i = 1; $i <= 5; $i++): ?>
        <img src="https://i.pravatar.cc/64?img=<?= $i * 9 ?>"
             alt="Pre-registrant" class="pr-avatar" loading="lazy" />
        <?php endfor; ?>
      </div>
      <div class="pr-proof-text">
        <strong><?= number_format(max(47 + $prereg_count, 47)) ?>+ people</strong> have already pre-registered
      </div>
    </div>
  </div>


  <!-- RIGHT: Form -->
  <div class="pr-right">
    <div class="pr-form-card">

      <!-- Success state (hidden by default) -->
      <div class="pr-success" id="successState">
        <div class="pr-success-icon">🎉</div>
        <h3>You're on the list!</h3>
        <p id="successMsg">Welcome aboard. We'll email you with everything you need when we launch. Keep an eye on your inbox!</p>
        <div class="position-badge" id="positionBadge">🏅 Early Member #<?= number_format(48 + $prereg_count) ?></div>
        <div style="margin-top:24px;display:flex;gap:10px;flex-direction:column;">
          <p style="font-size:13px;color:rgba(255,255,255,0.4);margin-bottom:4px;">Share with friends and earn referral bonuses</p>
          <div style="display:flex;gap:10px;justify-content:center;flex-wrap:wrap;">
            <a href="https://twitter.com/intent/tweet?text=I just pre-registered for Marvel Academy — Africa's next big tech training platform! Join me 👇&url=<?= urlencode('https://marvelacademy.ng/pre-register.php') ?>"
               target="_blank"
               style="display:flex;align-items:center;gap:7px;background:rgba(255,255,255,0.07);border:1px solid rgba(255,255,255,0.1);color:rgba(255,255,255,0.7);text-decoration:none;font-size:13px;padding:9px 16px;border-radius:9px;transition:all 0.3s;">
              𝕏 Share on X
            </a>
            <a href="https://wa.me/?text=I just pre-registered for Marvel Academy — Africa's next big tech training platform! Join here: <?= urlencode('https://marvelacademy.ng/pre-register.php') ?>"
               target="_blank"
               style="display:flex;align-items:center;gap:7px;background:rgba(37,211,102,0.1);border:1px solid rgba(37,211,102,0.2);color:#25D366;text-decoration:none;font-size:13px;padding:9px 16px;border-radius:9px;transition:all 0.3s;">
              WhatsApp
            </a>
          </div>
        </div>
      </div>

      <!-- Form state -->
      <div id="formState">
        <h2>Secure Your Spot 🔒</h2>
        <p class="form-tagline">Join <?= number_format(47 + $prereg_count) ?>+ early members. No payment needed.</p>

        <form id="preRegForm" novalidate>
          <input type="hidden" name="action" value="preregister" />

          <div class="pr-form-group">
            <label for="pr_name">Full Name *</label>
            <div class="pr-input-icon">
              <span class="icon-left">👤</span>
              <input type="text" id="pr_name" name="name" class="pr-input"
                     placeholder="e.g. Adaeze Okonkwo" autocomplete="name" required />
            </div>
          </div>

          <div class="pr-form-group">
            <label for="pr_email">Email Address *</label>
            <div class="pr-input-icon">
              <span class="icon-left">📧</span>
              <input type="email" id="pr_email" name="email" class="pr-input"
                     placeholder="you@example.com" autocomplete="email" required />
            </div>
          </div>

          <div class="pr-form-group">
            <label for="pr_phone">Phone / WhatsApp <span style="opacity:0.4">(Optional)</span></label>
            <div class="pr-input-icon">
              <span class="icon-left">📱</span>
              <input type="tel" id="pr_phone" name="phone" class="pr-input"
                     placeholder="+234 800 000 0000" autocomplete="tel" />
            </div>
          </div>

          <div class="pr-form-group">
            <label for="pr_interest">Course of Interest</label>
            <select id="pr_interest" name="interest" class="pr-input">
              <option value="">— Select a course —</option>
              <?php foreach($courses as $c): ?>
              <option value="<?= htmlspecialchars($c['slug']) ?>"><?= htmlspecialchars($c['title']) ?></option>
              <?php endforeach; ?>
              <option value="multiple">Multiple Courses</option>
              <option value="undecided">I'm not sure yet</option>
            </select>
          </div>

          <div class="form-feedback-msg" id="formFeedback"></div>

          <button type="submit" class="pr-submit" id="submitBtn">
            <span id="btnText">Reserve My Spot</span>
            <span class="btn-arrow" id="btnArrow">→</span>
            <div class="spinner" id="btnSpinner"></div>
          </button>
        </form>

        <p class="pr-privacy">
          🔒 No credit card. No spam. By registering you agree to our
          <a href="#">Privacy Policy</a>. You can unsubscribe at any time.
        </p>
      </div><!-- /formState -->

    </div>
  </div>

</div><!-- /pr-wrap -->


<!-- ════════════════════════════════════════
     COUNTDOWN TIMER
════════════════════════════════════════ -->
<section class="countdown-section">
  <div class="countdown-inner">
    <div class="countdown-label">🚀 &nbsp;Official Launch In</div>
    <div class="countdown-grid">
      <div class="countdown-unit">
        <div class="countdown-number"><span id="cd-days">00</span></div>
        <div class="countdown-caption">Days</div>
      </div>
      <div class="countdown-sep">:</div>
      <div class="countdown-unit">
        <div class="countdown-number"><span id="cd-hours">00</span></div>
        <div class="countdown-caption">Hours</div>
      </div>
      <div class="countdown-sep">:</div>
      <div class="countdown-unit">
        <div class="countdown-number"><span id="cd-mins">00</span></div>
        <div class="countdown-caption">Minutes</div>
      </div>
      <div class="countdown-sep">:</div>
      <div class="countdown-unit">
        <div class="countdown-number"><span id="cd-secs">00</span></div>
        <div class="countdown-caption">Seconds</div>
      </div>
    </div>
    <p style="margin-top:22px;font-size:13px;color:rgba(255,255,255,0.3);">
      Target launch date: <strong style="color:rgba(255,255,255,0.55);"><?= date('F j, Y', strtotime('+60 days')) ?></strong>
    </p>
  </div>
</section>


<!-- ════════════════════════════════════════
     WHAT YOU GET (BENEFITS)
════════════════════════════════════════ -->
<section class="benefits-section">
  <div class="benefits-inner">
    <div class="benefits-header">
      <div class="section-tag" style="background:rgba(26,122,74,0.18);color:#5AE395;">🎁 Early-Bird Benefits</div>
      <h2>What Pre-Registered Members Get</h2>
      <p>Exclusive perks reserved only for people who sign up before we launch.</p>
    </div>

    <div class="benefits-grid">
      <?php
      $benefits = [
        ['icon'=>'💸', 'title'=>'Up to 30% Off All Courses', 'desc'=>'Your early-bird price is locked in forever. You pay less than anyone who joins after launch — no matter when you start.', 'badge'=>'Save ₦51k+'],
        ['icon'=>'🎟️', 'title'=>'Skip the Launch Queue', 'desc'=>'Course seats are limited. Pre-registrants get first pick before enrolment opens to the general public.', 'badge'=>'Priority Access'],
        ['icon'=>'📚', 'title'=>'Free Starter Resource Pack', 'desc'=>'Receive a curated bundle of career roadmaps, study guides and industry reading lists — delivered before classes begin.', 'badge'=>'Free Bonus'],
        ['icon'=>'👨‍🏫', 'title'=>'One Free Mentor Session', 'desc'=>'Get a 30-minute 1-on-1 career consultation with one of our expert tutors before the programme starts.', 'badge'=>'₦15,000 Value'],
        ['icon'=>'🏅', 'title'=>'Founding Member Badge', 'desc'=>'Wear your founding membership on your certificate and LinkedIn profile — a permanent mark of being an early believer.', 'badge'=>'Exclusive'],
        ['icon'=>'🔔', 'title'=>'Instant Launch Notification', 'desc'=>'The moment we go live, you\'ll be the first to know — via email and WhatsApp before any public announcement.', 'badge'=>'First to Know'],
      ];
      foreach($benefits as $b):
      ?>
      <div class="benefit-card">
        <span class="benefit-icon"><?= $b['icon'] ?></span>
        <h4><?= htmlspecialchars($b['title']) ?></h4>
        <p><?= htmlspecialchars($b['desc']) ?></p>
        <span class="benefit-badge"><?= htmlspecialchars($b['badge']) ?></span>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>


<!-- ════════════════════════════════════════
     COURSES PREVIEW
════════════════════════════════════════ -->
<section class="courses-preview-section">
  <div class="courses-preview-inner">
    <div class="courses-preview-header">
      <div>
        <h2>Courses Available at Launch</h2>
        <p>Fully remote · Globally recognised certification · Expert-led</p>
      </div>
      <span style="font-size:13px;color:rgba(255,255,255,0.4);"><?= count($courses) ?> courses planned</span>
    </div>

    <div class="courses-preview-grid">
      <?php foreach($courses as $c): ?>
      <div class="cp-card">
        <div class="cp-icon" style="background:<?= $c['icon'] ?>20;border:1px solid <?= $c['icon'] ?>40;">
          <?= $c['icon'] ?>
        </div>
        <h4><?= htmlspecialchars($c['title']) ?></h4>
        <p class="cp-sub"><?= htmlspecialchars($c['description']) ?></p>
        <div class="cp-footer">
          <div>
            <div class="cp-price-old"><?= format_price($c['original_price']) ?></div>
            <div class="cp-price-new"><?= format_price($c['price']) ?></div>
          </div>
          <span class="cp-duration">⏱ <?= htmlspecialchars($c['duration']) ?></span>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>


<!-- ════════════════════════════════════════
     HOW IT WORKS
════════════════════════════════════════ -->
<section class="howitworks-section">
  <div class="howitworks-inner">
    <div class="hiw-header">
      <div class="section-tag" style="background:rgba(26,122,74,0.18);color:#5AE395;display:table;margin:0 auto 16px;">🗺️ How It Works</div>
      <h2>Your Journey From Here to Hired</h2>
      <p>Four simple steps to transform your career with Marvel Academy.</p>
    </div>
    <div class="hiw-steps">
      <?php
      $steps = [
        ['num'=>'1','emoji'=>'📝','title'=>'Pre-Register Today','desc'=>'Drop your name and email. Zero commitment — just reserve your early-bird spot.'],
        ['num'=>'2','emoji'=>'📧','title'=>'Get Your Welcome Pack','desc'=>'Receive your free starter resources and a personal note from the Marvel Academy team.'],
        ['num'=>'3','emoji'=>'🚀','title'=>'Enrol When We Launch','desc'=>'On launch day, use your exclusive link to enrol at your locked-in early-bird price.'],
        ['num'=>'4','emoji'=>'🎓','title'=>'Learn, Build & Get Certified','desc'=>'Complete your chosen course, build real projects and earn your certificate.'],
      ];
      foreach($steps as $s):
      ?>
      <div class="hiw-step">
        <div class="hiw-step-num"><?= $s['emoji'] ?></div>
        <h4><?= htmlspecialchars($s['title']) ?></h4>
        <p><?= htmlspecialchars($s['desc']) ?></p>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>


<!-- ════════════════════════════════════════
     URGENCY / SPOTS REMAINING
════════════════════════════════════════ -->
<div class="urgency-banner" id="urgencyBanner">
  <div class="urgency-text">
    <h3>⚡ Early-Bird Spots Are Limited</h3>
    <p>We're capping early-bird enrolments to ensure quality support for every student. Once slots are filled, prices return to standard rates.</p>
  </div>
  <div class="urgency-spots">
    <div class="urgency-bar-wrap">
      <div class="urgency-bar-label">
        <span><?= min($prereg_count + 47, 150) ?> / 200 claimed</span>
        <span><?= max(200 - $prereg_count - 47, 0) ?> left</span>
      </div>
      <div class="urgency-bar-track">
        <div class="urgency-bar-fill" id="urgencyFill"
             data-pct="<?= min(round(($prereg_count + 47) / 200 * 100), 100) ?>"></div>
      </div>
    </div>
    <div class="urgency-pct"><?= min(round(($prereg_count + 47) / 200 * 100), 100) ?>%</div>
  </div>
</div>


<!-- ════════════════════════════════════════
     FOOTER MINI
════════════════════════════════════════ -->
<footer class="pr-footer">
  <!-- <div class="pr-footer-links">
    <a href="index.php">Home</a>
    <a href="courses.php">Courses</a>
    <a href="index.php#contact">Contact</a>
    <a href="#">Privacy Policy</a>
    <a href="#">Terms of Service</a>
  </div> -->
  <p>© <?= date('Y') ?> Marvel Academy. All rights reserved. &nbsp;·&nbsp; Built for African talent, recognised globally.</p>
</footer>


<!-- ════════════════════════════════════════
     JAVASCRIPT
════════════════════════════════════════ -->
<script>
// ── PARTICLES ──────────────────────────────────────────────────────
(function () {
  const canvas = document.getElementById('particles-canvas');
  const ctx = canvas.getContext('2d');
  let W, H, particles = [];

  function resize() {
    W = canvas.width  = window.innerWidth;
    H = canvas.height = window.innerHeight;
  }

  function makeParticle() {
    return {
      x: Math.random() * W,
      y: Math.random() * H,
      r: Math.random() * 1.8 + 0.4,
      dx: (Math.random() - 0.5) * 0.35,
      dy: (Math.random() - 0.5) * 0.35,
      opacity: Math.random() * 0.5 + 0.1,
    };
  }

  resize();
  for (let i = 0; i < 90; i++) particles.push(makeParticle());
  window.addEventListener('resize', resize);

  function draw() {
    ctx.clearRect(0, 0, W, H);
    particles.forEach(p => {
      ctx.beginPath();
      ctx.arc(p.x, p.y, p.r, 0, Math.PI * 2);
      ctx.fillStyle = `rgba(90,227,149,${p.opacity})`;
      ctx.fill();
      p.x += p.dx; p.y += p.dy;
      if (p.x < 0 || p.x > W) p.dx *= -1;
      if (p.y < 0 || p.y > H) p.dy *= -1;
    });
    requestAnimationFrame(draw);
  }
  draw();
})();


// ── COUNTDOWN ──────────────────────────────────────────────────────
(function () {
  const target = new Date('<?= $launch_date ?>').getTime();

  function pad(n) { return String(n).padStart(2, '0'); }

  function tick() {
    const now  = Date.now();
    const diff = target - now;

    if (diff <= 0) {
      ['days','hours','mins','secs'].forEach(id => {
        document.getElementById('cd-' + id).textContent = '00';
      });
      return;
    }

    const days  = Math.floor(diff / 86400000);
    const hours = Math.floor((diff % 86400000) / 3600000);
    const mins  = Math.floor((diff % 3600000)  / 60000);
    const secs  = Math.floor((diff % 60000)    / 1000);

    document.getElementById('cd-days').textContent  = pad(days);
    document.getElementById('cd-hours').textContent = pad(hours);
    document.getElementById('cd-mins').textContent  = pad(mins);
    document.getElementById('cd-secs').textContent  = pad(secs);
  }

  tick();
  setInterval(tick, 1000);
})();


// ── URGENCY BAR ANIMATION ──────────────────────────────────────────
window.addEventListener('load', function () {
  const fill = document.getElementById('urgencyFill');
  if (fill) {
    requestAnimationFrame(() => {
      fill.style.width = fill.dataset.pct + '%';
    });
  }
});


// ── PRE-REGISTRATION FORM ──────────────────────────────────────────
document.getElementById('preRegForm').addEventListener('submit', function (e) {
  e.preventDefault();

  const name     = document.getElementById('pr_name');
  const email    = document.getElementById('pr_email');
  const feedback = document.getElementById('formFeedback');
  const btnText  = document.getElementById('btnText');
  const btnArrow = document.getElementById('btnArrow');
  const spinner  = document.getElementById('btnSpinner');
  const submitBtn = document.getElementById('submitBtn');

  // Clear errors
  [name, email].forEach(el => el.classList.remove('error'));
  feedback.className = 'form-feedback-msg';
  feedback.style.display = 'none';

  // Validate
  const emailRe = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
  let valid = true;

  if (!name.value.trim()) {
    name.classList.add('error');
    valid = false;
  }
  if (!email.value.trim() || !emailRe.test(email.value.trim())) {
    email.classList.add('error');
    valid = false;
  }

  if (!valid) {
    feedback.className = 'form-feedback-msg error';
    feedback.textContent = '⚠️ Please fill in your name and a valid email address.';
    feedback.style.display = 'block';
    return;
  }

  // Loading state
  submitBtn.disabled = true;
  btnText.textContent = 'Reserving your spot…';
  btnArrow.style.display = 'none';
  spinner.style.display = 'inline-block';

  // AJAX submit
  const formData = new FormData(this);

  fetch(window.location.href, {
    method: 'POST',
    headers: { 'X-Requested-With': 'XMLHttpRequest' },
    body: formData
  })
  .then(r => r.json())
  .then(data => {
    spinner.style.display = 'none';
    btnArrow.style.display = 'inline';

    if (data.status === 'success') {
      // Show success panel
      document.getElementById('formState').style.display = 'none';
      const successEl = document.getElementById('successState');
      successEl.style.display = 'block';
      document.getElementById('successMsg').innerHTML = data.message;
    } else if (data.status === 'info') {
      // Already subscribed
      feedback.className = 'form-feedback-msg info';
      feedback.innerHTML = data.message;
      feedback.style.display = 'block';
      submitBtn.disabled = false;
      btnText.textContent = 'Reserve My Spot';
    } else {
      feedback.className = 'form-feedback-msg error';
      feedback.textContent = data.message;
      feedback.style.display = 'block';
      submitBtn.disabled = false;
      btnText.textContent = 'Reserve My Spot';
    }
  })
  .catch(() => {
    spinner.style.display = 'none';
    btnArrow.style.display = 'inline';
    feedback.className = 'form-feedback-msg error';
    feedback.textContent = '⚠️ Something went wrong. Please try again.';
    feedback.style.display = 'block';
    submitBtn.disabled = false;
    btnText.textContent = 'Reserve My Spot';
  });
});


// ── SCROLL FADE-IN (reuse main.css .fade-up) ──────────────────────
const obs = new IntersectionObserver(entries => {
  entries.forEach(e => { if (e.isIntersecting) { e.target.classList.add('visible'); obs.unobserve(e.target); } });
}, { threshold: 0.1 });
document.querySelectorAll('.benefit-card, .cp-card, .hiw-step').forEach(el => {
  el.classList.add('fade-up');
  obs.observe(el);
});
</script>
</body>
</html>
