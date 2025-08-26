<?php
// debug/db_status.php — Diagnóstico rápido do banco em produção
header('Content-Type: text/html; charset=utf-8');
date_default_timezone_set('America/Sao_Paulo');

$start = microtime(true);
require_once __DIR__ . '/../php/conn.php'; // <— usa sua conexão de produção

function h($s){ return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
function td($s){ return '<td>'.h($s).'</td>'; }

$errors = [];
$info = [
  'php_uname'    => php_uname(),
  'php_version'  => PHP_VERSION,
  'time'         => date('Y-m-d H:i:s'),
];

// Testes básicos
try {
  // Qual DB/host?
  $dbRow  = $conn->query('SELECT DATABASE() db')->fetch_assoc();
  $verRow = $conn->query('SELECT @@version AS v, @@hostname AS host, @@global.time_zone AS tz')->fetch_assoc();

  $info['db_name'] = $dbRow['db'] ?? '(n/d)';
  $info['mysql_version'] = $verRow['v'] ?? '(n/d)';
  $info['mysql_host'] = $verRow['host'] ?? '(n/d)';
  $info['mysql_tz'] = $verRow['tz'] ?? '(n/d)';

  // Tabela existe?
  $tblExists = $conn->query("SHOW TABLES LIKE 'acompanhamento_atividades'")->num_rows > 0;
  $info['table_exists'] = $tblExists ? 'SIM' : 'NÃO';

  if ($tblExists) {
    // Contagem total
    $totalRow = $conn->query("SELECT COUNT(*) c FROM acompanhamento_atividades")->fetch_assoc();
    $info['table_count'] = (int)($totalRow['c'] ?? 0);

    // Janela temporal
    $minmax = $conn->query("SELECT MIN(data_registro) min_dt, MAX(data_registro) max_dt FROM acompanhamento_atividades")->fetch_assoc();
    $info['min_data_registro'] = $minmax['min_dt'] ?? '(n/d)';
    $info['max_data_registro'] = $minmax['max_dt'] ?? '(n/d)';

    // 10 últimos (pelo data_registro)
    $rows = [];
    $res = $conn->query("
      SELECT id, nome, cpf, diretoria, data_inicial, data_final, LEFT(pontos_relevantes, 120) AS pr, data_registro
      FROM acompanhamento_atividades
      ORDER BY data_registro DESC
      LIMIT 10
    ");
    while($r=$res->fetch_assoc()){ $rows[] = $r; }
  } else {
    $rows = [];
  }

} catch (Throwable $e) {
  $errors[] = $e->getMessage();
}

$elapsed = round((microtime(true)-$start)*1000, 1);
?>
<!doctype html>
<html lang="pt-BR">
<head>
<meta charset="utf-8">
<title>DEBUG DB — COHIDRO AI</title>
<style>
  body{font:14px/1.45 system-ui, -apple-system, Segoe UI, Roboto, Arial; background:#0b0f14; color:#e5e7eb; padding:18px}
  .card{background:#111826; border:1px solid rgba(255,255,255,.08); border-radius:12px; padding:16px; margin-bottom:16px}
  .grid{display:grid; grid-template-columns:220px 1fr; gap:8px}
  table{width:100%; border-collapse:collapse; font-size:13px}
  th,td{border:1px solid #283347; padding:8px}
  th{background:#0f1723; text-align:left}
  .err{background:#2a1114; border:1px solid #7f1d1d; color:#fecaca; padding:10px; border-radius:8px}
  .muted{color:#9ca3af}
  code{background:#0f1723; padding:2px 6px; border-radius:6px}
</style>
</head>
<body>
  <div class="card">
    <h2 style="margin:0 0 8px">Status da conexão</h2>
    <div class="grid">
      <div>Servidor PHP</div><div><?= h($info['php_uname']) ?> (PHP <?= h($info['php_version']) ?>)</div>
      <div>MySQL host</div><div><?= h($info['mysql_host']) ?></div>
      <div>MySQL versão</div><div><?= h($info['mysql_version']) ?></div>
      <div>Time zone MySQL</div><div><?= h($info['mysql_tz']) ?></div>
      <div>Database atual</div><div><b><?= h($info['db_name']) ?></b></div>
      <div>Tabela acompanhamento_atividades</div><div><b><?= h($info['table_exists']) ?></b></div>
      <div>Qtde registros</div><div><b><?= h($info['table_count'] ?? 0) ?></b></div>
      <div>Janela de data_registro</div><div><?= h($info['min_data_registro'] ?? '') ?> → <?= h($info['max_data_registro'] ?? '') ?></div>
      <div>Gerado em</div><div><?= h($info['time']) ?> (<?= h($elapsed) ?>ms)</div>
    </div>
  </div>

  <?php if ($errors): ?>
    <div class="card err">
      <b>Erros:</b><br><?= h(implode("\n", $errors)) ?>
    </div>
  <?php endif; ?>

  <div class="card">
    <h3 style="margin-top:0">Últimos 10 registros (data_registro DESC)</h3>
    <?php if (!empty($rows)): ?>
    <table>
      <thead>
        <tr>
          <th>ID</th><th>Nome</th><th>CPF</th><th>Diretoria</th>
          <th>Data inicial</th><th>Data final</th><th>Pontos relevantes (120)</th><th>data_registro</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($rows as $r): ?>
          <tr>
            <?= td($r['id']) ?>
            <?= td($r['nome']) ?>
            <?= td($r['cpf']) ?>
            <?= td($r['diretoria']) ?>
            <?= td($r['data_inicial']) ?>
            <?= td($r['data_final']) ?>
            <?= td($r['pr']) ?>
            <?= td($r['data_registro']) ?>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
    <?php else: ?>
      <div class="muted">Sem registros para exibir, ou a tabela não existe.</div>
    <?php endif; ?>
  </div>

  <div class="card">
    <h3 style="margin-top:0">Dicas de verificação</h3>
    <ul>
      <li>Confirme se <code>php/conn.php</code> aponta para o **host/DB** corretos (produção).</li>
      <li>Cheque se há outra instância/clone do projeto servindo uma **raiz** diferente (aaPanel/nginx). Paths absolutos com <code>/</code> podem apontar para outra raiz.</li>
      <li>Se <b>Qtde registros</b> estiver diferente do esperado, é outro schema/host. Ajuste credenciais no <code>conn.php</code>.</li>
      <li>Depois de corrigir, recarregue esta página e a sua dashboard (DevTools → Network) e verifique.</li>
    </ul>
  </div>
</body>
</html>
