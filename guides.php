<?php
/**
 * Guides index (/guides/, via .htaccess rewrite) — lists the free guides,
 * each adapted from a chapter of the paid "Family Guide to Estate
 * Cleanouts" (sold on Whop). See README's "Guides" section.
 */
define('CH_APP', true);
require __DIR__ . '/partials/render-helpers.php';
$categories = require __DIR__ . '/partials/categories.php';
$guides = require __DIR__ . '/partials/guide-content.php';

$pageTitle = 'Free Estate Cleanout Guides | The Clearing House';
$pageDescription = 'Free, practical guides on sorting an estate, valuing items, and planning a cleanout timeline — adapted from The Family Guide to Estate Cleanouts.';
$canonical = 'https://clearinghousecharlotte.com/guides/';
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

<div class="info-page">
  <div class="eyebrow">Free guides</div>
  <h1>Estate Cleanout Guides</h1>
  <p class="lead">Practical, free guides on sorting an estate, valuing what's inside it, and planning a realistic timeline — adapted from <em>The Family Guide to Estate Cleanouts</em>.</p>
  <div class="info-panel">
    <?php foreach ($guides as $slug => $guide): ?>
      <h2><a class="inline" href="/guides/<?= h($slug) ?>/"><?= h($guide['title']) ?></a></h2>
      <p><?= h($guide['description']) ?></p>
    <?php endforeach; ?>
  </div>
</div>

<div class="guide-promo" style="max-width:780px; margin: 0 auto 60px;">
  <div>
    <div style="font-family:'IBM Plex Mono',monospace; font-size:12px; letter-spacing:.1em; text-transform:uppercase; color:var(--rust); margin-bottom:6px;">Digital guide</div>
    <h3>The Family Guide to Estate Cleanouts</h3>
    <p>These free guides cover the fundamentals — the full guide adds the complete checklists, week-by-week timeline, call scripts, and company screening matrix. Instant digital download.</p>
  </div>
  <a class="btn brass" href="<?= h(WHOP_GUIDE_LINK) ?>" target="_blank" rel="noopener noreferrer">Get the Guide — $19.97</a>
</div>

<?php include __DIR__ . '/partials/footer.php'; ?>
</body>
</html>
