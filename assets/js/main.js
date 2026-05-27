/* =============================================
   MARVEL ACADEMY — MAIN JAVASCRIPT
   ============================================= */

// ─── SPLASH SCREEN ───────────────────────────
function initSplash() {
  const splash = document.getElementById('splash-screen');
  if (!splash) return;

  setTimeout(() => {
    splash.classList.add('slide-up');
    setTimeout(() => {
      splash.style.display = 'none';
    }, 950);
  }, 3000);
}

// ─── MOBILE MENU ─────────────────────────────
function initMobileMenu() {
  const hamburger = document.getElementById('hamburger');
  const mobileMenu = document.getElementById('mobile-menu');
  if (!hamburger || !mobileMenu) return;

  hamburger.addEventListener('click', () => {
    const isOpen = mobileMenu.classList.toggle('open');
    hamburger.setAttribute('aria-expanded', isOpen);
    hamburger.classList.toggle('active', isOpen);
  });

  // Close on link click
  mobileMenu.querySelectorAll('a').forEach(link => {
    link.addEventListener('click', () => {
      mobileMenu.classList.remove('open');
      hamburger.classList.remove('active');
    });
  });
}

// ─── ACCORDION / FAQ ──────────────────────────
function initAccordions() {
  document.querySelectorAll('.accordion-header').forEach(header => {
    header.addEventListener('click', () => {
      const item = header.closest('.accordion-item');
      const body = item.querySelector('.accordion-body');
      const inner = item.querySelector('.accordion-body-inner');
      const isOpen = item.classList.contains('open');

      // Close all
      document.querySelectorAll('.accordion-item.open').forEach(openItem => {
        openItem.classList.remove('open');
        openItem.querySelector('.accordion-body').style.maxHeight = '0';
      });

      if (!isOpen) {
        item.classList.add('open');
        body.style.maxHeight = inner.scrollHeight + 'px';
      }
    });
  });
}

// ─── TOAST NOTIFICATIONS ─────────────────────
function showToast(message, type = 'success', duration = 3500) {
  let toast = document.getElementById('global-toast');
  if (!toast) {
    toast = document.createElement('div');
    toast.id = 'global-toast';
    toast.className = 'toast';
    document.body.appendChild(toast);
  }

  const icons = {
    success: `<svg class="toast-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>`,
    error: `<svg class="toast-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>`,
    info: `<svg class="toast-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>`
  };

  toast.className = `toast ${type}`;
  toast.innerHTML = `${icons[type] || icons.info}<span>${message}</span>`;

  requestAnimationFrame(() => {
    toast.classList.add('show');
  });

  setTimeout(() => {
    toast.classList.remove('show');
  }, duration);
}

// ─── CONTACT FORM WITH 3-MINUTE LOCKOUT ──────
function initContactForm() {
  const form = document.getElementById('contact-form');
  if (!form) return;

  const LOCKOUT_MS = 3 * 60 * 1000;
  const STORAGE_KEY = 'ma_contact_lockout';
  const timerMsg = document.getElementById('contact-timer-msg');
  const submitBtn = document.getElementById('contact-submit');

  function getRemainingTime() {
    const ts = localStorage.getItem(STORAGE_KEY);
    if (!ts) return 0;
    const elapsed = Date.now() - parseInt(ts, 10);
    return Math.max(0, LOCKOUT_MS - elapsed);
  }

  function formatTime(ms) {
    const total = Math.ceil(ms / 1000);
    const m = Math.floor(total / 60);
    const s = total % 60;
    return `${m}:${s.toString().padStart(2, '0')}`;
  }

  function disableForm(remaining) {
    form.querySelectorAll('input, textarea, button').forEach(el => el.disabled = true);
    form.classList.add('disabled');
    if (timerMsg) timerMsg.classList.add('show');

    let interval = setInterval(() => {
      const rem = getRemainingTime();
      if (timerMsg) timerMsg.textContent = `Form is locked for security. Please wait ${formatTime(rem)} before sending again.`;
      if (rem <= 0) {
        clearInterval(interval);
        enableForm();
      }
    }, 1000);

    if (timerMsg) timerMsg.textContent = `Form is locked for security. Please wait ${formatTime(remaining)} before sending again.`;
  }

  function enableForm() {
    form.querySelectorAll('input, textarea, button').forEach(el => el.disabled = false);
    form.classList.remove('disabled');
    if (timerMsg) timerMsg.classList.remove('show');
    localStorage.removeItem(STORAGE_KEY);
  }

  // Check on page load
  const remaining = getRemainingTime();
  if (remaining > 0) disableForm(remaining);

  form.addEventListener('submit', async (e) => {
    e.preventDefault();

    // Validate
    let valid = true;
    form.querySelectorAll('[required]').forEach(field => {
      const err = field.parentElement.querySelector('.form-error');
      if (!field.value.trim()) {
        field.classList.add('error');
        if (err) err.classList.add('show');
        valid = false;
      } else {
        field.classList.remove('error');
        if (err) err.classList.remove('show');
      }
    });

    if (!valid) return;

    // Show loading
    if (submitBtn) {
      submitBtn.disabled = true;
      submitBtn.innerHTML = `<span class="spinner"></span> Sending...`;
    }

    // Simulate sending (replace with real AJAX in production)
    await new Promise(r => setTimeout(r, 1200));

    // Lock form
    localStorage.setItem(STORAGE_KEY, Date.now().toString());
    disableForm(LOCKOUT_MS);

    form.reset();
    showToast('Message sent! We\'ll get back to you shortly.', 'success');
  });
}

// ─── SUBSCRIBE FORM WITH EMAIL VALIDATION ────
function initSubscribeForm() {
  const form = document.getElementById('subscribe-form');
  if (!form) return;

  const emailInput = form.querySelector('#subscribe-email');
  const submitBtn = form.querySelector('#subscribe-submit');
  const emailError = form.querySelector('#subscribe-email-error');

  function isValidEmail(email) {
    return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
  }

  if (emailInput) {
    emailInput.addEventListener('input', () => {
      const val = emailInput.value.trim();
      if (val && !isValidEmail(val)) {
        emailInput.classList.add('error');
        if (emailError) emailError.classList.add('show');
      } else {
        emailInput.classList.remove('error');
        if (emailError) emailError.classList.remove('show');
      }
    });
  }

  form.addEventListener('submit', async (e) => {
    e.preventDefault();

    const email = emailInput ? emailInput.value.trim() : '';
    if (!email || !isValidEmail(email)) {
      if (emailInput) emailInput.classList.add('error');
      if (emailError) emailError.classList.add('show');
      return;
    }

    if (submitBtn) {
      submitBtn.disabled = true;
      submitBtn.textContent = 'Subscribing...';
    }

    // AJAX to subscribe.php
    try {
      const formData = new FormData();
      formData.append('email', email);

      const response = await fetch('subscribe.php', {
        method: 'POST',
        body: formData
      });

      const result = await response.json();

      if (result.success) {
        showToast(result.message || 'You\'re subscribed! 🎉', 'success');
        form.reset();
      } else {
        showToast(result.message || 'Something went wrong. Please try again.', 'error');
      }
    } catch {
      // Fallback for static demo
      showToast('You\'re subscribed! Stay tuned for updates. 🎉', 'success');
      form.reset();
    }

    if (submitBtn) {
      submitBtn.disabled = false;
      submitBtn.textContent = 'Subscribe';
    }
  });
}

// ─── SMOOTH SCROLL ───────────────────────────
function initSmoothScroll() {
  document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', e => {
      const target = document.querySelector(anchor.getAttribute('href'));
      if (target) {
        e.preventDefault();
        target.scrollIntoView({ behavior: 'smooth', block: 'start' });
      }
    });
  });
}

// ─── ANIMATE ON SCROLL ───────────────────────
function initScrollAnimations() {
  const observer = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        entry.target.classList.add('animate-in');
        observer.unobserve(entry.target);
      }
    });
  }, { threshold: 0.1, rootMargin: '0px 0px -40px 0px' });

  document.querySelectorAll('.anim').forEach(el => observer.observe(el));
}

// ─── COURSE FILTER (courses.php) ─────────────
function initCourseFilter() {
  const filterBtns = document.querySelectorAll('.filter-btn');
  const cards = document.querySelectorAll('.course-card-wrapper');
  if (!filterBtns.length) return;

  filterBtns.forEach(btn => {
    btn.addEventListener('click', () => {
      filterBtns.forEach(b => b.classList.remove('active'));
      btn.classList.add('active');

      const filter = btn.dataset.filter;
      cards.forEach(card => {
        const level = card.dataset.level || '';
        if (filter === 'all' || level === filter) {
          card.style.display = '';
        } else {
          card.style.display = 'none';
        }
      });
    });
  });
}

// ─── COUNTDOWN TIMER (hero) ──────────────────
function initCountdownTimer() {
  const timer = document.getElementById('offer-timer');
  if (!timer) return;

  let target = localStorage.getItem('ma_offer_deadline');
  if (!target) {
    target = Date.now() + 48 * 60 * 60 * 1000;
    localStorage.setItem('ma_offer_deadline', target);
  }

  function update() {
    const diff = parseInt(target) - Date.now();
    if (diff <= 0) {
      timer.textContent = 'Offer expired';
      return;
    }
    const h = Math.floor(diff / 3600000);
    const m = Math.floor((diff % 3600000) / 60000);
    const s = Math.floor((diff % 60000) / 1000);
    timer.textContent = `${h.toString().padStart(2,'0')}:${m.toString().padStart(2,'0')}:${s.toString().padStart(2,'0')}`;
  }

  update();
  setInterval(update, 1000);
}

// ─── INIT ALL ─────────────────────────────────
document.addEventListener('DOMContentLoaded', () => {
  initSplash();
  initMobileMenu();
  initAccordions();
  initContactForm();
  initSubscribeForm();
  initSmoothScroll();
  initScrollAnimations();
  initCourseFilter();
  initCountdownTimer();
});

// ─── Testimonial Slider ──────────────────
(function() {
  const track   = document.getElementById('testimonials-track');
  const dotsBox = document.getElementById('t-dots');
  if (!track || !dotsBox) return;

  const cards  = track.querySelectorAll('.testimonial-card');
  let current  = 0;
  let perView  = getPerView();
  let maxIndex = Math.max(0, cards.length - perView);
  let autoplay;

  function getPerView() {
    return window.innerWidth < 640 ? 1 : window.innerWidth < 1024 ? 2 : 3;
  }

  function buildDots() {
    dotsBox.innerHTML = '';
    const count = maxIndex + 1;
    for (let i = 0; i < count; i++) {
      const d = document.createElement('button');
      d.className = 't-dot' + (i === current ? ' active' : '');
      d.setAttribute('aria-label', `Go to testimonial ${i + 1}`);
      d.setAttribute('role', 'tab');
      d.setAttribute('aria-selected', i === current ? 'true' : 'false');
      d.addEventListener('click', () => go(i));
      dotsBox.appendChild(d);
    }
  }

  function go(index) {
    current = Math.max(0, Math.min(index, maxIndex));
    const cardW = cards[0].offsetWidth + 20;
    track.scrollTo({ left: current * cardW, behavior: 'smooth' });
    dotsBox.querySelectorAll('.t-dot').forEach((d, i) => {
      d.classList.toggle('active', i === current);
      d.setAttribute('aria-selected', i === current ? 'true' : 'false');
    });
  }

  document.getElementById('t-prev').addEventListener('click', () => go(current - 1));
  document.getElementById('t-next').addEventListener('click', () => go(current + 1));

  function startAuto() {
    autoplay = setInterval(() => go(current + 1 > maxIndex ? 0 : current + 1), 5000);
  }
  function stopAuto() { clearInterval(autoplay); }

  track.addEventListener('mouseenter', stopAuto);
  track.addEventListener('mouseleave', startAuto);

  window.addEventListener('resize', () => {
    perView  = getPerView();
    maxIndex = Math.max(0, cards.length - perView);
    current  = Math.min(current, maxIndex);
    buildDots();
    go(current);
  });

  buildDots();
  go(0);
  startAuto();
})();

// ─── Referral copy button ────────────────
function copyRef() {
  const text = 'https://marvelacademy.com/ref/YOURCODE';
  navigator.clipboard.writeText(text).then(() => {
    showToast('Referral link copied to clipboard!', 'success');
  }).catch(() => {
    showToast('Could not copy — please copy the link manually.', 'error');
  });
}

// ─── Keyboard accessibility for accordion ─
document.querySelectorAll('.accordion-header').forEach(header => {
  header.addEventListener('keydown', e => {
    if (e.key === 'Enter' || e.key === ' ') {
      e.preventDefault();
      header.click();
    }
  });
});
