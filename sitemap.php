<?php
/**
 * Dynamic sitemap, served at /sitemap.xml via .htaccess rewrite. Lists the
 * homepage, every category, every business listing, and every guide —
 * regenerated on each request from the businesses table (and the guide
 * content file), so it never goes stale like a hand-maintained file would.
 */
define('CH_APP', true);
header('Content-Type: application/xml; charset=UTF-8');
require __DIR__ . '/api/db.php';
$guides = require __DIR__ . '/partials/guide-content.php';

$siteUrl = 'https://clearinghousecharlotte.com/';
$urls = [$siteUrl, $siteUrl . 'guides/'];

foreach (['estate-sale', 'junk-removal', 'hoarding-biohazard'] as $slug) {
  $urls[] = $siteUrl . 'category/' . $slug . '/';
}

foreach (array_keys($guides) as $slug) {
  $urls[] = $siteUrl . 'guides/' . $slug . '/';
}

$rows = $pdo->query('SELECT id FROM businesses')->fetchAll();
foreach ($rows as $row) {
  $urls[] = $siteUrl . 'listing/' . $row['id'] . '/';
}

echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
foreach ($urls as $url) {
  echo '  <url><loc>' . htmlspecialchars($url, ENT_QUOTES, 'UTF-8') . '</loc></url>' . "\n";
}
echo '</urlset>' . "\n";
