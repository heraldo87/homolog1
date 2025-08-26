<?php
// formulario.php — Lançamento de Acompanhamento de Atividades (AAI)
// Nível 1: sem sessão (acesso livre). Níveis 2/3: se houver sessão, pré-preenche.
// Agora também pré-preenche Nível 1 se vier ?cpf=########### na URL.

if (session_status() === PHP_SESSION_NONE) session_start();

require_once __DIR__ . '/php/conn.php'; // necessário para buscar colaborador por CPF

function only_digits(?string $s): string { return preg_replace('/\D+/', '', $s ?? ''); }

$nomeSessao  = $_SESSION['nome']  ?? '';
$emailSessao = $_SESSION['email'] ?? '';
$cpfSessao   = $_SESSION['cpf']   ?? '';

// Se NÃO houver sessão (nível 1), tentar pré-preencher a partir do CPF na URL
if (empty($nomeSessao) || empty($cpfSessao)) {
  $cpfUrl = only_digits($_GET['cpf'] ?? '');
  if (strlen($cpfUrl) === 11) {
    $sql = "SELECT nome, email,
                   REPLACE(REPLACE(REPLACE(cpf, '.', ''), '-', ''), ' ', '') AS cpf_norm,
                   diretoria
            FROM colaboradores
            WHERE REPLACE(REPLACE(REPLACE(cpf, '.', ''), '-', ''), ' ', '') = ?
            LIMIT 1";
    if ($stmt = $conn->prepare($sql)) {
      $stmt->bind_param('s', $cpfUrl);
      $stmt->execute();
      $res = $stmt->get_result();
      if ($row = $res->fetch_assoc()) {
        $nomeSessao  = $row['nome']      ?: $nomeSessao;
        $emailSessao = $row['email']     ?: $emailSessao;
        $cpfSessao   = $row['cpf_norm']  ?: $cpfSessao;
        $diretoriaBanco = $row['diretoria'] ?? '';
      }
      $stmt->close();
    }
  }
}

$ok  = $_GET['ok']  ?? '';
$err = $_GET['err'] ?? '';
?>
<!doctype html>
<html lang="pt-BR" data-bs-theme="dark">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Lançar Atividade — COHIDRO AI</title>
  <style>
    :root{ --bg:#0b0f14; --card:#111826; --text:#e5e7eb; --muted:#9ca3af; --field:#0f1723; --stroke:#283347; --accent:#22d3ee; --danger:#ef4444; }
    *{box-sizing:border-box} html,body{height:100%}
    body{ margin:0; color:var(--text); background:var(--bg); font:16px/1.5 system-ui, -apple-system, Segoe UI, Roboto, Arial, sans-serif; padding:24px }
    .container{max-width:880px; margin:0 auto}
    .card{background:var(--card); border:1px solid rgba(255,255,255,.08); border-radius:16px; box-shadow:0 10px 30px rgba(0,0,0,.35)}
    .card-body{padding:24px}
    .row{display:grid; grid-template-columns:1fr 1fr; gap:16px}
    .row-1{display:grid; grid-template-columns:1fr; gap:16px}
    .form-label{display:block; margin:.25rem 0 .35rem}
    .form-control, textarea, select{width:100%; background:var(--field); color:var(--text); border:1px solid var(--stroke); border-radius:12px; padding:.8rem 1rem}
    textarea{min-height:120px; resize:vertical}
    .btn{display:inline-flex; align-items:center; justify-content:center; gap:.5rem; padding:.9rem 1.2rem; border:0; border-radius:12px; cursor:pointer; font-weight:700}
    .btn-primary{background:linear-gradient(180deg, var(--accent), #06b6d4); color:#001016}
    .muted{color:var(--muted)}
    .alert{padding:.75rem 1rem; border-radius:12px; margin:0 0 12px}
    .alert-ok{background:#102416; border:1px solid #14532d; color:#bbf7d0}
    .alert-err{background:#2a1114; border:1px solid #7f1d1d; color:#fecaca}
    .pill{display:inline-block; padding:.2rem .55rem; border-radius:999px; background:#102039; border:1px solid #283347; color:#a5b4fc; font-size:.8rem; margin-left:.4rem}
  </style>
</head>
<body>
  <main class="container">
    <h2 style="margin:0 0 8px">Acompanhamento de Atividades (AAI)
      <?php if (!empty($nomeSessao)): ?>
        <span class="pill">Colaborador: <?= htmlspecialchars($nomeSessao) ?></span>
      <?php endif; ?>
    </h2>
    <div class="muted" style="margin:0 0 16px">Preencha os campos abaixo e clique em <b>Enviar</b>.</div>

    <?php if ($ok === '1'): ?>
      <div class="alert alert-ok">Registro salvo com sucesso!</div>
    <?php elseif ($err): ?>
      <div class="alert alert-err">
        <?php
          $msgs = [
            'val' => 'Falha de validação: verifique CPF, datas e campos obrigatórios.',
            'db'  => 'Erro ao salvar no banco de dados.',
            'dup' => 'Registro duplicado detectado para o mesmo período.',
          ];
          echo htmlspecialchars($msgs[$err] ?? 'Erro inesperado.');
        ?>
      </div>
    <?php endif; ?>

    <div class="card">
      <div class="card-body">
        <form method="post" action="/php/aai_store.php" id="formAAI" novalidate>
          <div class="row">
            <div>
              <label class="form-label" for="nome">Nome</label>
              <input class="form-control" type="text" id="nome" name="nome" required
                     value="<?= htmlspecialchars($nomeSessao) ?>">
            </div>
            <div>
              <label class="form-label" for="email">E-mail</label>
              <input class="form-control" type="email" id="email" name="email"
                     value="<?= htmlspecialchars($emailSessao) ?>" placeholder="opcional (não grava na tabela)">
            </div>
          </div>

          <div class="row">
            <div>
              <label class="form-label" for="cpf">CPF</label>
              <input class="form-control" type="text" id="cpf" name="cpf" inputmode="numeric" maxlength="14"
                     placeholder="000.000.000-00" required value="<?= htmlspecialchars($cpfSessao) ?>">
            </div>
            <div>
              <label class="form-label" for="diretoria">Diretoria</label>
              <input class="form-control" type="text" id="diretoria" name="diretoria" required
                     placeholder="Ex.: DIM / NITERÓI"
                     value="<?= htmlspecialchars($diretoriaBanco ?? '') ?>">
            </div>
          </div>

          <div class="row">
            <div>
              <label class="form-label" for="data_inicial">Data inicial</label>
              <input class="form-control" type="date" id="data_inicial" name="data_inicial" required>
            </div>
            <div>
              <label class="form-label" for="data_final">Data final</label>
              <input class="form-control" type="date" id="data_final" name="data_final" required>
            </div>
          </div>

          <div class="row-1">
            <div>
              <label class="form-label" for="atividades_realizadas">Atividades realizadas</label>
              <textarea id="atividades_realizadas" name="atividades_realizadas" required></textarea>
            </div>
            <div>
              <label class="form-label" for="atividades_previstas">Atividades previstas</label>
              <textarea id="atividades_previstas" name="atividades_previstas" required></textarea>
            </div>
            <div>
              <label class="form-label" for="pontos_relevantes">Pontos relevantes</label>
              <textarea id="pontos_relevantes" name="pontos_relevantes" placeholder="opcional"></textarea>
            </div>
          </div>

          <div style="margin-top:14px; display:flex; gap:10px">
            <button class="btn btn-primary" type="submit">Enviar</button>
            <a class="btn" href="/login.php" style="background:#263041; color:#e5e7eb">Voltar ao login</a>
          </div>
        </form>
      </div>
    </div>
  </main>

  <script>
    // Máscara de CPF (não substitui validação back-end)
    const onlyDigits = s => s.replace(/\D+/g,'');
    const $cpf = document.getElementById('cpf');
    function formatCPF(v){
      const d = onlyDigits(v).slice(0,11);
      let out = '';
      if (d.length>0) out = d.slice(0,3);
      if (d.length>=4) out += '.'+d.slice(3,6);
      if (d.length>=7) out += '.'+d.slice(6,9);
      if (d.length>=10) out += '-'+d.slice(9,11);
      return out;
    }
    // Formata o valor inicial (caso venha "só dígitos" do banco)
    if ($cpf.value && /^\d{11}$/.test($cpf.value)) {
      $cpf.value = formatCPF($cpf.value);
    }
    $cpf.addEventListener('input', () => { $cpf.value = formatCPF($cpf.value); });
  </script>
</body>
</html>
