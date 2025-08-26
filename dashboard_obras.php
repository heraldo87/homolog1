<?php
// ======================================================
// dashboard_obras.php — COHIDRO AI · Dashboard de Obras
// - Filtros no TOPO (faixa horizontal)
// - Cartões/tabelas com alturas padronizadas
// - Velocímetro centralizado
// - Mantém parciais header/topbar/sidebar
// - Dados fictícios em JS para homologação visual
// ======================================================
$brand       = 'COHIDRO AI';
$page_title  = $brand . ' — Dashboard de Obras';
$active      = 'overview'; // manter consistente com seu index/sidebar
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

  <main id="content" class="content">

    <!-- ======= ESTILOS DE ALTURA PADRÃO ======= -->
    <style>
      /* Alturas uniformes */
      .row-kpis   .card-d { height: 140px; }
      .row-blocos .card-d { height: 180px; }
      .row-tabelas .card-d { height: 380px; }

      /* Áreas com rolagem interna (reserva ~48px do título) */
      .row-tabelas .scroll-area { height: calc(380px - 48px); overflow: auto; }

      /* Velocímetro */
      #gauge { width: 160px; height: 110px; }
      .kpi-gauge { display: flex; align-items: center; gap: 14px; }

      /* Garante que cartões estiquem dentro da coluna */
      .h-100 { height: 100%; }
    </style>

    <!-- FILTROS NO TOPO -->
    <div class="card-d p-3 mb-3">
      <div class="row g-2 align-items-end">
        <div class="col-12 col-md-2">
          <label class="form-label small">Município</label>
          <input id="f-municipio" class="form-control form-control-sm" placeholder="Todos">
        </div>
        <div class="col-12 col-md-2">
          <label class="form-label small">Secretaria</label>
          <input id="f-secretaria" class="form-control form-control-sm" placeholder="Todos">
        </div>
        <div class="col-12 col-md-2">
          <label class="form-label small">Fonte de Recurso</label>
          <input id="f-fonte" class="form-control form-control-sm" placeholder="Todos">
        </div>
        <div class="col-12 col-md-2">
          <label class="form-label small">Obra</label>
          <input id="f-obra" class="form-control form-control-sm" placeholder="Todos">
        </div>
        <div class="col-12 col-md-2">
          <label class="form-label small">Status</label>
          <select id="f-status" class="form-select form-select-sm">
            <option value="">Todos</option>
            <option>Planejada</option>
            <option>Em execução</option>
            <option>Concluída</option>
            <option>Paralisada</option>
          </select>
        </div>
        <div class="col-12 col-md-2 d-flex gap-2">
          <button id="btn-aplicar" class="btn btn-primary btn-sm flex-fill">Aplicar</button>
          <button id="btn-limpar"  class="btn btn-outline-secondary btn-sm flex-fill">Limpar</button>
        </div>
      </div>
    </div>

    <!-- 1ª LINHA: KPIs (altura uniforme) -->
    <div class="row g-3 row-kpis">
      <div class="col-12 col-md-6 col-xl-3">
        <div class="card-d p-3 kpi h-100">
          <div class="label">Valor total da Obra</div>
          <div id="kpi-total" class="value">R$ 0,00</div>
          <div class="text-secondary small">&nbsp;</div>
        </div>
      </div>
      <div class="col-12 col-md-6 col-xl-3">
        <div class="card-d p-3 kpi h-100">
          <div class="label">Realizado</div>
          <div id="kpi-realizado" class="value">R$ 0,00</div>
          <div class="text-secondary small">&nbsp;</div>
        </div>
      </div>
      <div class="col-12 col-md-6 col-xl-3">
        <div class="card-d p-3 kpi h-100">
          <div class="label">Saldo Contratual</div>
          <div id="kpi-saldo" class="value">R$ 0,00</div>
          <div class="text-secondary small">Total − Realizado</div>
        </div>
      </div>
      <div class="col-12 col-md-6 col-xl-3">
        <div class="card-d p-3 kpi h-100">
          <div class="label">Evolução Financeira</div>
          <div class="kpi-gauge">
            <div id="gauge"></div>
            <div>
              <div id="kpi-evolucao" class="value">0%</div>
              <div class="text-secondary small">Realizado / Total</div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- 2ª LINHA: BLOCOS (altura uniforme) -->
    <div class="row g-3 mt-1 row-blocos">
      <div class="col-12 col-xl-3">
        <div class="card-d p-3 h-100">
          <h6 class="mb-2">Previsão de Desembolso</h6>
          <div class="d-flex flex-column gap-2">
            <div>
              <div class="text-secondary small">2025</div>
              <div id="prev-2025" class="h5 mb-0">R$ 0,00</div>
            </div>
            <div>
              <div class="text-secondary small">2026</div>
              <div id="prev-2026" class="h5 mb-0">R$ 0,00</div>
            </div>
          </div>
        </div>
      </div>

      <div class="col-12 col-xl-3">
        <div class="card-d p-3 h-100">
          <h6 class="mb-2">Resolução Conjunta</h6>
          <div class="h5 mb-1" id="rc-total">R$ 0,00</div>
          <div class="text-secondary">CONJUNTA</div>
          <div class="mt-2">
            <div class="text-secondary">SALDO</div>
            <div class="h6 text-warning" id="rc-saldo">R$ 0,00</div>
          </div>
        </div>
      </div>

      <div class="col-12 col-xl-3">
        <div class="card-d p-3 h-100">
          <h6 class="mb-2">Descentralização</h6>
          <div class="h5 mb-1" id="desc-total">R$ 0,00</div>
          <div class="text-secondary">DESCENTRALIZAÇÃO</div>
          <div class="mt-2">
            <div class="text-secondary">SALDO</div>
            <div class="h6 text-warning" id="desc-saldo">R$ 0,00</div>
          </div>
        </div>
      </div>

      <div class="col-12 col-xl-3">
        <div class="card-d p-3 h-100">
          <h6 class="mb-2">Empenho</h6>
          <div class="h5 mb-1" id="emp-total">R$ 0,00</div>
          <div class="text-secondary">EMPENHO</div>
          <div class="mt-2">
            <div class="text-secondary">SALDO</div>
            <div class="h6 text-warning" id="emp-saldo">R$ 0,00</div>
          </div>
        </div>
      </div>
    </div>

    <!-- 3ª LINHA: Empresas x Contrato/Vigência (mesma altura + rolagem) -->
    <div class="row g-3 mt-1 row-tabelas">
      <div class="col-12 col-xl-4">
        <div class="card-d p-3 h-100">
          <h6 class="mb-2">Empresa</h6>
          <div id="lista-empresas" class="scroll-area"></div>
        </div>
      </div>

      <div class="col-12 col-xl-8">
        <div class="card-d p-3 h-100">
          <h6 class="mb-2">Contrato - Vigência</h6>
          <div class="scroll-area">
            <div class="table-responsive">
              <table id="tbl" class="table table-dark table-hover align-middle table-sm mb-0">
                <thead>
                  <tr>
                    <th>OBRA</th>
                    <th>TCT INÍCIO</th>
                    <th>TCT TÉRMINO</th>
                    <th>INÍCIO</th>
                    <th>TÉRMINO</th>
                    <th>OBRA INÍCIO</th>
                    <th>OBRA TÉRMINO</th>
                    <th>GARANTIA</th>
                    <th class="text-end">Valor Total</th>
                    <th class="text-end">Realizado</th>
                  </tr>
                </thead>
                <tbody><!-- preenchido via JS --></tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- 4ª LINHA: Gráficos -->
    <div class="row g-3 mt-1">
      <div class="col-12 col-xl-6">
        <div class="card-d p-3">
          <h6 class="mb-2">Obras por Status</h6>
          <div id="chartPie" class="chart" style="height:300px"></div>
        </div>
      </div>
      <div class="col-12 col-xl-6">
        <div class="card-d p-3">
          <h6 class="mb-2">Realizado por Secretaria (R$)</h6>
          <div id="chartBar" class="chart" style="height:300px"></div>
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

  <script>
// ===============================================
// DADOS FICTÍCIOS (substituir por fonte real depois)
// ===============================================
const OBRAS = [
  {
    obra:'C.E. Maurício Medeiros de Alvarenga - Rio das Ostras',
    empresa:'ANRX Engenharia e Soluções Ltda.',
    municipio:'Rio das Ostras',
    secretaria:'Educação',
    fonte:'Tesouro',
    status:'Em execução',
    tct_inicio:'2023-03-14', tct_termino:'2025-10-24',
    inicio:'2022-06-10', termino:'2025-05-30',
    obra_inicio:'2023-06-23', obra_termino:'2025-12-31',
    garantia:'28/10/2026',
    total: 28500000, realizado: 11000000,
    previsao: {2025: 12000000, 2026: 3600000},
    rc: -46298745.70,
    descent: -50353605.81,
    empenho:  46430382.02
  },
  {
    obra:'Cense São Gonçalo',
    empresa:'APD Construções e Serviços Ltda.',
    municipio:'São Gonçalo',
    secretaria:'Assistência',
    fonte:'Convênio',
    status:'Em execução',
    tct_inicio:'2024-04-04', tct_termino:'2025-10-26',
    inicio:'2022-08-31', termino:'2026-05-25',
    obra_inicio:'2024-09-20', obra_termino:'2025-12-18',
    garantia:'31/08/2026',
    total: 18500000, realizado:  7200000,
    previsao: {2025:  8000000,  2026: 1000000},
    rc: -2000000, descent: -1500000, empenho:  9000000
  },
  {
    obra:'Centro de Mídia (projetos)',
    empresa:'Comercial de Equipamentos CNL Ltda.',
    municipio:'Rio de Janeiro',
    secretaria:'Cultura',
    fonte:'Tesouro',
    status:'Planejada',
    tct_inicio:'2024-05-13', tct_termino:'2025-09-05',
    inicio:'2024-11-10', termino:'2025-10-29',
    obra_inicio:'2025-10-15', obra_termino:'2025-12-31',
    garantia:'12/06/2026',
    total: 32500000, realizado: 16800000,
    previsao: {2025: 12000000, 2026:  2000000},
    rc: -1000000, descent: -500000,  empenho: 15000000
  },
  {
    obra:'CRIAAD Cabo Frio',
    empresa:'Cone Engª e Construção Civil Ltda.',
    municipio:'Cabo Frio',
    secretaria:'Justiça',
    fonte:'Convênio',
    status:'Em execução',
    tct_inicio:'2024-08-19', tct_termino:'2028-12-23',
    inicio:'2024-10-23', termino:'2026-02-26',
    obra_inicio:'2024-12-27', obra_termino:'2026-02-28',
    garantia:'18/03/2027',
    total: 41000000, realizado: 17500000,
    previsao: {2025: 16000000, 2026:  8000000},
    rc: -3500000, descent: -2500000, empenho: 20000000
  },
  {
    obra:'Def. Campos',
    empresa:'CNL Infra Serviços Ltda.',
    municipio:'Campos',
    secretaria:'Saúde',
    fonte:'Tesouro',
    status:'Concluída',
    tct_inicio:'2021-11-18', tct_termino:'2024-06-10',
    inicio:'2022-02-10', termino:'2025-02-19',
    obra_inicio:'2022-04-02', obra_termino:'2025-01-31',
    garantia:'31/01/2026',
    total: 21000000, realizado: 19000000,
    previsao: {2025:  5000000, 2026:   112113},
    rc: -100000, descent: -50000, empenho: 21000000
  },
];

// ===============================================
// UTIL
// ===============================================
const BRL = new Intl.NumberFormat('pt-BR', { style:'currency', currency:'BRL' });
const PCT = new Intl.NumberFormat('pt-BR', { style:'percent', minimumFractionDigits:0, maximumFractionDigits:0 });

const fmtData = d => d ? d.split('-').reverse().join('/') : '';
const soma = (arr, key) => arr.reduce((a,b)=> a + (+b[key]||0), 0);
const uniq = arr => [...new Set(arr)];

function filtrar(rows, f){
  const qMun  = (f.municipio||'').toLowerCase();
  const qSec  = (f.secretaria||'').toLowerCase();
  const qFont = (f.fonte||'').toLowerCase();
  const qObra = (f.obra||'').toLowerCase();
  const qStat = (f.status||'').toLowerCase();

  return rows.filter(r=>{
    const okMun  = !qMun  || (r.municipio||'').toLowerCase().includes(qMun);
    const okSec  = !qSec  || (r.secretaria||'').toLowerCase().includes(qSec);
    const okFont = !qFont || (r.fonte||'').toLowerCase().includes(qFont);
    const okObra = !qObra || (r.obra||'').toLowerCase().includes(qObra);
    const okSta  = !qStat || (r.status||'').toLowerCase().includes(qStat);
    return okMun && okSec && okFont && okObra && okSta;
  });
}

// ===============================================
// KPIs & BLOCOS
// ===============================================
function calcularKPIs(rows){
  const total      = soma(rows,'total');
  const realizado  = soma(rows,'realizado');
  const saldo      = Math.max(total - realizado, 0);
  const evol       = total>0 ? (realizado/total) : 0;

  // Previsões (anos-alvo)
  const somaPrev = (ano)=> rows.reduce((acc,r)=> acc + ((r.previsao?.[ano])||0), 0);

  // Resolução Conjunta / Descentralização (mantém sinal)
  const rcTotal   = rows.reduce((acc,r)=> acc + (+r.rc||0), 0);
  const rcSaldo   = rcTotal;

  const descTotal = rows.reduce((acc,r)=> acc + (+r.descent||0), 0);
  const descSaldo = descTotal;

  // Empenho
  const empTotal  = rows.reduce((acc,r)=> acc + (+r.empenho||0), 0);
  const empSaldo  = Math.max(empTotal - realizado, 0);

  return {
    total, realizado, saldo, evol,
    prev2025: somaPrev(2025),
    prev2026: somaPrev(2026),
    rcTotal, rcSaldo,
    descTotal, descSaldo,
    empTotal, empSaldo
  };
}

function aplicarKPIs(k){
  document.getElementById('kpi-total').textContent     = BRL.format(k.total);
  document.getElementById('kpi-realizado').textContent = BRL.format(k.realizado);
  document.getElementById('kpi-saldo').textContent     = BRL.format(k.saldo);
  document.getElementById('kpi-evolucao').textContent  = PCT.format(k.evol);

  document.getElementById('prev-2025').textContent = BRL.format(k.prev2025);
  document.getElementById('prev-2026').textContent = BRL.format(k.prev2026);

  document.getElementById('rc-total').textContent  = BRL.format(k.rcTotal);
  document.getElementById('rc-saldo').textContent  = BRL.format(k.rcSaldo);

  document.getElementById('desc-total').textContent = BRL.format(k.descTotal);
  document.getElementById('desc-saldo').textContent = BRL.format(k.descSaldo);

  document.getElementById('emp-total').textContent = BRL.format(k.empTotal);
  document.getElementById('emp-saldo').textContent = BRL.format(k.empSaldo);

  // Gauge (velocímetro)
  gauge.setOption({
    series:[{
      type:'gauge',
      startAngle:180, endAngle:0,
      min:0, max:1, splitNumber:4,
      axisLine:{ lineStyle:{ width:10 } },
      pointer:{ show:false },
      progress:{ show:true, width:10 },
      axisTick:{ show:false }, splitLine:{ show:false }, axisLabel:{ show:false },
      detail:{ valueAnimation:true, formatter: (k.evol*100).toFixed(2)+'%' },
      data:[{ value:k.evol }]
    }]
  });
}

// ===============================================
// LISTA EMPRESAS + TABELA
// ===============================================
function preencherEmpresas(rows){
  const cont = document.getElementById('lista-empresas');
  const empresas = uniq(rows.map(r=> r.empresa)).sort((a,b)=> a.localeCompare(b));
  cont.innerHTML = empresas.map(e=> `<div class="py-1 border-bottom border-secondary-subtle">${e||'-'}</div>`).join('');
}

function preencherTabela(rows){
  const tb = document.querySelector('#tbl tbody');
  tb.innerHTML = '';
  rows.forEach(r=>{
    tb.insertAdjacentHTML('beforeend', `
      <tr>
        <td>${r.obra}</td>
        <td>${fmtData(r.tct_inicio)}</td>
        <td>${fmtData(r.tct_termino)}</td>
        <td>${fmtData(r.inicio)}</td>
        <td>${fmtData(r.termino)}</td>
        <td>${fmtData(r.obra_inicio)}</td>
        <td>${fmtData(r.obra_termino)}</td>
        <td>${r.garantia||''}</td>
        <td class="text-end">${BRL.format(r.total)}</td>
        <td class="text-end">${BRL.format(r.realizado)}</td>
      </tr>
    `);
  });
}

// ===============================================
// GRÁFICOS
// ===============================================
let chartPie, chartBar, gauge;

function renderPie(rows){
  const by = {};
  rows.forEach(r=> { by[r.status] = (by[r.status]||0) + 1; });
  const data = Object.entries(by).map(([k,v])=>({name:k||'—', value:v}));

  chartPie.setOption({
    tooltip:{trigger:'item'},
    legend:{top:0},
    series:[{
      type:'pie',
      radius:['45%','70%'],
      label:{show:true, formatter:'{b}: {c}'},
      data
    }]
  });
}

function renderBar(rows){
  // Somar realizado por secretaria
  const agg = {};
  rows.forEach(r=> { agg[r.secretaria] = (agg[r.secretaria]||0) + (+r.realizado||0); });
  const cats = Object.keys(agg);
  const vals = cats.map(k=> agg[k]);

  chartBar.setOption({
    grid:{left:48,right:16,top:24,bottom:32},
    tooltip:{trigger:'axis'},
    xAxis:{type:'category', data:cats, axisTick:{show:false}},
    yAxis:{type:'value', axisLabel:{formatter:(v)=>BRL.format(v)}},
    series:[{type:'bar', data:vals, barWidth:'48%'}]
  });
}

// ===============================================
// BOOT
// ===============================================
let gaugeEl, pieEl, barEl;

document.addEventListener('DOMContentLoaded', ()=>{
  document.getElementById('y').textContent = new Date().getFullYear();

  gaugeEl = document.getElementById('gauge');
  pieEl   = document.getElementById('chartPie');
  barEl   = document.getElementById('chartBar');

  gauge   = echarts.init(gaugeEl);
  chartPie= echarts.init(pieEl);
  chartBar= echarts.init(barEl);

  // Estado atual (após filtros)
  let current = [...OBRAS];

  function recompute(){
    const filtros = {
      municipio: document.getElementById('f-municipio').value,
      secretaria:document.getElementById('f-secretaria').value,
      fonte:     document.getElementById('f-fonte').value,
      obra:      document.getElementById('f-obra').value,
      status:    document.getElementById('f-status').value,
    };
    current = filtrar(OBRAS, filtros);

    aplicarKPIs(calcularKPIs(current));
    preencherEmpresas(current);
    preencherTabela(current);
    renderPie(current);
    renderBar(current);
  }

  // Primeira renderização
  recompute();

  // Botões filtro
  document.getElementById('btn-aplicar').addEventListener('click', recompute);
  document.getElementById('btn-limpar').addEventListener('click', ()=>{
    ['f-municipio','f-secretaria','f-fonte','f-obra'].forEach(id=> document.getElementById(id).value = '');
    document.getElementById('f-status').value = '';
    recompute();
  });

  // Responsivo
  window.addEventListener('resize', ()=>{ gauge.resize(); chartPie.resize(); chartBar.resize(); });
});
  </script>

  <!-- Mantém seu app.js global, se houver -->
  <script src="/assets/js/app.js" defer></script>
</body>
</html>
