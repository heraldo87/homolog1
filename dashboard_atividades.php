<?php
$brand      = 'COHIDRO AI';
$page_title = $brand . ' — BI · Atividades';
$active     = 'dashboard-atividades';
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

  <!-- CSS extra (ordenação da tabela) -->
  <style>
    #tblAtv thead th.sortable{ cursor:pointer; user-select:none; white-space:nowrap; }
    #tblAtv thead th .sort{ display:inline-block; width:1rem; margin-left:.35rem; opacity:.6; vertical-align:middle; }
    #tblAtv thead th.th-active .sort{ opacity:1; }
    #tblAtv thead th.sortable:hover .sort{ opacity:.9; }
  </style>

  <main id="content" class="content">
    <!-- FILTROS -->
    <div class="card-d p-3 mb-3">
      <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 py-1">
        <h6 class="mb-0">BI · Acompanhamento de Atividades</h6>
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
            <option>DIM</option>
            <option>DMA</option>
            <option>DPO</option>
            <option>DGE</option>
          </select>
        </div>
        <div class="col-12 col-md-3">
          <label class="form-label">Busca (nome, atividade, pontos)</label>
          <input id="fBusca" class="form-control" placeholder="Digite para filtrar...">
        </div>
      </div>
    </div>

    <!-- KPIs -->
    <div class="row g-3">
      <div class="col-12 col-md-6 col-xl-3">
        <div class="card-d p-3 kpi"><div class="label">Total de registros</div><div id="kpiTotal" class="value">0</div><div class="text-secondary small">no intervalo</div></div>
      </div>
      <div class="col-12 col-md-6 col-xl-3">
        <div class="card-d p-3 kpi"><div class="label">Colaboradores únicos</div><div id="kpiColab" class="value">0</div><div class="text-secondary small">distintos</div></div>
      </div>
      <div class="col-12 col-md-6 col-xl-3">
        <div class="card-d p-3 kpi"><div class="label">Duração média</div><div id="kpiDur" class="value">0</div><div class="text-secondary small">dias (data_final - data_inicial)</div></div>
      </div>
      <div class="col-12 col-md-6 col-xl-3">
        <div class="card-d p-3 kpi"><div class="label">% com pontos relevantes</div><div id="kpiPR" class="value">0%</div><div class="text-secondary small">registros com anotações</div></div>
      </div>
    </div>

    <!-- GRÁFICOS -->
    <div class="row g-3 mt-1">
      <div class="col-12 col-xl-8">
        <div class="card-d p-3">
          <h6 class="mb-2">Registros por mês</h6>
          <div id="chartMes" class="chart"></div>
        </div>
      </div>
      <div class="col-12 col-xl-4">
        <div class="card-d p-3">
          <h6 class="mb-2">Distribuição por Diretoria</h6>
          <div id="chartDiretoria" class="chart" style="height: 290px"></div>
        </div>
      </div>
    </div>

    <div class="row g-3 mt-1">
      <div class="col-12">
        <div class="card-d p-3">
          <h6 class="mb-2">Top Colaboradores por quantidade de registros</h6>
          <div id="chartColab" class="chart" style="height:360px"></div>
        </div>
      </div>
    </div>

    <!-- TABELA -->
    <div class="row g-3 mt-1">
      <div class="col-12">
        <div class="card-d p-3">
          <div class="d-flex align-items-center justify-content-between mb-2">
            <div class="d-flex align-items-center gap-2">
              <h6 class="mb-0">Registros (amostra)</h6>
              <label class="text-secondary small">Linhas:</label>
              <select id="perPage" class="form-select form-select-sm"
                      style="width:88px;background:var(--field);border-color:var(--stroke);color:var(--text)">
                <option>10</option><option>25</option><option>50</option>
              </select>
            </div>
            <div id="pager" class="d-flex align-items-center gap-1"></div>
          </div>

          <div class="d-flex align-items-center justify-content-end mb-2">
            <input id="fBuscaTabela" class="form-control form-control-sm"
                   style="width:240px;background:var(--field);border-color:var(--stroke);color:var(--text)"
                   placeholder="Filtrar na tabela...">
          </div>

          <div class="table-responsive">
            <table id="tblAtv" class="table table-dark table-hover align-middle table-sm">
              <thead>
                <tr>
                  <th class="sortable" data-key="id">ID <span class="sort bi bi-arrow-down-up"></span></th>
                  <th class="sortable" data-key="nome">Nome <span class="sort bi bi-arrow-down-up"></span></th>
                  <th class="sortable" data-key="cpf">CPF <span class="sort bi bi-arrow-down-up"></span></th>
                  <th class="sortable" data-key="diretoria">Diretoria <span class="sort bi bi-arrow-down-up"></span></th>
                  <th class="sortable" data-key="data_inicial">Data Inicial <span class="sort bi bi-arrow-down-up"></span></th>
                  <th class="sortable" data-key="data_final">Data Final <span class="sort bi bi-arrow-down-up"></span></th>
                  <th class="sortable" data-key="pontos_relevantes">Pontos Relevantes <span class="sort bi bi-arrow-down-up"></span></th>
                </tr>
              </thead>
              <tbody><!-- render via JS --></tbody>
            </table>
          </div>
          <small class="text-secondary">* Paginação e ordenação no cliente.</small>
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
  <script src="/assets/js/dash_atividades.js" defer></script>
</body>
</html>