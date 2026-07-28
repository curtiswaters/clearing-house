<?php
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
  http_response_code(405);
  echo json_encode(['error' => 'Method not allowed']);
  exit;
}

require __DIR__ . '/db.php';

try {
  $rows = $pdo->query('SELECT id, name, category, phone, city, website, oneliner, description, verified, featured, category_sponsor, markets FROM businesses')->fetchAll();
} catch (PDOException $e) {
  http_response_code(500);
  echo json_encode(['error' => 'Database error']);
  exit;
}

$businesses = array_map(function ($row) {
  $row['verified'] = (bool) $row['verified'];
  $row['featured'] = (bool) $row['featured'];
  $row['category_sponsor'] = (bool) $row['category_sponsor'];
  return $row;
}, $rows);

echo json_encode($businesses);
