<?php
// Sem sessão aqui, conforme pedido
$brand       = 'COHIDRO AI';
$page_title  = $brand . ' — Dashboard';
$active      = 'overview'; // overview | formulario | dashboards | relatorios | usuarios | config
?>
<!doctype html>
<html lang="pt-BR" data-bs-theme="dark">
<?php include __DIR__ . '/partials/header.php'; ?>
<body>

  <?php
    // Partials de navegação
    // (topbar e sidebar já têm fallbacks para $usuario/$nivel)
    include __DIR__ . '/partials/topbar.php';
    $__ACTIVE = $active; // marca o item ativo no sidebar
    include __DIR__ . '/partials/sidebar.php';
  ?>

  <main id="content" class="content">
    <!-- KPIs -->
    <div class="row g-3">
      <div class="col-12 col-md-6 col-xl-3">
        <div class="card-d p-3 kpi"><div class="label">Obras em andamento</div><div class="value">32</div><div class="text-secondary small">+3 vs mês anterior</div></div>
      </div>
      <div class="col-12 col-md-6 col-xl-3">
        <div class="card-d p-3 kpi"><div class="label">Valor contratado</div><div class="value">R$ 18,4 mi</div><div class="text-secondary small">+6,2% YTD</div></div>
      </div>
      <div class="col-12 col-md-6 col-xl-3">
        <div class="card-d p-3 kpi"><div class="label">Execução física média</div><div class="value">61%</div><div class="text-secondary small">-1 pp no mês</div></div>
      </div>
      <div class="col-12 col-md-6 col-xl-3">
        <div class="card-d p-3 kpi"><div class="label">Alertas críticos</div><div class="value">5</div><div class="text-secondary small">2 novos</div></div>
      </div>
    </div>

    <!-- Charts -->
    <div class="row g-3 mt-1">
      <div class="col-12 col-xl-8">
        <div class="card-d p-3">
          <h6 class="mb-2">Execução Física — Últimos 12 meses</h6>
          <div id="chartLine" class="chart"></div>
        </div>
      </div>
      <div class="col-12 col-xl-4">
        <div class="card-d p-3">
          <h6 class="mb-2">Obras por Status</h6>
          <div id="chartPie" class="chart" style="height:290px"></div>
        </div>
      </div>
    </div>

    <div class="row g-3 mt-1">
      <div class="col-12 col-xl-6">
        <div class="card-d p-3">
          <h6 class="mb-2">Valor Liquidado por Diretoria (R$)</h6>
          <div id="chartBar" class="chart"></div>
        </div>
      </div>

      <!-- Tabela -->
      <div class="col-12 col-xl-6">
        <div class="card-d p-3">
          <div class="d-flex align-items-center justify-content-between mb-2">
            <h6 class="mb-0">Obras — Próximos marcos</h6>
            <input id="filter-municipio" class="form-control form-control-sm"
                   style="width:220px; background:var(--field); border-color:var(--stroke); color:var(--text)"
                   placeholder="Filtrar por município...">
          </div>
          <div class="table-responsive">
            <table id="tbl" class="table table-dark table-hover align-middle table-sm">
              <thead>
                <tr>
                  <th>Processo</th><th>Município</th><th>Diretoria</th>
                  <th>Empresa</th><th>Prazo (dias)</th><th>Término Prev.</th>
                </tr>
              </thead>
              <tbody>
                <tr><td>SEI-00123</td><td>Niterói</td><td>DIM</td><td>Construsul</td><td>180</td><td>2025-10-14</td></tr>
                <tr><td>SEI-00487</td><td>Nova Iguaçu</td><td>DMA</td><td>Obras RJ</td><td>120</td><td>2025-09-03</td></tr>
                <tr><td>SEI-00276</td><td>Campos</td><td>DIM</td><td>Alpha Eng.</td><td>210</td><td>2026-01-22</td></tr>
                <tr><td>SEI-00942</td><td>Volta Redonda</td><td>DPO</td><td>Beta Constr.</td><td>90</td><td>2025-11-10</td></tr>
                <tr><td>SEI-00611</td><td>Petrópolis</td><td>DMA</td><td>RJ Obras</td><td>150</td><td>2025-12-01</td></tr>
                <tr><td>SEI-00339</td><td>Itaperuna</td><td>DPO</td><td>Constr. Vale</td><td>200</td><td>2026-02-18</td></tr>
              </tbody>
            </table>
          </div>
          <small class="text-secondary">* Dados fictícios para ilustração.</small>
        </div>
      </div>
    </div>

    <div class="text-center text-secondary mt-4 small">
      © <span id="y"></span> <?= htmlspecialchars($brand) ?>
    </div>
  </main>

  <!-- JS (CDNs) -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" defer></script>
  <script src="https://cdn.jsdelivr.net/npm/echarts@5/dist/echarts.min.js" defer></script>

  <!-- JS da aplicação -->
  <script src="/assets/js/app.js" defer></script>
</body>
</html>
