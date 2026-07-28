<?php
/**
 * Server-rendered market page (/market/{slug}/, via .htaccess rewrite) —
 * businesses serving a specific city/market across all categories. See
 * README's "Markets" section.
 */
define('CH_APP', true);
require __DIR__ . '/api/db.php';
require __DIR__ . '/partials/render-helpers.php';
$categories = require __DIR__ . '/partials/categories.php';
$markets = require __DIR__ . '/partials/markets.php';

$slug = $_GET['slug'] ?? '';
if (!isset($markets[$slug])) {
  http_response_code(404);
  ?>
  <!DOCTYPE html>
  <html lang="en">
  <head>
  <meta charset="UTF-8">
  <title>Market not found | The Clearing House</title>
  <link rel="stylesheet" href="/style.css">
  </head>
  <body>
  <?php include __DIR__ . '/partials/header.php'; ?>
  <div class="empty"><span class="display">Market not found.</span><a href="/markets/">← Back to markets</a></div>
  <?php include __DIR__ . '/partials/footer.php'; ?>
  </body>
  </html>
  <?php
  exit;
}

$market = $markets[$slug];

$stmt = $pdo->prepare('SELECT * FROM businesses WHERE FIND_IN_SET(?, markets)');
$stmt->execute([$slug]);
$inMarket = $stmt->fetchAll();

$list = sort_for_display($inMarket);
$sponsor = array_values(array_filter($list, function ($b) { return $b['category_sponsor']; }));
$featured = array_values(array_filter($list, function ($b) { return $b['featured'] && !$b['category_sponsor']; }));

$pageTitle = 'Estate Sale, Junk Removal & Cleanout Services in ' . $market['name'] . ' | The Clearing House';
$pageDescription = 'Estate sale, junk removal, and hoarding cleanup companies serving ' . $market['name'] . '. ' . $market['blurb'];
$canonical = 'https://clearinghousecharlotte.com/market/' . $slug . '/';
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
  <div class="eyebrow"><?= count($inMarket) ?> businesses</div>
  <h1><?= h($market['name']) ?></h1>
  <p class="sub"><?= h($market['blurb']) ?></p>
  <form class="search-drawer" action="/" method="get">
    <input type="text" name="q" placeholder="Search within <?= h($market['name']) ?>…" autocomplete="off">
    <button type="submit">Search</button>
  </form>
</div>

<?php if ($sponsor): ?>
<div class="section-head"><h2>Category Sponsors</h2><div class="meta">exclusive placement</div></div>
<div class="grid"><?php foreach ($sponsor as $b) echo card_html($b, $categories); ?></div>
<?php endif; ?>

<?php if ($featured): ?>
<div class="section-head"><h2>Featured</h2><div class="meta">sponsored placement</div></div>
<div class="grid"><?php foreach ($featured as $b) echo card_html($b, $categories); ?></div>
<?php endif; ?>

<div class="section-head"><h2>All businesses serving <?= h($market['name']) ?></h2><div class="meta"><?= count($inMarket) ?> businesses</div></div>
<div class="grid">
<?php if ($list): ?>
  <?php foreach ($list as $b) echo card_html($b, $categories); ?>
<?php else: ?>
  <div class="empty">No listings yet for this market — check back soon, or <a href="/#/contact" style="text-decoration:underline;">let us know</a> if you know a company serving this area.</div>
<?php endif; ?>
</div>

<?php include __DIR__ . '/partials/footer.php'; ?>
</body>
</html>
