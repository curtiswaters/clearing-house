<?php
/**
 * Markets index (/markets/, via .htaccess rewrite) — lists every market
 * this directory covers, with a live business count per market. See
 * README's "Markets" section.
 */
define('CH_APP', true);
require __DIR__ . '/api/db.php';
require __DIR__ . '/partials/render-helpers.php';
$categories = require __DIR__ . '/partials/categories.php';
$markets = require __DIR__ . '/partials/markets.php';

$counts = [];
foreach (array_keys($markets) as $slug) {
  $stmt = $pdo->prepare('SELECT COUNT(*) FROM businesses WHERE FIND_IN_SET(?, markets)');
  $stmt->execute([$slug]);
  $counts[$slug] = (int) $stmt->fetchColumn();
}

$pageTitle = 'Markets We Serve | The Clearing House';
$pageDescription = 'Estate sale, junk removal, and hoarding cleanup services across the Charlotte metro — Charlotte, Concord, Cornelius, Gastonia, Fort Mill, Rock Hill, and Indian Land.';
$canonical = 'https://clearinghousecharlotte.com/markets/';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<?php include __DIR__ . '/partials/analytics.php'; ?>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= h($pageTitle) ?></title>
<meta name="description" content="<?= h($pageDescription) ?>">
<link rel="canonical" href="<?= h($canonical) ?>">
<meta property="og:site_name" content="The Clearing House">
<meta property="og:type" content="website">
<meta property="og:url" content="<?= h($canonical) ?>">
<meta property="og:title" content="<?= h($pageTitle) ?>">
<meta property="og:description" content="<?= h($pageDescription) ?>">
<meta property="og:image" content="https://clearinghousecharlotte.com/hero_estatedir1.webp">
<meta name="twitter:card" content="summary_large_image">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Fraunces:ital,opsz,wght@0,9..144,400;0,9..144,500;0,9..144,600;0,9..144,700;1,9..144,500&family=Inter:wght@400;500;600;700&family=IBM+Plex+Mono:wght@400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="/style.css">
<script type="application/ld+json"><?= json_encode(['@context' => 'https://schema.org', '@graph' => site_schema_graph()]) ?></script>
</head>
<body>

<?php include __DIR__ . '/partials/header.php'; ?>

<div class="hero" style="padding-bottom:8px;">
  <div class="eyebrow">Charlotte metro</div>
  <h1>Markets We Serve</h1>
  <p class="sub">Estate sale, junk removal, and hoarding cleanup services across the wider Charlotte metro — pick a market to see who serves it.</p>
</div>

<div class="tiles">
  <?php foreach ($markets as $slug => $market): ?>
    <a href="/market/<?= h($slug) ?>/" class="tile" style="text-decoration:none; display:block;">
      <h3><?= h($market['name']) ?></h3>
      <p><?= h($market['blurb']) ?></p>
      <div class="count"><?= $counts[$slug] ?> listed →</div>
    </a>
  <?php endforeach; ?>
</div>

<?php include __DIR__ . '/partials/footer.php'; ?>
</body>
</html>
