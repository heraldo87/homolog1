<?php
header('Content-Type: text/plain; charset=utf-8');
require_once __DIR__ . '/../php/conn.php';

$who = $conn->host_info ?? 'n/d';
$db  = $conn->query('SELECT DATABASE() db')->fetch_assoc()['db'] ?? 'n/d';
$cnt = $conn->query('SELECT COUNT(*) c FROM acompanhamento_atividades')->fetch_assoc()['c'] ?? '0';

echo "Host info: $who\n";
echo "Database: $db\n";
echo "acompanhamento_atividades (COUNT): $cnt\n";
