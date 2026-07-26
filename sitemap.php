<?php
/**
 * Dynamic sitemap, served at /sitemap.xml via .htaccess rewrite. Lists the
 * homepage, every category, and every business listing — regenerated on
 * each request from the businesses table, so it never goes stale like a
 * hand-maintained static file would.
 */
header('Content-Type: application/xml; charset=UTF-8');
require __DIR__ . '/api/db.php';

$siteUrl = 'https://clearinghousecharlotte.com/';
$urls = [$siteUrl];

foreach (['estate-sale', 'junk-removal', 'hoarding-biohazard'] as $slug) {
  $urls[] = $siteUrl . 'category/' . $slug . '/';
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
