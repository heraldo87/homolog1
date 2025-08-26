<?php
// debug/sql_peek.php — executor de SELECT com LIMIT (somente leitura)
header('Content-Type: text/html; charset=utf-8');
require_once __DIR__ . '/../php/conn.php';

function h($s){ return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }

$q = trim($_POST['q'] ?? $_GET['q'] ?? "SELECT id, nome, cpf, diretoria, data_inicial, data_final, data_registro FROM acompanhamento_atividades ORDER BY id DESC LIMIT 20");

$err = '';
$rows = [];
$cols = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST' || isset($_GET['q'])) {
  if (!preg_match('/^\s*SELECT\b/i', $q)) {
    $err = 'Apenas SELECT é permitido.';
  } elseif (!preg_match('/\bLIMIT\s+\d+/i', $q)) {
    $err = 'Inclua LIMIT na consulta para evitar dumps grandes.';
  } else {
    try {
      $res = $conn->query($q);
      if ($res === false) {
        $err = 'Erro SQL: ' . $conn->error;
      } else {
        while ($r = $res->fetch_assoc()) $rows[] = $r;
        if (!empty($rows)) $cols = array_keys($rows[0]);
      }
    } catch (Throwable $e) {
      $err = $e->getMessage();
    }
  }
}
?>
<!doctype html>
<html lang="pt-BR">
<head>
<meta charset="utf-8">
<title>SQL Peek — DEBUG</title>
<style>
  body{font:14px/1.45 system-ui, -apple-system, Segoe UI, Roboto, Arial; background:#0b0f14; color:#e5e7eb; padding:18px}
  .card{background:#111826; border:1px solid rgba(255,255,255,.08); border-radius:12px; padding:16px; margin-bottom:16px}
  textarea{width:100%; height:120px; background:#0f1723; color:#e5e7eb; border:1px solid #283347; border-radius:8px; padding:10px}
  button{padding:.6rem 1rem; border:0; border-radius:8px; background:#22d3ee; color:#001016; font-weight:700; cursor:pointer}
  table{width:100%; border-collapse:collapse; font-size:13px}
  th,td{border:1px solid #283347; padding:8px}
  th{background:#0f1723; text-align:left}
  .err{background:#2a1114; border:1px solid #7f1d1d; color:#fecaca; padding:10px; border-radius:8px}
  .muted{color:#9ca3af}
</style>
</head>
<body>
  <div class="card">
    <h2 style="margin:0 0 8px">SQL Peek (somente SELECT)</h2>
    <form method="post">
      <textarea name="q"><?= h($q) ?></textarea>
      <div style="margin-top:10px">
        <button type="submit">Executar</button>
        <span class="muted">Apenas SELECT com LIMIT é permitido.</span>
      </div>
    </form>
  </div>

  <?php if ($err): ?>
    <div class="card err"><b>Erro:</b> <?= h($err) ?></div>
  <?php endif; ?>

  <?php if (!$err && !empty($rows)): ?>
    <div class="card">
      <h3 style="margin-top:0">Resultado (<?= count($rows) ?> linha(s))</h3>
      <div class="muted" style="margin:0 0 8px">Primeiras colunas: <?= h(implode(', ', $cols)) ?></div>
      <div class="table-wrap">
        <table>
          <thead><tr><?php foreach ($cols as $c) echo '<th>'.h($c).'</th>'; ?></tr></thead>
          <tbody>
            <?php foreach ($rows as $r): ?>
              <tr><?php foreach ($cols as $c) echo '<td>'.h($r[$c]).'</td>'; ?></tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  <?php endif; ?>
</body>
</html>
