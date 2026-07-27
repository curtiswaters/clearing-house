<?php
/**
 * Server-rendered listing page (/listing/{id}/, via .htaccess rewrite),
 * so each business has a real, independently crawlable URL instead of a
 * hash fragment inside the index.html SPA. See README's SEO section.
 */
define('CH_APP', true);
require __DIR__ . '/api/db.php';
require __DIR__ . '/partials/render-helpers.php';
$categories = require __DIR__ . '/partials/categories.php';

$id = $_GET['id'] ?? '';
$stmt = $pdo->prepare('SELECT * FROM businesses WHERE id = ?');
$stmt->execute([$id]);
$b = $stmt->fetch();

if (!$b) {
  http_response_code(404);
  ?>
  <!DOCTYPE html>
  <html lang="en">
  <head>
  <meta charset="UTF-8">
  <title>Listing not found | The Clearing House</title>
  <link rel="stylesheet" href="/style.css">
  </head>
  <body>
  <?php include __DIR__ . '/partials/header.php'; ?>
  <div class="empty"><span class="display">Listing not found.</span><a href="/">← Back to directory</a></div>
  <?php include __DIR__ . '/partials/footer.php'; ?>
  </body>
  </html>
  <?php
  exit;
}

$cat = $categories[$b['category']];
$activeCategorySlug = $b['category'];

$pageTitle = $b['name'] . ' — ' . $cat['short'] . ' in ' . $b['city'] . ' | The Clearing House';
$pageDescription = $b['oneliner'];
$canonical = 'https://clearinghousecharlotte.com/listing/' . $b['id'] . '/';
$phoneDigits = preg_replace('/[^\d+]/', '', $b['phone']);

[$locality, $region] = parse_city_state($b['city']);
$localBusiness = [
  '@type' => 'LocalBusiness',
  'name' => $b['name'],
  'telephone' => $b['phone'],
  'category' => $cat['short'],
  'description' => $b['oneliner'],
  'url' => $canonical,
  'address' => [
    '@type' => 'PostalAddress',
    'addressLocality' => $locality,
    'addressRegion' => $region,
    'addressCountry' => 'US',
  ],
];
if ($b['website']) $localBusiness['sameAs'] = website_href($b['website']);
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
<script type="application/ld+json"><?= json_encode(['@context' => 'https://schema.org', '@graph' => array_merge(site_schema_graph(), [$localBusiness])]) ?></script>
</head>
<body>

<?php include __DIR__ . '/partials/header.php'; ?>

<div class="listing-page">
  <a class="back-link" href="/category/<?= h($b['category']) ?>/">← Back to <?= h($cat['short']) ?></a>
  <div class="listing-head">
    <div class="avatar" style="background:<?= avatar_color($b['category']) ?>"><?= h(initials($b['name'])) ?></div>
    <div class="listing-title">
      <h1><?= h($b['name']) ?></h1>
      <div class="badges">
        <span class="badge <?= h($b['category']) ?>"><?= h($cat['short']) ?></span>
        <?php if ($b['category_sponsor']): ?><span class="badge sponsor">★ Category Sponsor</span><?php endif; ?>
        <?php if ($b['featured']): ?><span class="badge featured">★ Featured</span><?php endif; ?>
        <?php if ($b['verified']): ?><span class="badge verified">✓ Verified</span><?php endif; ?>
        <span class="mono" style="font-size:12px; color:var(--ink-soft);"><?= h($b['city']) ?></span>
      </div>
    </div>
  </div>
  <div class="listing-panel">
    <div class="desc">
      <span class="oneliner">"<?= h($b['oneliner']) ?>"</span>
      <?= h($b['description']) ?>
    </div>
    <div class="contact-row">
      <div class="contact-chip">📞 <a href="tel:<?= h($phoneDigits) ?>"><?= h($b['phone']) ?></a></div>
      <div class="contact-chip">📍 <?= h($b['city']) ?></div>
      <?php if ($b['website']): ?>
      <div class="contact-chip">🌐 <a href="<?= h(website_href($b['website'])) ?>" target="_blank" rel="noopener noreferrer"><?= h(website_label($b['website'])) ?></a></div>
      <?php endif; ?>
    </div>
    <div class="cta-row">
      <a class="btn" href="tel:<?= h($phoneDigits) ?>">Call <?= h($b['phone']) ?></a>
      <?php if ($b['website']): ?>
      <a class="btn ghost" href="<?= h(website_href($b['website'])) ?>" target="_blank" rel="noopener noreferrer">Visit website</a>
      <?php endif; ?>
      <a class="btn ghost" href="/category/<?= h($b['category']) ?>/">See similar listings</a>
    </div>
  </div>
</div>

<?php include __DIR__ . '/partials/footer.php'; ?>
</body>
</html>
