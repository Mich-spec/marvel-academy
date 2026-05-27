<?php
/**
 * config.php — Marvel Academy Application Configuration
 *
 * ⚠️  PRODUCTION CHECKLIST:
 *   1. Set DB_PASS to a strong password
 *   2. Set APP_KEY to a random 64-char hex string
 *   3. Set APP_ENV to 'production'
 *   4. Set SMTP credentials for real email
 *   5. Move this file ABOVE the web root if possible
 *   6. Set APP_URL to your real domain
 *   7. Ensure SESSION_SECURE = true on HTTPS
 */

// ── Environment ─────────────────────────────────────────────
define('APP_ENV',    getenv('APP_ENV')    ?: 'development'); // 'development' | 'production'
define('APP_NAME',   'Marvel Academy');
define('APP_URL',    getenv('APP_URL')    ?: 'http://localhost/marvel academy');
define('APP_KEY',    getenv('APP_KEY')    ?: 'change-me-to-64-char-random-hex-string-in-production');

// ── Database ─────────────────────────────────────────────────
define('DB_HOST',    getenv('DB_HOST')    ?: 'localhost');
define('DB_PORT',    getenv('DB_PORT')    ?: '3306');
define('DB_NAME',    getenv('DB_NAME')    ?: 'marvel_academy');
define('DB_USER',    getenv('DB_USER')    ?: 'root');
define('DB_PASS',    getenv('DB_PASS')    ?: '');
define('DB_CHARSET', 'utf8mb4');

// ── Session Configuration ────────────────────────────────────
define('SESSION_NAME',     'ma_sess');
define('SESSION_LIFETIME', 7200);           // 2 hours (seconds)
define('SESSION_SECURE',   APP_ENV === 'production');
define('SESSION_HTTPONLY',  true);
define('SESSION_SAMESITE',  'Lax');

// ── Security ─────────────────────────────────────────────────
define('BCRYPT_COST',        12);           // Password hashing cost (10–14 recommended)
define('MAX_LOGIN_ATTEMPTS', 5);            // Before lockout
define('LOCKOUT_MINUTES',    15);           // Lockout duration
define('CSRF_TOKEN_NAME',    '_csrf_token');
define('REMEMBER_COOKIE',    'ma_remember');
define('REMEMBER_DAYS',      30);

// ── Paths ────────────────────────────────────────────────────
define('BASE_PATH',     __DIR__);
define('INCLUDES_PATH', BASE_PATH . '/includes');
define('DATA_PATH',     BASE_PATH . '/data');
define('ASSETS_URL',    APP_URL   . '/assets');

// ── SMTP / Email ─────────────────────────────────────────────
define('SMTP_HOST',     getenv('SMTP_HOST')     ?: 'smtp.mailtrap.io');
define('SMTP_PORT',     getenv('SMTP_PORT')     ?: 587);
define('SMTP_USER',     getenv('SMTP_USER')     ?: '');
define('SMTP_PASS',     getenv('SMTP_PASS')     ?: '');
define('SMTP_FROM',     getenv('SMTP_FROM')     ?: 'noreply@marvelacademy.com');
define('SMTP_FROM_NAME',getenv('SMTP_FROM_NAME')?: APP_NAME);

// ── Error Reporting ──────────────────────────────────────────
if (APP_ENV === 'production') {
  error_reporting(0);
  ini_set('display_errors', '0');
  ini_set('log_errors',     '1');
  ini_set('error_log',      BASE_PATH . '/logs/app_error.log');
} else {
  error_reporting(E_ALL);
  ini_set('display_errors', '1');
}

// ── Session bootstrap ────────────────────────────────────────
if (session_status() === PHP_SESSION_NONE) {
  session_name(SESSION_NAME);

  $cookieParams = [
    'lifetime' => SESSION_LIFETIME,
    'path'     => '/',
    'domain'   => '',
    'secure'   => SESSION_SECURE,
    'httponly' => SESSION_HTTPONLY,
    'samesite' => SESSION_SAMESITE,
  ];

  // PHP 7.3+: use session_set_cookie_params with array
  if (PHP_VERSION_ID >= 70300) {
    session_set_cookie_params($cookieParams);
  } else {
    session_set_cookie_params(
      $cookieParams['lifetime'],
      $cookieParams['path'],
      $cookieParams['domain'],
      $cookieParams['secure'],
      $cookieParams['httponly']
    );
  }

  session_start();
}

// ── Timezone ─────────────────────────────────────────────────
date_default_timezone_set('Africa/Lagos');


// course-related functions
function get_courses() {
    $json = file_get_contents(__DIR__ . '/data/courses.json');
    return json_decode($json, true);
}

function get_course_by_slug($slug) {
    $courses = get_courses();
    foreach ($courses as $course) {
        if ($course['slug'] === $slug) return $course;
    }
    return null;
}

function format_price($price) {
    return '₦' . number_format($price / 1000) . 'k';
}
// course-related functions end

// ── PDO Database Connection (singleton) ──────────────────────
function db(): PDO {
  static $pdo = null;

  if ($pdo === null) {
    $dsn = sprintf(
      'mysql:host=%s;port=%s;dbname=%s;charset=%s',
      DB_HOST, DB_PORT, DB_NAME, DB_CHARSET
    );

    $options = [
      PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
      PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
      PDO::ATTR_EMULATE_PREPARES   => false,
      PDO::MYSQL_ATTR_FOUND_ROWS   => true,
    ];

    try {
      $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
    } catch (PDOException $e) {
      if (APP_ENV === 'production') {
        http_response_code(503);
        die('Service temporarily unavailable. Please try again later.');
      }
      throw $e;
    }
  }

  return $pdo;
}

// ── CSRF helpers ─────────────────────────────────────────────
function csrf_token(): string {
  if (empty($_SESSION[CSRF_TOKEN_NAME])) {
    $_SESSION[CSRF_TOKEN_NAME] = bin2hex(random_bytes(32));
  }
  return $_SESSION[CSRF_TOKEN_NAME];
}

function csrf_field(): string {
  return '<input type="hidden" name="' . CSRF_TOKEN_NAME
       . '" value="' . htmlspecialchars(csrf_token(), ENT_QUOTES) . '">';
}

function csrf_verify(): bool {
  $token = $_POST[CSRF_TOKEN_NAME]
        ?? $_SERVER['HTTP_X_CSRF_TOKEN']
        ?? '';
  $valid = isset($_SESSION[CSRF_TOKEN_NAME])
        && hash_equals($_SESSION[CSRF_TOKEN_NAME], $token);
  // Rotate token after each verified POST
  if ($valid) {
    unset($_SESSION[CSRF_TOKEN_NAME]);
  }
  return $valid;
}

// ── Sanitisation helper ───────────────────────────────────────
function clean(string $value): string {
  return htmlspecialchars(trim($value), ENT_QUOTES, 'UTF-8');
}

// ── Redirect helper ───────────────────────────────────────────
function redirect(string $url, int $code = 302): never {
  header("Location: $url", true, $code);
  exit;
}

// ── Flash message helpers ─────────────────────────────────────
function flash_set(string $key, string $message, string $type = 'info'): void {
  $_SESSION['flash'][$key] = ['msg' => $message, 'type' => $type];
}

function flash_get(string $key): ?array {
  $flash = $_SESSION['flash'][$key] ?? null;
  unset($_SESSION['flash'][$key]);
  return $flash;
}
