<?php
$brand      = 'COHIDRO AI';
$page_title = $brand . ' — BI · Obras';
$active     = 'dashboard-obras';
?>
<!doctype html>
<html lang="pt-BR" data-bs-theme="dark">
<?php include __DIR__ . '/partials/header.php'; ?>
<body>
  <?php
    include __DIR__ . '/partials/topbar.php';
    $__ACTIVE = $active;
    include __DIR__ . '/partials/sidebar.php';
  ?>

  <style>
    .kpi-money .value { font-size:1.35rem; font-weight:800 }
    .mini-title { font-weight:700; letter-spacing:.2px }
    .list-scroll { max-height:280px; overflow:auto }
    #tblObras thead th.sortable{ cursor:pointer; user-select:none; white-space:nowrap; }
    #tblObras thead th .sort{ display:inline-block; width:1rem; margin-left:.35rem; opacity:.6; vertical-align:middle; }
    #tblObras thead th.th-active .sort{ opacity:1; }
  </style>

  <main id="content" class="content">
    <!-- FILTROS -->
    <div class="card-d p-3 mb-3">
      <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 py-1">
        <h6 class="mb-0">BI · Obras (financeiro / vigência)</h6>
        <div class="d-flex align-items-center gap-2">
          <button id="btnLimpar" class="btn btn-sm btn-outline-secondary">Limpar filtros</button>
          <button id="btnExport" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-download me-1"></i>Exportar CSV
          </button>
          <span class="text-secondary small">
            <span id="countFiltered">0</span>/<span id="countTotal">0</span> registros
          </span>
        </div>
      </div>
      <hr class="border-secondary-subtle my-2">
      <div class="row g-2">
        <div class="col-12 col-md-3">
          <label class="form-label">Data inicial</label>
          <input id="fDataIni" type="date" class="form-control">
        </div>
        <div class="col-12 col-md-3">
          <label class="form-label">Data final</label>
          <input id="fDataFim" type="date" class="form-control">
        </div>
        <div class="col-12 col-md-3">
          <label class="form-label">Diretoria</label>
          <select id="fDiretoria" class="form-select" style="background:var(--field);border-color:var(--stroke);color:var(--text)">
            <option value="">Todas</option>
            <option>DIM</option><option>DMA</option><option>DPO</option><option>DGE</option>
          </select>
        </div>
        <div class="col-12 col-md-3">
          <label class="form-label">Busca (obra/empresa/pontos)</label>
          <input id="fBusca" class="form-control" placeholder="Digite para filtrar...">
        </div>
      </div>
    </div>

    <!-- KPIs principais -->
    <div class="row g-3">
      <div class="col-12 col-xl-3">
        <div class="card-d p-3 kpi kpi-money">
          <div class="mini-title text-uppercase">Valor total da Obra</div>
          <div id="kpiValor" class="value">R$ 0,00</div>
        </div>
      </div>
      <div class="col-12 col-xl-3">
        <div class="card-d p-3 kpi kpi-money">
          <div class="mini-title text-uppercase">Realizado</div>
          <div id="kpiRealizado" class="value">R$ 0,00</div>
        </div>
      </div>
      <div class="col-12 col-xl-3">
        <div class="card-d p-3 kpi kpi-money">
          <div class="mini-title text-uppercase">Saldo Contratual</div>
          <div id="kpiSaldo" class="value">R$ 0,00</div>
        </div>
      </div>
      <div class="col-12 col-xl-3">
        <div class="card-d p-3">
          <div class="mini-title text-uppercase mb-2">Evolução Financeira</div>
          <div id="gaugeEvo" style="height:130px"></div>
          <div class="d-flex justify-content-between text-secondary small">
            <span>0,00%</span><span>100,00%</span>
          </div>
        </div>
      </div>
    </div>

    <!-- Cards secundários -->
    <div class="row g-3 mt-1">
      <div class="col-12 col-xl-3">
        <div class="card-d p-3">
          <div class="mini-title">Previsão de Desembolso</div>
          <div class="mt-2">
            <div class="text-secondary small" id="prevAno1Label">2025</div>
            <div id="prevAno1" class="fw-bold">R$ 0,00</div>
          </div>
          <div class="mt-3">
            <div class="text-secondary small" id="prevAno2Label">2026</div>
            <div id="prevAno2" class="fw-bold">R$ 0,00</div>
          </div>
        </div>
      </div>
      <div class="col-12 col-xl-3">
        <div class="card-d p-3">
          <div class="mini-title">Resolução Conjunta</div>
          <div id="kpiResolucao" class="fw-bold mt-2">R$ 0,00</div>
          <div class="text-secondary">CONJUNTA</div>
          <div class="mt-2 text-secondary">SALDO</div>
          <div id="kpiResolucaoSaldo" class="fw-bold">R$ 0,00</div>
        </div>
      </div>
      <div class="col-12 col-xl-3">
        <div class="card-d p-3">
          <div class="mini-title">Descentralização</div>
          <div id="kpiDesc" class="fw-bold mt-2">R$ 0,00</div>
          <div class="text-secondary">DESCENTRALIZAÇÃO</div>
          <div class="mt-2 text-secondary">SALDO</div>
          <div id="kpiDescSaldo" class="fw-bold">R$ 0,00</div>
        </div>
      </div>
      <div class="col-12 col-xl-3">
        <div class="card-d p-3">
          <div class="mini-title">Empenho</div>
          <div id="kpiEmpenho" class="fw-bold mt-2">R$ 0,00</div>
          <div class="text-secondary">EMPENHO</div>
          <div class="mt-2 text-secondary">SALDO</div>
          <div id="kpiEmpenhoSaldo" class="fw-bold">R$ 0,00</div>
        </div>
      </div>
    </div>

    <!-- Lista de empresas + Tabela -->
    <div class="row g-3 mt-1">
      <div class="col-12 col-xl-4">
        <div class="card-d p-3">
          <div class="mini-title mb-2">Empresa</div>
          <div id="listaEmpresas" class="list-scroll small"><!-- via JS --></div>
        </div>
      </div>

      <div class="col-12 col-xl-8">
        <div class="card-d p-3">
          <div class="d-flex align-items-center justify-content-between mb-2">
            <div class="d-flex align-items-center gap-2">
              <div class="mini-title mb-0">Contrato - Vigência</div>
              <label class="text-secondary small">Linhas:</label>
              <select id="perPage" class="form-select form-select-sm"
                      style="width:88px;background:var(--field);border-color:var(--stroke);color:var(--text)">
                <option>10</option><option>25</option><option>50</option>
              </select>
            </div>
            <div class="d-flex align-items-center gap-1" id="pager"></div>
          </div>

          <div class="d-flex align-items-center justify-content-end mb-2">
            <input id="fBuscaTabela" class="form-control form-control-sm"
                   style="width:240px;background:var(--field);border-color:var(--stroke);color:var(--text)"
                   placeholder="Filtrar na tabela...">
          </div>

          <div class="table-responsive">
            <table id="tblObras" class="table table-dark table-hover align-middle table-sm">
              <thead>
                <tr>
                  <th class="sortable" data-key="obra">OBRA <span class="sort bi bi-arrow-down-up"></span></th>
                  <th class="sortable" data-key="tct_inicio">TCT INICIO <span class="sort bi bi-arrow-down-up"></span></th>
                  <th class="sortable" data-key="tct_termino">TCT TÉRMINO <span class="sort bi bi-arrow-down-up"></span></th>
                  <th class="sortable" data-key="inicio">INÍCIO <span class="sort bi bi-arrow-down-up"></span></th>
                  <th class="sortable" data-key="termino">TÉRMINO <span class="sort bi bi-arrow-down-up"></span></th>
                  <th class="sortable" data-key="obra_inicio">OBRA INICIO <span class="sort bi bi-arrow-down-up"></span></th>
                  <th class="sortable" data-key="obra_termino">OBRA TÉRMINO <span class="sort bi bi-arrow-down-up"></span></th>
                  <th class="sortable" data-key="garantia_meses">GARANTIA <span class="sort bi bi-arrow-down-up"></span></th>
                </tr>
              </thead>
              <tbody><!-- via JS --></tbody>
            </table>
          </div>
          <small class="text-secondary">* Dados fictícios; estrutura pronta para ler a tabela de atividades.</small>
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

  <!-- JS da aplicação (caminhos RELATIVOS) -->
  <script src="assets/js/app.js?v=1" defer></script>
  <script src="assets/js/dash_obras.js?v=1" defer></script>
</body>
</html>
