<?php
// php/auth_check.php — proteção de páginas por nível de acesso

function _start_secure_session(): void {
  if (session_status() === PHP_SESSION_NONE) {
    $isHttps = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
    session_set_cookie_params([
      'lifetime' => 0,
      'path'     => '/',
      'secure'   => $isHttps,
      'httponly' => true,
      'samesite' => 'Lax',
    ]);
    session_start();
  }
}

function require_login(int $minLevel = 2): void {
  _start_secure_session();
  $lvl = intval($_SESSION['access_level'] ?? 0);
  if ($lvl < $minLevel) {
    $redir = '/login.php?redirect=' . urlencode($_SERVER['REQUEST_URI'] ?? '/');
    header('Location: ' . $redir, true, 302);
    exit;
  }
  // evita cache de páginas protegidas
  header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
  header('Pragma: no-cache');
}

function logout_and_redirect(): void {
  _start_secure_session();
  $_SESSION = [];
  if (ini_get('session.use_cookies')) {
    $p = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'] ?? '', $p['secure'], $p['httponly']);
  }
  session_destroy();
  header('Location: /login.php');
  exit;
}
