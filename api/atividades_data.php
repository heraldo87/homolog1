<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../php/conn.php'; // <-- usa seu banco de PRODUÇÃO

function only_digits(?string $s): string { return preg_replace('/\D+/', '', $s ?? ''); }
function param($k,$d=''){ return isset($_GET[$k]) ? trim((string)$_GET[$k]) : $d; }

$start = param('start',''); $end = param('end',''); $dir = param('dir',''); $q = param('q','');
$page = max(1,(int)param('page','1')); $per = (int)param('per','10');
if(!in_array($per,[10,25,50],true)) $per=10;
$sort = param('sort','id'); $valid=['id','nome','cpf','diretoria','data_inicial','data_final','pontos_relevantes'];
if(!in_array($sort,$valid,true)) $sort='id';
$order = strtolower(param('order','desc'))==='asc'?'ASC':'DESC';
$offset = ($page-1)*$per;

// WHERE dinâmico
$where=[]; $bind=[]; $types='';
if($start!=='' && preg_match('/^\d{4}-\d{2}-\d{2}$/',$start)){ $where[]='data_inicial >= ?'; $bind[]=$start; $types.='s'; }
if($end  !=='' && preg_match('/^\d{4}-\d{2}-\d{2}$/',$end  )){ $where[]='data_final   <= ?'; $bind[]=$end;   $types.='s'; }
if($dir  !==''){ $where[]='diretoria = ?'; $bind[]=$dir; $types.='s'; }
if($q    !==''){
  $where[]='(nome LIKE ? OR cpf LIKE ? OR atividades_realizadas LIKE ? OR atividades_previstas LIKE ? OR pontos_relevantes LIKE ?)';
  $qq='%'.$q.'%'; for($i=0;$i<5;$i++){ $bind[]=$qq; $types.='s'; }
}
$whereSql = $where ? 'WHERE '.implode(' AND ',$where) : '';

// contadores
$total = (int)($conn->query("SELECT COUNT(*) c FROM acompanhamento_atividades")->fetch_assoc()['c'] ?? 0);
$countSql = "SELECT COUNT(*) c FROM acompanhamento_atividades $whereSql";
$countStmt = $conn->prepare($countSql); if($types) $countStmt->bind_param($types,...$bind);
$countStmt->execute(); $filtered=(int)($countStmt->get_result()->fetch_assoc()['c'] ?? 0); $countStmt->close();

// linhas
$listSql = "SELECT id,nome,cpf,diretoria,data_inicial,data_final,pontos_relevantes,data_registro
            FROM acompanhamento_atividades
            $whereSql ORDER BY $sort $order LIMIT ? OFFSET ?";
$listStmt = $conn->prepare($listSql);
$t = $types.'ii'; $b=$bind; $b[]=$per; $b[]=$offset;
$listStmt->bind_param($t, ...$b);
$listStmt->execute();
$rs = $listStmt->get_result();
$rows=[];
while($r=$rs->fetch_assoc()){
  $d=preg_replace('/\D+/','',$r['cpf']??'');
  if(strlen($d)===11) $r['cpf']=substr($d,0,3).'.'.substr($d,3,3).'.'.substr($d,6,3).'-'.substr($d,9,2);
  $rows[]=$r;
}
$listStmt->close();

// KPIs
$kpiColab=0;$kpiDur=0.0;$kpiPR=0.0;
$s=$conn->prepare("SELECT COUNT(DISTINCT cpf) c FROM acompanhamento_atividades $whereSql"); if($types) $s->bind_param($types,...$bind); $s->execute(); $kpiColab=(int)($s->get_result()->fetch_assoc()['c']??0); $s->close();
$s=$conn->prepare("SELECT AVG(DATEDIFF(data_final,data_inicial)+1) d FROM acompanhamento_atividades $whereSql"); if($types) $s->bind_param($types,...$bind); $s->execute(); $kpiDur=(float)($s->get_result()->fetch_assoc()['d']??0); $s->close();
$s=$conn->prepare("SELECT SUM(CASE WHEN TRIM(IFNULL(pontos_relevantes,''))<>'' THEN 1 ELSE 0 END) with_pr, COUNT(*) total_f FROM acompanhamento_atividades $whereSql"); if($types) $s->bind_param($types,...$bind); $s->execute(); $r=$s->get_result()->fetch_assoc(); $s->close();
$with=(int)($r['with_pr']??0); $totf=(int)($r['total_f']??1); $kpiPR=$totf>0?round(100*$with/$totf,1):0.0;

// séries
$seriesMes=[]; $s=$conn->prepare("SELECT DATE_FORMAT(data_registro,'%Y-%m') ym, COUNT(*) c FROM acompanhamento_atividades $whereSql GROUP BY ym ORDER BY ym ASC"); if($types) $s->bind_param($types,...$bind); $s->execute(); $rm=$s->get_result(); while($row=$rm->fetch_assoc()) $seriesMes[]=['ym'=>$row['ym'],'c'=>(int)$row['c']]; $s->close();
$seriesDir=[]; $s=$conn->prepare("SELECT diretoria, COUNT(*) c FROM acompanhamento_atividades $whereSql GROUP BY diretoria ORDER BY c DESC"); if($types) $s->bind_param($types,...$bind); $s->execute(); $rd=$s->get_result(); while($row=$rd->fetch_assoc()) $seriesDir[]=['diretoria'=>$row['diretoria']?:'—','c'=>(int)$row['c']]; $s->close();
$seriesCol=[]; $s=$conn->prepare("SELECT nome, COUNT(*) c FROM acompanhamento_atividades $whereSql GROUP BY nome ORDER BY c DESC LIMIT 15"); if($types) $s->bind_param($types,...$bind); $s->execute(); $rc=$s->get_result(); while($row=$rc->fetch_assoc()) $seriesCol[]=['nome'=>$row['nome']?:'—','c'=>(int)$row['c']]; $s->close();

echo json_encode([
  'total'=>$total,'filtered'=>$filtered,'page'=>$page,'per'=>$per,'rows'=>$rows,
  'kpis'=>['total'=>$filtered,'colab'=>$kpiColab,'dur'=>round($kpiDur,1),'pr'=>$kpiPR],
  'series'=>['mes'=>$seriesMes,'diretoria'=>$seriesDir,'colab'=>$seriesCol]
], JSON_UNESCAPED_UNICODE);
