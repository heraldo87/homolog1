<?php
// php/aai_store.php — Persiste um AAI

require_once __DIR__ . '/conn.php';

function only_digits(?string $s): string { return preg_replace('/\D+/', '', $s ?? ''); }
function fail(string $code): void {
  header('Location: /formulario.php?err=' . urlencode($code));
  exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  fail('val');
}

$nome   = trim($_POST['nome'] ?? '');
$email  = trim($_POST['email'] ?? ''); // opcional (não grava na tabela)
$cpfRaw = $_POST['cpf'] ?? '';
$cpf    = only_digits($cpfRaw);
$dir    = trim($_POST['diretoria'] ?? '');
$d1     = trim($_POST['data_inicial'] ?? '');
$d2     = trim($_POST['data_final'] ?? '');
$ar     = trim($_POST['atividades_realizadas'] ?? '');
$ap     = trim($_POST['atividades_previstas'] ?? '');
$pr     = trim($_POST['pontos_relevantes'] ?? '');

// ===== Validações mínimas =====
if ($nome === '' || $dir === '' || $ar === '' || $ap === '' || strlen($cpf) !== 11) {
  fail('val');
}
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $d1) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $d2)) {
  fail('val');
}
if ($d1 > $d2) {
  fail('val');
}

// (Opcional) regra de duplicidade: impedir 2 registros iguais de mesmo CPF e mesmo intervalo
$dupSql = "SELECT id FROM acompanhamento_atividades WHERE cpf = ? AND data_inicial = ? AND data_final = ? LIMIT 1";
if ($dup = $conn->prepare($dupSql)) {
  $dup->bind_param('sss', $cpf, $d1, $d2);
  $dup->execute();
  $resDup = $dup->get_result();
  if ($resDup && $resDup->fetch_assoc()) {
    $dup->close();
    fail('dup');
  }
  $dup->close();
}

// ===== Insert =====
// Use id = NULL para cobrir casos em que id não seja AUTO_INCREMENT ainda.
$sql = "INSERT INTO acompanhamento_atividades
        (id, nome, cpf, diretoria, data_inicial, data_final, atividades_realizadas, atividades_previstas, pontos_relevantes, data_registro)
        VALUES (NULL, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";

$stmt = $conn->prepare($sql);
if (!$stmt) fail('db');

$stmt->bind_param(
  'ssssssss',
  $nome, $cpf, $dir, $d1, $d2, $ar, $ap, $pr
);

$ok = $stmt->execute();
$stmt->close();

if (!$ok) fail('db');

// Sucesso → PRG para a tela de confirmação
$nomeEnc = urlencode($nome);
header("Location: /aai_sucesso.php?ok=1&nome={$nomeEnc}");
exit;
