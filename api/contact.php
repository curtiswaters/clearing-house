<?php
/**
 * Sends the contact form via PHP's built-in mail() through the server's
 * local MTA — no SMTP library needed. Works on cPanel as long as
 * contact_from_email (in api/config.php) is a mailbox on the site's own
 * domain, which most mail servers require to avoid being flagged as spam.
 */
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  http_response_code(405);
  echo json_encode(['error' => 'Method not allowed']);
  exit;
}

require __DIR__ . '/db.php'; // only used for $config here; no DB queries in this file

$body = json_decode(file_get_contents('php://input'), true) ?? [];

// Honeypot: a hidden field real visitors never fill in. If it's set,
// silently pretend success instead of tipping off the bot.
if (($body['website'] ?? '') !== '') {
  echo json_encode(['ok' => true]);
  exit;
}

$name = trim($body['name'] ?? '');
$email = trim($body['email'] ?? '');
$subject = trim($body['subject'] ?? '') ?: 'Message from The Clearing House site';
$message = trim($body['message'] ?? '');

if ($name === '' || $email === '' || $message === '') {
  http_response_code(400);
  echo json_encode(['error' => 'Name, email, and message are required']);
  exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
  http_response_code(400);
  echo json_encode(['error' => 'Please enter a valid email address']);
  exit;
}

// Strip newlines from anything going into a header, to prevent header injection.
$sanitizeHeader = fn($s) => trim(preg_replace('/[\r\n]+/', ' ', $s));
$name = $sanitizeHeader($name);
$subject = $sanitizeHeader($subject);

$to = $config['contact_to_email'];
$from = $config['contact_from_email'];

$headers = "From: The Clearing House <{$from}>\r\n" .
           "Reply-To: {$name} <{$email}>\r\n" .
           "Content-Type: text/plain; charset=UTF-8";

$mailBody = "Name: {$name}\nEmail: {$email}\n\n{$message}";

$sent = mail($to, $subject, $mailBody, $headers);

if (!$sent) {
  http_response_code(502);
  echo json_encode(['error' => 'Could not send message']);
  exit;
}

echo json_encode(['ok' => true]);
