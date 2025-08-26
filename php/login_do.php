<?php
/**
 * php/login_do.php — Login por CPF
 * Regras:
 * - Nível 1: NÃO inicia sessão → redireciona para formulario.php com ?cpf=###########
 * - Nível 2/3: inicia sessão segura → redireciona (default: index.php)
 * - Aceita CPF com/sem pontuação (normaliza no back-end)
 * - (Opcional) Checa status do colaborador
 */

require_once __DIR__ . '/conn.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  header('Location: /login.php?err=method');
  exit;
}

function only_digits(?string $s): string {
  return preg_replace('/\D+/', '', $s ?? '');
}

$cpfRaw   = $_POST['cpf'] ?? '';
$cpf      = only_digits($cpfRaw);
$redirect = trim((string)($_POST['redirect'] ?? ''));

// Validação mínima
if (strlen($cpf) !== 11) {
  header('Location: /login.php?err=cpf');
  exit;
}

// Consulta tolerante a CPF com/sem pontuação
$sql = <<<SQL
SELECT id_colaborador, nome, email, access_level, status,
       REPLACE(REPLACE(REPLACE(cpf, '.', ''), '-', ''), ' ', '') AS cpf_norm
FROM colaboradores
WHERE REPLACE(REPLACE(REPLACE(cpf, '.', ''), '-', ''), ' ', '') = ?
LIMIT 1
SQL;

if (!$stmt = $conn->prepare($sql)) {
  header('Location: /login.php?err=sqlprep');
  exit;
}
$stmt->bind_param('s', $cpf);
$stmt->execute();
$res  = $stmt->get_result();
$user = $res ? $res->fetch_assoc() : null;
$stmt->close();

if (!$user) {
  header('Location: /login.php?err=notfound');
  exit;
}

// (Opcional) checagem de status
$status = strtoupper((string)($user['status'] ?? ''));
if ($status && !in_array($status, ['ATIVO','CONTRATADO'], true)) {
  header('Location: /login.php?err=status');
  exit;
}

$level = (int)$user['access_level'];
$cpf11 = $user['cpf_norm'] ?: $cpf;

// --------------------------
// Fluxo: nível 1 (sem sessão)
// --------------------------
if ($level === 1) {
  // Garante que não ficará nenhuma sessão ativa
  if (session_status() === PHP_SESSION_ACTIVE) {
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
      $p = session_get_cookie_params();
      setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'] ?? '', $p['secure'], $p['httponly']);
    }
    session_destroy();
  }
  // Envia CPF na URL para pré-preenchimento do formulário
  $target = $redirect ?: '/formulario.php?cpf=' . urlencode($cpf11);
  header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
  header('Pragma: no-cache');
  header('Location: ' . $target, true, 302);
  exit;
}

// ------------------------------------
// Fluxo: níveis 2 e 3 (com sessão ativa)
// ------------------------------------
$isHttps = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
session_set_cookie_params([
  'lifetime' => 0,
  'path'     => '/',
  'secure'   => $isHttps,
  'httponly' => true,
  'samesite' => 'Lax',
]);
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}
session_regenerate_id(true);

// Seta dados mínimos da sessão
$_SESSION['user_id']      = $user['id_colaborador'];
$_SESSION['nome']         = $user['nome'];
$_SESSION['cpf']          = $cpf11;             // 11 dígitos
$_SESSION['email']        = $user['email'] ?? null;
$_SESSION['access_level'] = $level;

// Rota de destino (ajuste se tiver /admin)
$target = $redirect ?: '/index.php';
// if ($level >= 3) { $target = $redirect ?: '/admin/index.php'; }

header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');
header('Location: ' . $target, true, 302);
exit;
