<?php
/**
 * Server-rendered category page (/category/{slug}/, via .htaccess rewrite),
 * so category pages are real, independently crawlable URLs instead of a
 * hash fragment inside the index.html SPA. See README's SEO section.
 */
define('CH_APP', true);
require __DIR__ . '/api/db.php';
require __DIR__ . '/partials/render-helpers.php';
$categories = require __DIR__ . '/partials/categories.php';

$slug = $_GET['slug'] ?? '';
if (!isset($categories[$slug])) {
  http_response_code(404);
  $activeCategorySlug = null;
  ?>
  <!DOCTYPE html>
  <html lang="en">
  <head>
  <meta charset="UTF-8">
  <title>Category not found | The Clearing House</title>
  <link rel="stylesheet" href="/style.css">
  </head>
  <body>
  <?php include __DIR__ . '/partials/header.php'; ?>
  <div class="empty"><span class="display">Category not found.</span><a href="/">← Back to directory</a></div>
  <?php include __DIR__ . '/partials/footer.php'; ?>
  </body>
  </html>
  <?php
  exit;
}

$cat = $categories[$slug];
$activeCategorySlug = $slug;

$stmt = $pdo->prepare('SELECT * FROM businesses WHERE category = ?');
$stmt->execute([$slug]);
$inCategory = $stmt->fetchAll();

$list = sort_for_display($inCategory);
$sponsor = array_values(array_filter($list, function ($b) { return $b['category_sponsor']; }));
$featured = array_values(array_filter($list, function ($b) { return $b['featured'] && !$b['category_sponsor']; }));

$pageTitle = $cat['label'] . ' in Charlotte, NC | The Clearing House';
$pageDescription = $cat['blurb'];
$canonical = 'https://clearinghousecharlotte.com/category/' . $slug . '/';
?>
<!DOCTYPE html>
<html lang="en">
<head>
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
  <div class="eyebrow"><?= count($inCategory) ?> businesses</div>
  <h1><?= h($cat['label']) ?></h1>
  <p class="sub"><?= h($cat['blurb']) ?></p>
  <form class="search-drawer" action="/" method="get">
    <input type="text" name="q" placeholder="Search within <?= h($cat['short']) ?>…" autocomplete="off">
    <button type="submit">Search</button>
  </form>
</div>

<?php if ($sponsor): ?>
<div class="section-head"><h2>Category Sponsor</h2><div class="meta">exclusive placement</div></div>
<div class="grid"><?php foreach ($sponsor as $b) echo card_html($b, $categories); ?></div>
<?php endif; ?>

<?php if ($featured): ?>
<div class="section-head"><h2>Featured</h2><div class="meta">sponsored placement</div></div>
<div class="grid"><?php foreach ($featured as $b) echo card_html($b, $categories); ?></div>
<?php endif; ?>

<div class="section-head"><h2>All <?= h($cat['short']) ?></h2><div class="meta"><?= count($inCategory) ?> businesses</div></div>
<div class="grid">
<?php if ($list): ?>
  <?php foreach ($list as $b) echo card_html($b, $categories); ?>
<?php else: ?>
  <div class="empty">No listings yet in this category.</div>
<?php endif; ?>
</div>

<?php include __DIR__ . '/partials/footer.php'; ?>
</body>
</html>
