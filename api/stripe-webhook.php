<?php
/**
 * Stripe webhook endpoint. Register this URL (https://yourdomain/api/stripe-webhook.php)
 * in the Stripe Dashboard → Developers → Webhooks, subscribed to the
 * checkout.session.completed event, and put its signing secret in
 * api/config.php as 'stripe_webhook_secret'.
 *
 * This is the ONLY place featured status should be set to true — it only
 * runs after Stripe has verified the payment succeeded.
 */

require __DIR__ . '/db.php';

$payload = file_get_contents('php://input');
$sigHeader = $_SERVER['HTTP_STRIPE_SIGNATURE'] ?? '';

if (!verifyStripeSignature($payload, $sigHeader, $config['stripe_webhook_secret'])) {
  http_response_code(400);
  exit('Invalid signature');
}

$event = json_decode($payload, true);

if (($event['type'] ?? '') === 'checkout.session.completed') {
  $businessId = $event['data']['object']['metadata']['business_id'] ?? '';
  if ($businessId !== '') {
    $stmt = $pdo->prepare('UPDATE businesses SET featured = 1 WHERE id = ?');
    $stmt->execute([$businessId]);
  }
}

http_response_code(200);
echo 'ok';

/**
 * Verifies a Stripe webhook signature without the Stripe SDK.
 * See https://stripe.com/docs/webhooks/signatures#verify-manually
 */
function verifyStripeSignature(string $payload, string $sigHeader, string $secret): bool {
  if ($sigHeader === '' || $secret === '') return false;

  $parts = [];
  foreach (explode(',', $sigHeader) as $pair) {
    [$key, $value] = array_pad(explode('=', $pair, 2), 2, '');
    $parts[$key] = $value;
  }

  $timestamp = $parts['t'] ?? '';
  $signature = $parts['v1'] ?? '';
  if ($timestamp === '' || $signature === '') return false;

  // Reject events older than 5 minutes to guard against replay attacks.
  if (abs(time() - (int) $timestamp) > 300) return false;

  $expected = hash_hmac('sha256', $timestamp . '.' . $payload, $secret);
  return hash_equals($expected, $signature);
}
