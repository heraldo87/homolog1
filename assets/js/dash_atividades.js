// assets/js/dash_atividades.js — versão robusta (API relativa + debug)

(function(){
  const $ = (sel, el=document) => el.querySelector(sel);
  const $$ = (sel, el=document) => Array.from(el.querySelectorAll(sel));

  // --- DEBUG BAR (opcional, mas ajuda muito) ---
  let debugBar = document.createElement('div');
  debugBar.style.cssText = 'position:fixed;bottom:8px;left:8px;background:#2a1114;color:#fecaca;border:1px solid #7f1d1d;border-radius:8px;padding:6px 10px;font:12px/1.3 system-ui;display:none;z-index:9999';
  debugBar.id = 'dbgApi';
  document.body.appendChild(debugBar);
  function showErr(msg){ debugBar.textContent = msg; debugBar.style.display='block'; }

  // Elementos
  const fDataIni = $('#fDataIni');
  const fDataFim = $('#fDataFim');
  const fDiret   = $('#fDiretoria');
  const fBusca   = $('#fBusca');
  const btnLimpar= $('#btnLimpar');
  const btnExport= $('#btnExport');
  const perPage  = $('#perPage');
  const pager    = $('#pager');
  const kpiTotal = $('#kpiTotal');
  const kpiColab = $('#kpiColab');
  const kpiDur   = $('#kpiDur');
  const kpiPR    = $('#kpiPR');

  const tbl      = $('#tblAtv');
  const tbody    = $('#tblAtv tbody');
  const fBuscaTb = $('#fBuscaTabela');

  const countFiltered = $('#countFiltered');
  const countTotal    = $('#countTotal');
  $('#y').textContent = new Date().getFullYear();

  // --- API: use caminho RELATIVO à página, não raiz do domínio ---
  const API_BASE = new URL('api/atividades_data.php', document.baseURI);

  // Estado
  let state = {
    page: 1,
    per: parseInt(perPage?.value || '10', 10) || 10,
    sort: 'id',
    order: 'desc',
    cacheRows: [],
  };

  // ECharts
  let chartMes = echarts.init($('#chartMes'));
  let chartDir = echarts.init($('#chartDiretoria'));
  let chartCol = echarts.init($('#chartColab'));

  function buildURL(extra={}) {
    const u = new URL(API_BASE.href);
    const params = {
      start: fDataIni?.value || '',
      end:   fDataFim?.value || '',
      dir:   fDiret?.value || '',
      q:     fBusca?.value || '',
      page:  state.page,
      per:   state.per,
      sort:  state.sort,
      order: state.order
    };
    Object.assign(params, extra);
    Object.entries(params).forEach(([k,v]) => { if (v!=='' && v!==null && v!==undefined) u.searchParams.set(k, v); });
    return u;
  }

  async function loadData() {
    try {
      const url = buildURL();
      const res = await fetch(url, { cache: 'no-store' });

      // Se a API estiver protegida e sem sessão, pode vir HTML (login) em vez de JSON:
      const ct = res.headers.get('content-type') || '';
      if (!res.ok) throw new Error(`HTTP ${res.status} ao carregar ${url.pathname}`);
      if (!ct.includes('application/json')) {
        const txt = await res.text();
        throw new Error(`Retorno não-JSON da API (${url.pathname}). Conteúdo inicial: ${txt.slice(0,120).replace(/\s+/g,' ')}`);
      }

      const json = await res.json();
      render(json);
      debugBar.style.display='none'; // ok
    } catch (e) {
      console.error(e);
      showErr(`Falha na API: ${e.message} · API usada: ${API_BASE.pathname}`);
      // Zera visuais para não parecer "aleatório"
      kpiTotal.textContent='0'; kpiColab.textContent='0'; kpiDur.textContent='0'; kpiPR.textContent='0%';
      countFiltered.textContent='0'; countTotal.textContent='0';
      tbody.innerHTML = '';
      chartMes.clear(); chartDir.clear(); chartCol.clear();
    }
  }

  function render(json) {
    countTotal.textContent    = json.total;
    countFiltered.textContent = json.filtered;
    kpiTotal.textContent = json.kpis.total ?? 0;
    kpiColab.textContent = json.kpis.colab ?? 0;
    kpiDur.textContent   = json.kpis.dur ?? 0;
    kpiPR.textContent    = (json.kpis.pr ?? 0) + '%';

    state.cacheRows = json.rows || [];
    renderTable(state.cacheRows);
    renderPager(json.filtered, json.page, json.per);
    drawMes(json.series.mes || []);
    drawDir(json.series.diretoria || []);
    drawCol(json.series.colab || []);
  }

  function renderTable(rows) {
    const qtb = (fBuscaTb?.value || '').toLowerCase().trim();
    let filtered = rows;
    if (qtb) {
      filtered = rows.filter(r =>
        (r.id+'').includes(qtb) ||
        (r.nome||'').toLowerCase().includes(qtb) ||
        (r.cpf||'').toLowerCase().includes(qtb) ||
        (r.diretoria||'').toLowerCase().includes(qtb) ||
        (r.pontos_relevantes||'').toLowerCase().includes(qtb)
      );
    }
    tbody.innerHTML = filtered.map(r => `
      <tr>
        <td>${r.id}</td>
        <td>${escapeHtml(r.nome||'')}</td>
        <td>${escapeHtml(r.cpf||'')}</td>
        <td>${escapeHtml(r.diretoria||'')}</td>
        <td>${safeDate(r.data_inicial)}</td>
        <td>${safeDate(r.data_final)}</td>
        <td>${escapeHtml(shorten(r.pontos_relevantes||'', 100))}</td>
      </tr>
    `).join('');
  }

  function renderPager(totalFiltered, page, per) {
    const pages = Math.max(1, Math.ceil(totalFiltered / per));
    pager.innerHTML = '';
    const mkBtn = (label, p, disabled=false, active=false) => {
      const b = document.createElement('button');
      b.className = 'btn btn-sm ' + (active ? 'btn-primary' : 'btn-outline-secondary');
      b.textContent = label;
      b.disabled = disabled;
      b.addEventListener('click', () => { state.page = p; loadData().catch(console.error); });
      return b;
    };
    pager.appendChild(mkBtn('«', 1, page===1));
    pager.appendChild(mkBtn('‹', Math.max(1, page-1), page===1));
    const span = document.createElement('span');
    span.className = 'text-secondary small mx-2';
    span.textContent = `Página ${page} de ${pages}`;
    pager.appendChild(span);
    pager.appendChild(mkBtn('›', Math.min(pages, page+1), page===pages));
    pager.appendChild(mkBtn('»', pages, page===pages));
  }

  function drawMes(data) {
    chartMes.setOption({
      tooltip: { trigger: 'axis' },
      grid: { left: 40, right: 20, top: 20, bottom: 40 },
      xAxis: { type: 'category', data: data.map(d => d.ym) },
      yAxis: { type: 'value' },
      series: [{ type: 'bar', data: data.map(d => d.c) }]
    });
  }

  function drawDir(data) {
    chartDir.setOption({
      tooltip: { trigger: 'item' },
      series: [{
        type: 'pie',
        radius: ['50%', '75%'],
        data: data.map(d => ({ name: d.diretoria || '—', value: d.c })),
        label: { formatter: '{b}: {c}' }
      }]
    });
  }

  function drawCol(data) {
    chartCol.setOption({
      tooltip: { trigger: 'axis' },
      grid: { left: 120, right: 20, top: 20, bottom: 40 },
      xAxis: { type: 'value' },
      yAxis: { type: 'category', data: data.map(d => d.nome) },
      series: [{ type: 'bar', data: data.map(d => d.c) }]
    });
  }

  // utils
  function escapeHtml(s){ return (s||'').replace(/[&<>"']/g, m => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'}[m])); }
  function shorten(s, n){ return s.length>n ? s.slice(0,n-1)+'…' : s; }
  function safeDate(s){ return (s||'').slice(0,10); }

  // eventos
  $$('#tblAtv thead th.sortable').forEach(th => {
    th.addEventListener('click', () => {
      const key = th.getAttribute('data-key');
      if (state.sort === key) {
        state.order = (state.order === 'asc') ? 'desc' : 'asc';
      } else {
        state.sort = key; state.order = 'asc';
      }
      $$('#tblAtv thead th').forEach(t => t.classList.remove('th-active'));
      th.classList.add('th-active');
      state.page = 1;
      loadData().catch(console.error);
    });
  });

  [fDataIni, fDataFim, fDiret, fBusca].forEach(el => {
    if (!el) return;
    el.addEventListener('change', () => { state.page = 1; loadData().catch(console.error); });
    el.addEventListener('keyup',  (e) => { if (e.key === 'Enter') { state.page = 1; loadData().catch(console.error); }});
  });

  if (perPage) perPage.addEventListener('change', () => { state.per = parseInt(perPage.value,10)||10; state.page=1; loadData().catch(console.error); });
  if (fBuscaTb) fBuscaTb.addEventListener('input', () => { renderTable(state.cacheRows); });
  if (btnLimpar) btnLimpar.addEventListener('click', () => {
    if (fDataIni) fDataIni.value=''; if (fDataFim) fDataFim.value=''; if (fDiret) fDiret.value=''; if (fBusca) fBusca.value='';
    state.page = 1; loadData().catch(console.error);
  });

  btnExport?.addEventListener('click', async () => {
    try {
      const url = buildURL({ page:1, per: 50000 });
      const res = await fetch(url, { cache: 'no-store' });
      if (!res.ok) throw new Error(`HTTP ${res.status} export`);
      const json = await res.json();
      const rows = json.rows || [];
      const csv = [
        ['ID','Nome','CPF','Diretoria','Data Inicial','Data Final','Pontos Relevantes'].join(';'),
        ...rows.map(r => [
          r.id,
          `"${(r.nome||'').replace(/"/g,'""')}"`,
          r.cpf||'',
          r.diretoria||'',
          safeDate(r.data_inicial),
          safeDate(r.data_final),
          `"${(r.pontos_relevantes||'').replace(/"/g,'""').replace(/\r?\n/g,' ')}"`
        ].join(';'))
      ].join('\n');

      const blob = new Blob([csv], {type:'text/csv;charset=utf-8;'});
      const a = document.createElement('a');
      a.href = URL.createObjectURL(blob);
      a.download = 'acompanhamento_atividades.csv';
      document.body.appendChild(a); a.click(); a.remove();
    } catch (e) {
      console.error(e); showErr('Falha ao exportar CSV: ' + e.message);
    }
  });

  loadData().catch(console.error);
})();
