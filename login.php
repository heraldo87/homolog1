<!doctype html>
<html lang="pt-BR" data-bs-theme="dark">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Login — COHIDRO AI</title>

  <!-- Estilo próprio (sem importar Bootstrap por enquanto) -->
  <style>
    :root{
      --bg:#0b0f14; --card:#111826; --text:#e5e7eb; --muted:#9ca3af;
      --field:#0f1723; --stroke:#283347; --accent:#22d3ee; --accent-2:#06b6d4; --danger:#ef4444;
    }
    *{box-sizing:border-box}
    html,body{height:100%}
    body{
      margin:0; color:var(--text); background:
        radial-gradient(1200px 600px at 80% -10%, rgba(100,100,255,.12), transparent 60%),
        radial-gradient(900px 500px at -10% 110%, rgba(0,180,140,.10), transparent 60%),
        var(--bg);
      font: 16px/1.5 system-ui, -apple-system, Segoe UI, Roboto, Arial, "Helvetica Neue", "Noto Sans", "Liberation Sans", sans-serif;
      display:flex; align-items:center; justify-content:center; padding:24px;
    }
    .container{width:100%; max-width:440px}
    .card{background:var(--card); border:1px solid rgba(255,255,255,.08); border-radius:16px; box-shadow:0 10px 30px rgba(0,0,0,.35)}
    .card-body{padding:28px}
    .brand{font-weight:700; letter-spacing:.4px}
    .text-secondary{color:var(--muted)}
    .form-label{display:block; margin:0 0 8px}
    .form-text{color:var(--muted); font-size:.9rem; margin-top:8px}
    .form-control{
      width:100%; padding:1rem 1.1rem; border-radius:12px;
      border:1px solid var(--stroke); background:var(--field); color:var(--text);
      font-variant-numeric: tabular-nums;
      outline:none; transition:border-color .15s ease;
    }
    .form-control:focus{border-color:var(--accent)}
    .btn{
      width:100%; display:inline-flex; align-items:center; justify-content:center; gap:.5rem;
      padding:1rem 1.1rem; border:0; border-radius:14px; cursor:pointer; font-weight:700;
      background:linear-gradient(180deg, var(--accent), var(--accent-2)); color:#001016;
      box-shadow:0 10px 28px rgba(34,211,238,.22);
      margin-top:20px; /* + espaço entre CPF e botão */
    }
    .btn:active{transform:translateY(1px)}
    .alert{padding:.7rem .9rem; border-radius:10px; margin-bottom:16px; font-size:.95rem}
    .alert-danger{background:#2a1114; border:1px solid #7f1d1d; color:#fecaca}
    .alert-success{background:#102416; border:1px solid #14532d; color:#bbf7d0}
    .invalid{border-color:var(--danger)!important}
    .invalid-feedback{color:#fecaca; font-size:.9rem; margin-top:8px; display:none}
    .invalid + .invalid-feedback{display:block}
    .header{ text-align:center; margin-bottom:22px }
  </style>
</head>
<body>
  <main class="container">
    <div class="card">
      <div class="card-body">
        <div class="header">
          <div class="brand fs-4">COHIDRO <span style="color:var(--accent)">AI</span></div>
          <div class="text-secondary small">Acesso ao sistema</div>
        </div>

        <div id="msg" class="alert" style="display:none"></div>

        <form id="formLogin" method="post" action="">
          <div class="mb-3">
            <label for="cpf" class="form-label">CPF</label>
            <input
              type="text"
              id="cpf"
              name="cpf"
              class="form-control"
              inputmode="numeric"
              maxlength="14"
              placeholder="000.000.000-00"
              autocomplete="on"
              autofocus
              required
            >
            <div class="form-text">Digite apenas seu CPF. Ex.: 123.456.789-09</div>
            <div class="invalid-feedback">CPF inválido. Verifique e tente novamente.</div>
          </div>

          <button type="submit" class="btn">Entrar</button>
          <!-- Link inferior removido conforme pedido -->
        </form>
      </div>
    </div>
  </main>

  <script>
    // Utilidades
    const onlyDigits = (s) => s.replace(/\D+/g, '');
    function formatCPF(v){
      const d = onlyDigits(v).slice(0,11);
      let out = '';
      if (d.length > 0) out = d.slice(0,3);
      if (d.length >= 4) out += '.' + d.slice(3,6);
      if (d.length >= 7) out += '.' + d.slice(6,9);
      if (d.length >= 10) out += '-' + d.slice(9,11);
      return out;
    }
    function isValidCPF(cpf){
      const s = onlyDigits(cpf);
      if (s.length !== 11) return false;
      if (/^(\d)\1{10}$/.test(s)) return false; // todos iguais
      for (let t = 9; t < 11; t++) {
        let d = 0;
        for (let c = 0; c < t; c++) d += parseInt(s[c],10) * ((t + 1) - c);
        d = ((10 * d) % 11) % 10;
        if (parseInt(s[t],10) !== d) return false;
      }
      return true;
    }

    // Máscara + limpeza de estado inválido ao digitar
    const $cpf = document.getElementById('cpf');
    $cpf.addEventListener('input', () => {
      const before = $cpf.value;
      $cpf.value = formatCPF(before);
      $cpf.classList.remove('invalid');
      document.getElementById('msg').style.display = 'none';
    });

    // Submit (mock, sem back-end ainda)
    const $form = document.getElementById('formLogin');
    const $msg = document.getElementById('msg');
    $form.addEventListener('submit', (e) => {
      e.preventDefault();
      const ok = isValidCPF($cpf.value);
      if (!ok) {
        $cpf.classList.add('invalid');
        $msg.className = 'alert alert-danger';
        $msg.textContent = 'CPF inválido.';
        $msg.style.display = 'block';
        $cpf.focus();
        return;
      }
      $cpf.classList.remove('invalid');
      $msg.className = 'alert alert-success';
      $msg.textContent = 'CPF válido! (Na integração, faremos POST/redirect pelo nível de acesso).';
      $msg.style.display = 'block';
    });
  </script>
</body>
</html>
