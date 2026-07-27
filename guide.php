<?php
/**
 * Individual guide page (/guides/{slug}/, via .htaccess rewrite). Content
 * lives in partials/guide-content.php as a plain PHP array (editorial
 * content that changes rarely) rather than a database table.
 */
define('CH_APP', true);
require __DIR__ . '/partials/render-helpers.php';
$categories = require __DIR__ . '/partials/categories.php';
$guides = require __DIR__ . '/partials/guide-content.php';

$slug = $_GET['slug'] ?? '';
if (!isset($guides[$slug])) {
  http_response_code(404);
  ?>
  <!DOCTYPE html>
  <html lang="en">
  <head>
  <meta charset="UTF-8">
  <title>Guide not found | The Clearing House</title>
  <link rel="stylesheet" href="/style.css">
  </head>
  <body>
  <?php include __DIR__ . '/partials/header.php'; ?>
  <div class="empty"><span class="display">Guide not found.</span><a href="/guides/">← Back to guides</a></div>
  <?php include __DIR__ . '/partials/footer.php'; ?>
  </body>
  </html>
  <?php
  exit;
}

$guide = $guides[$slug];
$canonical = 'https://clearinghousecharlotte.com/guides/' . $slug . '/';

$article = [
  '@type' => 'Article',
  'headline' => $guide['title'],
  'description' => $guide['description'],
  'mainEntityOfPage' => $canonical,
  'author' => ['@type' => 'Organization', 'name' => 'The Clearing House'],
  'publisher' => ['@type' => 'Organization', 'name' => 'The Clearing House', 'url' => 'https://clearinghousecharlotte.com/'],
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<?php include __DIR__ . '/partials/analytics.php'; ?>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= h($guide['title']) ?> | The Clearing House</title>
<meta name="description" content="<?= h($guide['description']) ?>">
<link rel="canonical" href="<?= h($canonical) ?>">
<meta property="og:site_name" content="The Clearing House">
<meta property="og:type" content="article">
<meta property="og:url" content="<?= h($canonical) ?>">
<meta property="og:title" content="<?= h($guide['title']) ?>">
<meta property="og:description" content="<?= h($guide['description']) ?>">
<meta property="og:image" content="https://clearinghousecharlotte.com/hero_estatedir1.webp">
<meta name="twitter:card" content="summary_large_image">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Fraunces:ital,opsz,wght@0,9..144,400;0,9..144,500;0,9..144,600;0,9..144,700;1,9..144,500&family=Inter:wght@400;500;600;700&family=IBM+Plex+Mono:wght@400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="/style.css">
<script type="application/ld+json"><?= json_encode(['@context' => 'https://schema.org', '@graph' => array_merge(site_schema_graph(), [$article])]) ?></script>
</head>
<body>

<?php include __DIR__ . '/partials/header.php'; ?>

<div class="info-page">
  <a class="back-link" href="/guides/">← Back to guides</a>
  <div class="eyebrow">Free guide</div>
  <h1><?= h($guide['title']) ?></h1>
  <p class="lead"><?= h($guide['intro']) ?></p>
  <div class="info-panel">
    <?php foreach ($guide['sections'] as $section): ?>
      <h2><?= h($section['heading']) ?></h2>
      <p><?= $section['body'] ?></p>
    <?php endforeach; ?>
  </div>
</div>

<div class="guide-promo" style="max-width:780px; margin: 0 auto 60px;">
  <div>
    <div style="font-family:'IBM Plex Mono',monospace; font-size:12px; letter-spacing:.1em; text-transform:uppercase; color:var(--rust); margin-bottom:6px;">Get the full toolkit</div>
    <h3>The Family Guide to Estate Cleanouts</h3>
    <p><?= h($guide['cta_body']) ?></p>
  </div>
  <a class="btn brass" href="<?= h(WHOP_GUIDE_LINK) ?>" target="_blank" rel="noopener noreferrer">Get the Full Guide — $19.97</a>
</div>

<?php include __DIR__ . '/partials/footer.php'; ?>
</body>
</html>
