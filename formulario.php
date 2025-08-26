<!doctype html>
<html lang="pt-BR" data-bs-theme="dark">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Formulário AAI — COHIDRO AI</title>

  <!-- Bootstrap (CDN) -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

  <style>
    :root{ --bg:#0b0f14; --card:#111826; --text:#e5e7eb; --muted:#9ca3af; --stroke:#273244; --field:#0f1723; --acc:#22d3ee; }
    html,body{height:100%}
    body{
      margin:0;color:var(--text);
      background:
        radial-gradient(1200px 600px at 80% -10%, rgba(100,100,255,.12), transparent 60%),
        radial-gradient(900px 500px at -10% 110%, rgba(0,180,140,.10), transparent 60%),
        var(--bg);
      font:16px/1.5 system-ui,-apple-system,Segoe UI,Roboto,Arial,"Helvetica Neue","Noto Sans",sans-serif;
    }
    .container-limit{max-width:980px;margin:24px auto;padding:0 16px}
    .card{background:var(--card);border:1px solid rgba(255,255,255,.08);border-radius:14px;box-shadow:0 10px 30px rgba(0,0,0,.35);overflow:hidden}
    .card-header{padding:14px 18px;background:linear-gradient(90deg,#1d4ed8,#2563eb,#3b82f6);color:#fff;font-weight:600}
    .form-control{background:var(--field);border-color:var(--stroke);color:var(--text)}
    .form-control:focus{border-color:var(--acc);box-shadow:0 0 0 .25rem rgba(34,211,238,.15)}
    textarea.form-control{min-height:90px}
  </style>
</head>
<body>
  <div class="container-limit">

    <!-- (sem texto acima do formulário) -->
    <div class="card">
      <div class="card-header">Dados do Registro</div>

      <!-- Apenas UI por enquanto (sem inserir no banco) -->
      <form class="card-body" id="formAAI" method="post" action="">
        <!-- Linha 1: nome + cpf -->
        <div class="row g-3 align-items-end">
          <div class="col-12 col-md-8">
            <label class="form-label">Colaborador <span class="text-danger">*</span></label>
            <input type="text" class="form-control" name="nome" required>
          </div>
          <div class="col-12 col-md-4">
            <label class="form-label">CPF <span class="text-danger">*</span></label>
            <input type="text" class="form-control" id="cpf" name="cpf" maxlength="11" placeholder="Somente dígitos (11)" required>
          </div>
        </div>

        <!-- Linha 2: diretoria + datas (sem Período de Avaliação) -->
        <div class="row g-3 mt-0 align-items-end">
          <div class="col-12 col-md-4">
            <label class="form-label">Diretoria <span class="text-danger">*</span></label>
            <input type="text" class="form-control" name="diretoria" required>
          </div>
          <div class="col-12 col-md-4">
            <label class="form-label">Data Inicial <span class="text-danger">*</span></label>
            <input type="date" class="form-control" id="data_inicial" name="data_inicial" required>
          </div>
          <div class="col-12 col-md-4">
            <label class="form-label">Data Final <span class="text-danger">*</span></label>
            <input type="date" class="form-control" id="data_final" name="data_final" required>
          </div>
        </div>

        <!-- Linha 3: textareas -->
        <div class="mt-3">
          <label class="form-label">Atividades Realizadas</label>
          <textarea class="form-control" name="atividades_realizadas"></textarea>
        </div>

        <div class="mt-3">
          <label class="form-label">Atividades Previstas</label>
          <textarea class="form-control" name="atividades_previstas"></textarea>
        </div>

        <div class="mt-3">
          <label class="form-label">Pontos Relevantes</label>
          <textarea class="form-control" name="pontos_relevantes"></textarea>
        </div>

        <div class="d-flex gap-2 pt-3">
          <button type="submit" class="btn btn-primary">Enviar</button>
          <button type="reset" class="btn btn-outline-secondary">Limpar</button>
        </div>
      </form>
    </div>
  </div>

  <!-- Bootstrap JS (opcional, só para componentes) -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

  <!-- JS simples: validações de frente -->
  <script>
    // CPF: apenas dígitos
    document.getElementById('cpf').addEventListener('input', (e)=>{
      e.target.value = (e.target.value||'').replace(/\D+/g,'').slice(0,11);
    });

    // Validação simples: data_final >= data_inicial
    document.getElementById('formAAI').addEventListener('submit', (e)=>{
      const di = document.getElementById('data_inicial').value;
      const df = document.getElementById('data_final').value;
      if (di && df && df < di) {
        e.preventDefault();
        alert('A Data Final não pode ser anterior à Data Inicial.');
      }
    });
  </script>
</body>
</html>
