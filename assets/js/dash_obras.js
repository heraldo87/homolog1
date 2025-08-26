// =================== Helpers ===================
const qs  = (s,r=document)=>r.querySelector(s);
const qsa = (s,r=document)=>Array.from(r.querySelectorAll(s));
const fmtBRL = new Intl.NumberFormat('pt-BR',{style:'currency',currency:'BRL'});

function toDate(s){ const [y,m,d]=s.split('-').map(n=>+n); return new Date(y,m-1,d); }
function addDays(d,n){ const p=new Date(d); p.setDate(p.getDate()+n); return p; }
function clamp(v,min,max){ return Math.max(min, Math.min(max, v)); }

// =================== Seed fictício ===================
function seedObrasFromAtividades(){
  const nomes = ['Ana Costa','Bruno Lima','Carlos Souza','Daniela Mendes','Eduardo Prado','Fernanda Alves','Gustavo Rocha','Helena Duarte','Ivan Silveira','Joana Reis','Karina Matos','Lucas Araujo','Marina Pires','Nicolas Vidal','Olivia Martins'];
  const diretorias = ['DIM','DMA','DPO','DGE'];
  const empresas = ['ANRX Engenharia e Soluções Ltda.','APD Construções e Serviços Ltda.','Comercial de Equipamentos CNL Ltda','Cone Engª e Construção Civil Ltda.','Enge Prat Engenharia e Serviços Ltda','EngeNorte Empreendimentos e Serviços Ltda.','FB Chaves Construções Ltda.','Alpha Eng.','Beta Construtora','RJ Obras','Construsul','Obras RJ'];
  const obrasNames = ['Revitalização de Escola','Reforma UPA','Urbanização Bairro','Construção de CIEP','Recuperação de Ponte','Drenagem Av. Central','Requalificação Praça','Ampliação Hospital'];
  const textos = ['Revisão de projetos','Vistoria técnica','Relatório consolidado','Ajustes de cronograma','Análise de conformidade','Reunião com empreiteira','Levantamento de campo','Integração com orçamento'];

  let id=1, out=[], hoje=new Date();
  for (let m=0;m<12;m++){
    const base=new Date(hoje.getFullYear(),hoje.getMonth()-m,15);
    const registrosMes=6+Math.floor(Math.random()*6);
    for(let i=0;i<registrosMes;i++){
      const di=new Date(base.getFullYear(), base.getMonth(), 1+Math.floor(Math.random()*27));
      const dur=30+Math.floor(Math.random()*280);
      const df=addDays(di, dur);

      const valor = 5_000_000 + Math.floor(Math.random()*45_000_000);
      const realizado = Math.floor(valor * (0.25 + Math.random()*0.65));
      const empenho = Math.floor(valor * (0.2 + Math.random()*0.4));
      const resolucao = Math.floor(valor * (0.05 + Math.random()*0.2));
      const descent = Math.floor(valor * (0.05 + Math.random()*0.2));

      const ano1 = hoje.getFullYear();
      const ano2 = ano1 + 1;
      const prev1 = Math.floor(valor * (0.15 + Math.random()*0.25));
      const prev2 = Math.floor(valor * (0.05 + Math.random()*0.20));

      const obra = obrasNames[Math.floor(Math.random()*obrasNames.length)] + ' — ' + ['Niterói','Campos','Petrópolis','Volta Redonda','Nova Iguaçu','Cabo Frio'][Math.floor(Math.random()*6)];
      const empresa = empresas[Math.floor(Math.random()*empresas.length)];

      out.push({
        id: id++,
        nome: nomes[Math.floor(Math.random()*nomes.length)],
        cpf: ('00000000000'+Math.floor(Math.random()*99999999999)).slice(-11),
        diretoria: diretorias[Math.floor(Math.random()*diretorias.length)],
        data_inicial: di.toISOString().slice(0,10),
        data_final:   df.toISOString().slice(0,10),
        atividades_realizadas: textos[Math.floor(Math.random()*textos.length)],
        atividades_previstas:  textos[Math.floor(Math.random()*textos.length)],
        pontos_relevantes: textos[Math.floor(Math.random()*textos.length)],
        data_registro: df.toISOString().slice(0,10),

        obra, empresa, valor_total: valor, realizado, empenho,
        resolucao_conjunta: resolucao, descentralizacao: descent,
        prev_ano1: { ano: ano1, valor: prev1 },
        prev_ano2: { ano: ano2, valor: prev2 },

        tct_inicio: di.toISOString().slice(0,10),
        tct_termino: addDays(di, clamp(dur-20, 60, 540)).toISOString().slice(0,10),
        inicio: di.toISOString().slice(0,10),
        termino: df.toISOString().slice(0,10),
        obra_inicio: addDays(di, 10).toISOString().slice(0,10),
        obra_termino: addDays(df, -10).toISOString().slice(0,10),
        garantia_meses: [12, 18, 24, 36][Math.floor(Math.random()*4)]
      });
    }
  }
  return out.sort((a,b)=>a.data_registro.localeCompare(b.data_registro));
}

// =================== Estado ===================
const S = { all:[], filtered:[], charts:{ gauge:null }, sortKey:'termino', sortDir:'desc', page:1, perPage:10 };

// =================== Gauge seguro ===================
function renderGauge(ratio){
  const el = qs('#gaugeEvo');
  if (!el) return;

  if (!window.echarts) {
    // fallback textual
    el.innerHTML = `<div class="text-center fw-bold">${(ratio*100).toFixed(2)}%</div>`;
    return;
  }
  if (!S.charts.gauge) S.charts.gauge = echarts.init(el);
  S.charts.gauge.setOption({
    backgroundColor:'transparent',
    series: [{ type:'gauge', startAngle:180, endAngle:0, min:0, max:1,
      progress:{show:true,width:14}, axisLine:{lineStyle:{width:14}},
      axisTick:{show:false}, splitLine:{show:false}, axisLabel:{show:false},
      pointer:{show:false}, anchor:{show:false},
      detail:{ valueAnimation:true, formatter:(v)=>(v*100).toFixed(2)+'%', color:'#e5e7eb', fontSize:18 },
      data:[{value:ratio}]
    }]
  });
  window.__charts = window.__charts || [];
  if (!window.__charts.includes(S.charts.gauge)) window.__charts.push(S.charts.gauge);
  window.addEventListener('resize', ()=>S.charts.gauge && S.charts.gauge.resize(), {once:true});
}

// =================== Empresas ===================
function renderEmpresas(){
  const counts = {};
  S.filtered.forEach(r=>counts[r.empresa]=(counts[r.empresa]||0)+1);
  const arr = Object.entries(counts).sort((a,b)=>b[1]-a[1]);
  qs('#listaEmpresas').innerHTML = arr.map(([nome,qt])=>`
    <div class="d-flex justify-content-between border-bottom border-secondary-subtle py-1">
      <span>${nome}</span><span class="text-secondary">${qt}</span>
    </div>
  `).join('') || '<div class="text-secondary">Sem dados para o filtro.</div>';
}

// =================== Filtros / KPIs ===================
function applyFilters(){
  const di = qs('#fDataIni')?.value || '';
  const df = qs('#fDataFim')?.value || '';
  const dir = qs('#fDiretoria')?.value || '';
  const q = (qs('#fBusca')?.value || '').toLowerCase();

  S.filtered = S.all.filter(r=>{
    if (di && r.data_inicial < di) return false;
    if (df && r.data_final   > df) return false;
    if (dir && r.diretoria !== dir) return false;
    if (q) {
      const hay = (r.obra+' '+r.empresa+' '+r.pontos_relevantes).toLowerCase();
      if (!hay.includes(q)) return false;
    }
    return true;
  });

  const totValor = S.filtered.reduce((a,r)=>a+r.valor_total,0);
  const totReal  = S.filtered.reduce((a,r)=>a+r.realizado,0);
  const saldo    = Math.max(0, totValor - totReal);

  qs('#countTotal')      && (qs('#countTotal').textContent = S.all.length);
  qs('#countFiltered')   && (qs('#countFiltered').textContent = S.filtered.length);
  qs('#kpiValor')        && (qs('#kpiValor').textContent = fmtBRL.format(totValor));
  qs('#kpiRealizado')    && (qs('#kpiRealizado').textContent = fmtBRL.format(totReal));
  qs('#kpiSaldo')        && (qs('#kpiSaldo').textContent = fmtBRL.format(saldo));

  renderGauge(totValor ? (totReal/totValor) : 0);

  const prev1 = S.filtered.reduce((a,r)=>a+(r.prev_ano1?.valor||0),0);
  const prev2 = S.filtered.reduce((a,r)=>a+(r.prev_ano2?.valor||0),0);
  const ano1 = S.filtered[0]?.prev_ano1?.ano || new Date().getFullYear();
  const ano2 = ano1 + 1;
  qs('#prevAno1Label') && (qs('#prevAno1Label').textContent = ano1);
  qs('#prevAno2Label') && (qs('#prevAno2Label').textContent = ano2);
  qs('#prevAno1')      && (qs('#prevAno1').textContent = fmtBRL.format(prev1));
  qs('#prevAno2')      && (qs('#prevAno2').textContent = fmtBRL.format(prev2));

  const res = S.filtered.reduce((a,r)=>a+r.resolucao_conjunta,0);
  const des = S.filtered.reduce((a,r)=>a+r.descentralizacao,0);
  const emp = S.filtered.reduce((a,r)=>a+r.empenho,0);
  qs('#kpiResolucao')      && (qs('#kpiResolucao').textContent = fmtBRL.format(res));
  qs('#kpiResolucaoSaldo') && (qs('#kpiResolucaoSaldo').textContent = fmtBRL.format(Math.max(0, totValor - res)));
  qs('#kpiDesc')           && (qs('#kpiDesc').textContent = fmtBRL.format(des));
  qs('#kpiDescSaldo')      && (qs('#kpiDescSaldo').textContent = fmtBRL.format(Math.max(0, totValor - des)));
  qs('#kpiEmpenho')        && (qs('#kpiEmpenho').textContent = fmtBRL.format(emp));
  qs('#kpiEmpenhoSaldo')   && (qs('#kpiEmpenhoSaldo').textContent = fmtBRL.format(Math.max(0, totValor - emp)));

  renderEmpresas();
  S.page = 1;
  renderTable();
}

// =================== Tabela (ordenação + paginação) ===================
function compare(a,b,key){
  const isDate = /inicio|termino/.test(key) || key.startsWith('tct_') || key.startsWith('obra_');
  if (isDate) return toDate(a[key]) - toDate(b[key]);
  if (key === 'garantia_meses') return a[key] - b[key];
  return (a[key]||'').toString().localeCompare((b[key]||'').toString(), 'pt-BR', {sensitivity:'base'});
}
function renderPager(total){
  const pages = Math.max(1, Math.ceil(total / S.perPage));
  S.page = Math.min(S.page, pages);
  const p = S.page, el = qs('#pager'); if (!el) return;
  el.innerHTML='';
  const mk=(t,dis,goto)=>{ const b=document.createElement('button'); b.className='btn btn-sm '+(dis?'btn-outline-secondary disabled':'btn-outline-secondary'); b.textContent=t; if(!dis) b.addEventListener('click',()=>{S.page=goto;renderTable();}); el.appendChild(b); };
  mk('«', p===1, 1); mk('‹', p===1, p-1);
  const st=Math.max(1,p-2), en=Math.min(pages,p+2);
  for(let i=st;i<=en;i++){ const b=document.createElement('button'); b.className='btn btn-sm '+(i===p?'btn-secondary':'btn-outline-secondary'); b.textContent=i; if(i!==p) b.addEventListener('click',()=>{S.page=i;renderTable();}); el.appendChild(b); }
  mk('›', p===pages, p+1); mk('»', p===pages, pages);
}
function updateSortIcons(){
  qsa('#tblObras thead th.sortable').forEach(th=>{
    th.classList.remove('th-active');
    const icon = th.querySelector('.sort');
    const key  = th.dataset.key;
    let cls = 'bi-arrow-down-up';
    if (S.sortKey === key) { th.classList.add('th-active'); cls = (S.sortDir==='asc'?'bi-caret-up-fill':'bi-caret-down-fill'); }
    icon.className = 'sort bi ' + cls;
  });
}
function renderTable(){
  const tb = qs('#tblObras tbody'); if (!tb) return;
  const q = (qs('#fBuscaTabela')?.value||'').toLowerCase();

  const rows = S.filtered.slice()
    .filter(r=>{
      if (!q) return true;
      const hay = (r.obra+' '+r.empresa+' '+r.diretoria).toLowerCase();
      return hay.includes(q);
    })
    .sort((a,b)=>{ const s=compare(a,b,S.sortKey); return S.sortDir==='asc'?s:-s; });

  const total = rows.length;
  renderPager(total);
  const start = (S.page-1)*S.perPage;
  const pageRows = rows.slice(start, start + S.perPage);

  tb.innerHTML = pageRows.map(r=>`
    <tr>
      <td>${r.obra}</td>
      <td>${r.tct_inicio}</td>
      <td>${r.tct_termino}</td>
      <td>${r.inicio}</td>
      <td>${r.termino}</td>
      <td>${r.obra_inicio}</td>
      <td>${r.obra_termino}</td>
      <td>${r.garantia_meses} meses</td>
    </tr>
  `).join('') || `<tr><td colspan="8" class="text-center text-secondary">Sem registros.</td></tr>`;

  updateSortIcons();
}

// =================== Init ===================
document.addEventListener('DOMContentLoaded', ()=>{
  // garante que estamos na tela
  if (!qs('#gaugeEvo') || !qs('#tblObras')) {
    console.warn('dash_obras.js: elementos da tela não encontrados — script ignorado.');
    return;
  }

  S.all = seedObrasFromAtividades();

  ['#fDataIni','#fDataFim','#fDiretoria','#fBusca'].forEach(sel=>{
    const el = qs(sel); if (el){ el.addEventListener('input', applyFilters); el.addEventListener('change', applyFilters); }
  });
  const per = qs('#perPage'); if (per) per.addEventListener('change', e=>{ S.perPage=parseInt(e.target.value,10)||10; S.page=1; renderTable(); });
  const btb = qs('#fBuscaTabela'); if (btb) btb.addEventListener('input', ()=>{ S.page=1; renderTable(); });

  qsa('#tblObras thead th.sortable').forEach(th=>{
    th.addEventListener('click', ()=>{
      const key = th.dataset.key;
      if (S.sortKey === key) S.sortDir = (S.sortDir==='asc') ? 'desc' : 'asc';
      else { S.sortKey = key; S.sortDir = key.includes('termino') || key.includes('inicio') ? 'desc' : 'asc'; }
      S.page=1; renderTable();
    });
  });

  const limpar = qs('#btnLimpar');
  if (limpar) limpar.addEventListener('click', ()=>{
    ['#fDataIni','#fDataFim','#fDiretoria','#fBusca','#fBuscaTabela'].forEach(sel=>{ const el=qs(sel); if(el) el.value=''; });
    S.page=1; applyFilters();
  });

  const exp = qs('#btnExport'); if (exp) exp.addEventListener('click', ()=>{
    const headers = ['obra','empresa','diretoria','valor_total','realizado','tct_inicio','tct_termino','inicio','termino','obra_inicio','obra_termino','garantia_meses'];
    const lines = [headers.join(';')];
    S.filtered.forEach(r=>{
      const row = headers.map(h=>{
        const v = (r[h] ?? '').toString().replace(/;/g, ',').replace(/\n/g,' ');
        return `"${v.replace(/"/g,'""')}"`;
      }).join(';');
      lines.push(row);
    });
    const blob = new Blob([lines.join('\n')], {type:'text/csv;charset=utf-8;'});
    const url = URL.createObjectURL(blob);
    const a = document.createElement('a'); a.href=url; a.download='obras_filtrado.csv';
    document.body.appendChild(a); a.click(); document.body.removeChild(a); URL.revokeObjectURL(url);
  });

  // primeira renderização
  applyFilters();
});
