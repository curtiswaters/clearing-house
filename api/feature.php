<?php
/**
 * TEMPORARY stopgap for the mock "Get Featured" checkout in index.html.
 * This endpoint has no payment verification — anyone who can reach it can
 * mark any listing as featured. It exists only so Featured status persists
 * in the demo/staging period. Once real Stripe billing is wired up, this
 * endpoint should be removed and featured status should only be set from
 * the Stripe webhook handler after a verified successful payment.
 */
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  http_response_code(405);
  echo json_encode(['error' => 'Method not allowed']);
  exit;
}

require __DIR__ . '/db.php';

$body = json_decode(file_get_contents('php://input'), true);
$id = $body['id'] ?? '';

if ($id === '') {
  http_response_code(400);
  echo json_encode(['error' => 'Missing id']);
  exit;
}

$stmt = $pdo->prepare('UPDATE businesses SET featured = 1 WHERE id = ?');
$stmt->execute([$id]);

if ($stmt->rowCount() === 0) {
  http_response_code(404);
  echo json_encode(['error' => 'Business not found']);
  exit;
}

echo json_encode(['ok' => true]);
