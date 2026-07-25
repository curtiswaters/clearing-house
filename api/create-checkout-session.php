<?php
/**
 * Creates a real Stripe Checkout Session for the $30/mo Featured Listing
 * subscription and returns its URL. The browser redirects to that URL —
 * card details are entered on Stripe's own hosted page, never on this site.
 *
 * Featured status is NOT set here. It's only set by stripe-webhook.php,
 * after Stripe confirms the payment actually succeeded.
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

$stmt = $pdo->prepare('SELECT id, name FROM businesses WHERE id = ?');
$stmt->execute([$id]);
$business = $stmt->fetch();

if (!$business) {
  http_response_code(404);
  echo json_encode(['error' => 'Business not found']);
  exit;
}

$scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$origin = $scheme . '://' . $_SERVER['HTTP_HOST'];
$successUrl = $origin . '/index.html?checkout=success&business=' . urlencode($id) . '#/listing/' . urlencode($id);
$cancelUrl  = $origin . '/index.html?checkout=cancel&business=' . urlencode($id) . '#/listing/' . urlencode($id);

$params = [
  'mode' => 'subscription',
  'success_url' => $successUrl,
  'cancel_url' => $cancelUrl,
  'line_items' => [[
    'quantity' => 1,
    'price_data' => [
      'currency' => 'usd',
      'unit_amount' => 3000,
      'recurring' => ['interval' => 'month'],
      'product_data' => [
        'name' => 'Featured Listing — ' . $business['name'],
      ],
    ],
  ]],
  'metadata' => ['business_id' => $business['id']],
];

$ch = curl_init('https://api.stripe.com/v1/checkout/sessions');
curl_setopt_array($ch, [
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_POST => true,
  CURLOPT_HTTPHEADER => ['Authorization: Bearer ' . $config['stripe_secret_key']],
  CURLOPT_POSTFIELDS => http_build_query($params),
]);
$response = curl_exec($ch);
$status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlError = curl_error($ch);
curl_close($ch);

if ($response === false) {
  http_response_code(502);
  echo json_encode(['error' => 'Could not reach Stripe: ' . $curlError]);
  exit;
}

$session = json_decode($response, true);

if ($status !== 200 || empty($session['url'])) {
  http_response_code(502);
  echo json_encode(['error' => $session['error']['message'] ?? 'Stripe request failed']);
  exit;
}

echo json_encode(['url' => $session['url']]);
