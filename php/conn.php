<?php
// conn.php — conexão MySQL (mysqli)

$DB_HOST = 'localhost';
$DB_PORT = 3306;
$DB_NAME = 'cortex360';
$DB_USER = 'cortex360';
$DB_PASS = 'Cortex360Vini';

$conn = @new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME, $DB_PORT);

if ($conn->connect_errno) {
  http_response_code(500);
  die('Erro ao conectar no MySQL: ' . htmlspecialchars($conn->connect_error));
}

$conn->set_charset('utf8mb4');