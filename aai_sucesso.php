<?php
if (session_status() === PHP_SESSION_NONE) session_start();
$nome = $_GET['nome'] ?? '';
$hasSession = !empty($_SESSION['access_level']);
$logoutUrl = $hasSession ? '/logout.php' : '/login.php';
?>
<!doctype html>
<html lang="pt-BR" data-bs-theme="dark">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Registro salvo — COHIDRO AI</title>
  <style>
    :root{ --bg:#0b0f14; --card:#111826; --text:#e5e7eb; --muted:#9ca3af; --accent:#22d3ee }
    *{box-sizing:border-box} html,body{height:100%}
    body{ margin:0; color:var(--text); background:var(--bg); font:16px/1.5 system-ui, -apple-system, Segoe UI, Roboto, Arial, sans-serif; display:flex; align-items:center; justify-content:center; padding:24px }
    .card{background:var(--card); border:1px solid rgba(255,255,255,.08); border-radius:16px; box-shadow:0 10px 30px rgba(0,0,0,.35); max-width:560px; padding:28px }
    .btn{display:inline-flex; align-items:center; justify-content:center; gap:.5rem; padding:.9rem 1.2rem; border:0; border-radius:12px; cursor:pointer; font-weight:700}
    .btn-primary{background:linear-gradient(180deg, var(--accent), #06b6d4); color:#001016}
    .btn-ghost{background:#263041; color:#e5e7eb}
  </style>
</head>
<body>
  <div class="card">
    <h2 style="margin:0 0 8px">Registro salvo com sucesso!</h2>
    <div style="color:#9ca3af; margin:0 0 16px">
      <?= $nome ? 'Obrigado, <b>'.htmlspecialchars($nome).'</b>.' : 'Obrigado.' ?>
      Deseja lançar outra atividade agora?
    </div>
    <div style="display:flex; gap:10px; flex-wrap:wrap">
      <a class="btn btn-primary" href="/formulario.php">Sim, lançar outra</a>
      <a class="btn btn-ghost" href="<?= htmlspecialchars($logoutUrl) ?>">Não, sair para o login</a>
    </div>
  </div>
</body>
</html>
