// ===== Helpers
function qs(s, r=document){ return r.querySelector(s); }
function qsa(s, r=document){ return Array.from(r.querySelectorAll(s)); }
const fmtNum = new Intl.NumberFormat('pt-BR');

function toDate(str){ const [y,m,d]=str.split('-').map(n=>+n); return new Date(y,m-1,d); }
function diffDias(a,b){ return Math.round((b-a)/86400000); }

// ===== Seed fictício
function seedData(){
  const nomes = ['Ana Costa','Bruno Lima','Carlos Souza','Daniela Mendes','Eduardo Prado','Fernanda Alves','Gustavo Rocha','Helena Duarte','Ivan Silveira','Joana Reis','Karina Matos','Lucas Araujo','Marina Pires','Nicolas Vidal','Olivia Martins'];
  const diretorias = ['DIM','DMA','DPO','DGE'];
  const cidades = ['Niterói','Nova Iguaçu','Campos','Volta Redonda','Petrópolis','Itaperuna','Resende','Cabo Frio'];
  const textos = ['Revisão de projetos','Vistoria técnica','Relatório consolidado','Ajustes de cronograma','Análise de conformidade','Reunião com empreiteira','Levantamento de campo','Integração com orçamento'];
  let id=1, out=[], hoje=new Date();
  for(let m=0;m<12;m++){
    const base=new Date(hoje.getFullYear(),hoje.getMonth()-m,15);
    const registrosMes=6+Math.floor(Math.random()*6);
    for(let i=0;i<registrosMes;i++){
      const di=new Date(base.getFullYear(), base.getMonth(), 1+Math.floor(Math.random()*27));
      const dur=3+Math.floor(Math.random()*20);
      const df=new Date(di.getFullYear(), di.getMonth(), di.getDate()+dur);
      out.push({
        id:id++,
        nome: nomes[Math.floor(Math.random()*nomes.length)],
        cpf: ('00000000000'+Math.floor(Math.random()*99999999999)).slice(-11),
        diretoria: diretorias[Math.floor(Math.random()*diretorias.length)],
        data_inicial: di.toISOString().slice(0,10),
        data_final:   df.toISOString().slice(0,10),
        atividades_realizadas: textos[Math.floor(Math.random()*textos.length)],
        atividades_previstas:  textos[Math.floor(Math.random()*textos.length)],
        pontos_relevantes: Math.random()<0.6 ? (textos[Math.floor(Math.random()*textos.length)]+' — '+cidades[Math.floor(Math.random()*cidades.length)]) : '',
        data_registro: df.toISOString().slice(0,10)
      });
    }
  }
  return out.sort((a,b)=>a.data_registro.localeCompare(b.data_registro));
}

// ===== Estado
const STATE = {
  all: [],
  filtered: [],
  charts: { mes:null, dir:null, colab:null },
  sortKey: 'data_registro',
  sortDir: 'desc',  // 'asc' | 'desc'
  page: 1,
  perPage: 10
};

// ===== Filtros
function applyFilters(){
  const di = qs('#fDataIni').value;
  const df = qs('#fDataFim').value;
  const dir = qs('#fDiretoria').value;
  const q = (qs('#fBusca').value || '').toLowerCase();

  STATE.filtered = STATE.all.filter(r=>{
    if (di && r.data_inicial < di) return false;
    if (df && r.data_final   > df) return false;
    if (dir && r.diretoria !== dir) return false;
    if (q) {
      const hay = (r.nome+' '+r.atividades_realizadas+' '+r.atividades_previstas+' '+r.pontos_relevantes).toLowerCase();
      if (!hay.includes(q)) return false;
    }
    return true;
  });

  // KPIs
  qs('#countTotal').textContent    = STATE.all.length;
  qs('#countFiltered').textContent = STATE.filtered.length;
  qs('#kpiTotal').textContent  = fmtNum.format(STATE.filtered.length);

  const colabs = new Set(STATE.filtered.map(r=>r.nome));
  qs('#kpiColab').textContent = fmtNum.format(colabs.size);

  const dias = STATE.filtered.map(r=>diffDias(toDate(r.data_inicial), toDate(r.data_final)));
  const mediaDias = dias.length ? Math.round(dias.reduce((a,b)=>a+b,0)/dias.length) : 0;
  qs('#kpiDur').textContent = fmtNum.format(mediaDias);

  const comPR = STATE.filtered.filter(r=>(r.pontos_relevantes||'').trim()!=='').length;
  const percPR = STATE.filtered.length ? Math.round((comPR/STATE.filtered.length)*100) : 0;
  qs('#kpiPR').textContent = `${percPR}%`;

  STATE.page = 1; // volta página
  renderCharts();
  renderTable();
}

// ===== Gráficos
function renderCharts(){
  const elMes=qs('#chartMes'), elDir=qs('#chartDiretoria'), elCol=qs('#chartColab');

  if (!STATE.charts.mes) STATE.charts.mes = echarts.init(elMes);
  const porMes={}; STATE.filtered.forEach(r=>{ const d=toDate(r.data_registro); const k=`${d.getFullYear()}-${String(d.getMonth()+1).padStart(2,'0')}`; porMes[k]=(porMes[k]||0)+1; });
  const kMes=Object.keys(porMes).sort(); const vMes=kMes.map(k=>porMes[k]);
  STATE.charts.mes.setOption({ backgroundColor:'transparent', tooltip:{trigger:'axis'},
    grid:{left:40,right:20,top:20,bottom:35},
    xAxis:{type:'category',data:kMes,axisLine:{lineStyle:{color:'#5b6b85'}},axisLabel:{color:'#cbd5e1'}},
    yAxis:{type:'value',axisLabel:{color:'#cbd5e1'},splitLine:{lineStyle:{color:'rgba(255,255,255,.06)'}}},
    series:[{type:'line',smooth:true,areaStyle:{opacity:.15},data:vMes}]
  });

  if (!STATE.charts.dir) STATE.charts.dir = echarts.init(elDir);
  const porDir={}; STATE.filtered.forEach(r=>porDir[r.diretoria]=(porDir[r.diretoria]||0)+1);
  const dataDir=Object.keys(porDir).map(k=>({name:k,value:porDir[k]}));
  STATE.charts.dir.setOption({ backgroundColor:'transparent', tooltip:{trigger:'item'},
    series:[{type:'pie',radius:['40%','70%'],avoidLabelOverlap:false,itemStyle:{borderRadius:8,borderColor:'#0b0f14',borderWidth:2},label:{color:'#e5e7eb'},data:dataDir}]
  });

  if (!STATE.charts.colab) STATE.charts.colab = echarts.init(elCol);
  const porCol={}; STATE.filtered.forEach(r=>porCol[r.nome]=(porCol[r.nome]||0)+1);
  const top=Object.entries(porCol).sort((a,b)=>b[1]-a[1]).slice(0,10);
  STATE.charts.colab.setOption({
    backgroundColor:'transparent', tooltip:{trigger:'axis'},
    grid:{left:140,right:20,top:20,bottom:35},
    xAxis:{type:'value',axisLabel:{color:'#cbd5e1'},splitLine:{lineStyle:{color:'rgba(255,255,255,.06)'}}},
    yAxis:{type:'category',data:top.map(t=>t[0]),axisLabel:{color:'#cbd5e1'}},
    series:[{type:'bar',barWidth:'55%',data:top.map(t=>t[1])}]
  });

  window.addEventListener('resize', ()=>{
    STATE.charts.mes && STATE.charts.mes.resize();
    STATE.charts.dir && STATE.charts.dir.resize();
    STATE.charts.colab && STATE.charts.colab.resize();
  }, { once:true });
}

// ===== Ordenação + Paginação
function compare(a,b,key){
  const isDate = key.includes('data_');
  const isNum  = key === 'id';
  let va=a[key], vb=b[key];
  if (isDate){ va=toDate(va); vb=toDate(vb); return va - vb; }
  if (isNum){ return (+va) - (+vb); }
  return (va||'').toString().localeCompare((vb||'').toString(), 'pt-BR', { sensitivity:'base' });
}

function renderPager(total){
  const pages = Math.max(1, Math.ceil(total / STATE.perPage));
  STATE.page = Math.min(STATE.page, pages);
  const p = STATE.page;

  const cont = qs('#pager');
  cont.innerHTML = '';

  const mk = (label, disabled, goTo) => {
    const b = document.createElement('button');
    b.className = 'btn btn-sm ' + (disabled ? 'btn-outline-secondary disabled' : 'btn-outline-secondary');
    b.textContent = label;
    if (!disabled) b.addEventListener('click', ()=>{ STATE.page = goTo; renderTable(); });
    cont.appendChild(b);
  };

  mk('«', p===1, 1);
  mk('‹', p===1, p-1);

  const start = Math.max(1, p-2);
  const end   = Math.min(pages, p+2);
  for (let i=start;i<=end;i++){
    const b = document.createElement('button');
    b.className = 'btn btn-sm ' + (i===p ? 'btn-secondary' : 'btn-outline-secondary');
    b.textContent = i;
    if (i!==p) b.addEventListener('click', ()=>{ STATE.page=i; renderTable(); });
    cont.appendChild(b);
  }

  mk('›', p===pages, p+1);
  mk('»', p===pages, pages);
}

function updateSortIcons(){
  qsa('#tblAtv thead th.sortable').forEach(th=>{
    th.classList.remove('th-active');
    const icon = th.querySelector('.sort');
    const key = th.dataset.key;
    let cls = 'bi-arrow-down-up';
    if (STATE.sortKey === key) {
      th.classList.add('th-active');
      cls = (STATE.sortDir === 'asc') ? 'bi-caret-up-fill' : 'bi-caret-down-fill';
    }
    icon.className = 'sort bi ' + cls;
  });
}

function renderTable(){
  const q = (qs('#fBuscaTabela').value || '').toLowerCase();

  const rows = STATE.filtered.slice().sort((a,b)=>{
    const s = compare(a,b,STATE.sortKey);
    return STATE.sortDir==='asc' ? s : -s;
  }).filter(r=>{
    if (!q) return true;
    const hay = (r.id+' '+r.nome+' '+r.cpf+' '+r.diretoria+' '+r.pontos_relevantes).toLowerCase();
    return hay.includes(q);
  });

  const total = rows.length;
  renderPager(total);
  const start = (STATE.page-1) * STATE.perPage;
  const pageRows = rows.slice(start, start + STATE.perPage);

  const tbody = qs('#tblAtv tbody');
  tbody.innerHTML = pageRows.map(r=>`
    <tr>
      <td>${r.id}</td>
      <td>${r.nome}</td>
      <td>${r.cpf}</td>
      <td>${r.diretoria}</td>
      <td>${r.data_inicial}</td>
      <td>${r.data_final}</td>
      <td>${(r.pontos_relevantes||'').slice(0,80)}${(r.pontos_relevantes||'').length>80?'…':''}</td>
    </tr>
  `).join('') || `<tr><td colspan="7" class="text-center text-secondary">Sem registros.</td></tr>`;

  updateSortIcons();
}

// ===== Export CSV
function exportCSV(){
  const headers = ['id','nome','cpf','diretoria','data_inicial','data_final','atividades_realizadas','atividades_previstas','pontos_relevantes','data_registro'];
  const lines = [headers.join(';')];
  STATE.filtered.forEach(r=>{
    const row = headers.map(h=>{
      const v = (r[h] ?? '').toString().replace(/;/g, ',').replace(/\n/g,' ');
      return `"${v.replace(/"/g,'""')}"`;
    }).join(';');
    lines.push(row);
  });
  const blob = new Blob([lines.join('\n')], {type:'text/csv;charset=utf-8;'});
  const url = URL.createObjectURL(blob);
  const a = document.createElement('a'); a.href=url; a.download='atividades_filtrado.csv';
  document.body.appendChild(a); a.click(); document.body.removeChild(a); URL.revokeObjectURL(url);
}

// ===== Init
document.addEventListener('DOMContentLoaded', ()=>{
  STATE.all = seedData();

  // filtros
  ['#fDataIni','#fDataFim','#fDiretoria','#fBusca'].forEach(sel=>{
    qs(sel).addEventListener('input', applyFilters);
    qs(sel).addEventListener('change', applyFilters);
  });
  qs('#btnLimpar').addEventListener('click', ()=>{
    qs('#fDataIni').value=''; qs('#fDataFim').value=''; qs('#fDiretoria').value=''; qs('#fBusca').value='';
    qs('#fBuscaTabela').value='';
    STATE.page = 1;
    applyFilters();
  });

  // tabela: busca, perPage, sort
  qs('#fBuscaTabela').addEventListener('input', ()=>{ STATE.page=1; renderTable(); });
  qs('#perPage').addEventListener('change', (e)=>{ STATE.perPage = parseInt(e.target.value,10) || 10; STATE.page=1; renderTable(); });

  qsa('#tblAtv thead th.sortable').forEach(th=>{
    th.addEventListener('click', ()=>{
      const key = th.dataset.key;
      if (STATE.sortKey === key) {
        STATE.sortDir = (STATE.sortDir === 'asc') ? 'desc' : 'asc';
      } else {
        STATE.sortKey = key;
        STATE.sortDir = (key==='id' || key.includes('data_')) ? 'desc' : 'asc';
      }
      STATE.page = 1;
      renderTable();
    });
  });

  qs('#btnExport').addEventListener('click', exportCSV);

  // inicial
  applyFilters();
  updateSortIcons();
});
