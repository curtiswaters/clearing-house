<?php
/**
 * Shared PDO connection, used by every endpoint in api/ and by sql/seed.php.
 */

$configFile = __DIR__ . '/config.php';
if (!file_exists($configFile)) {
  http_response_code(500);
  die('Missing api/config.php — copy api/config.example.php and fill in your database credentials.');
}

$config = require $configFile;

$dsn = "mysql:host={$config['host']};dbname={$config['db']};charset=utf8mb4";

$pdo = new PDO($dsn, $config['user'], $config['pass'], [
  PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
  PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
]);
