<?php
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
  http_response_code(405);
  echo json_encode(['error' => 'Method not allowed']);
  exit;
}

require __DIR__ . '/db.php';

try {
  $rows = $pdo->query('SELECT id, name, category, phone, city, oneliner, description, featured FROM businesses')->fetchAll();
} catch (PDOException $e) {
  http_response_code(500);
  echo json_encode(['error' => 'Database error']);
  exit;
}

$businesses = array_map(function ($row) {
  $row['featured'] = (bool) $row['featured'];
  return $row;
}, $rows);

echo json_encode($businesses);
