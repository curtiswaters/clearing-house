<?php
if (!defined('CH_APP')) { http_response_code(403); exit; }

function h($s): string {
  return htmlspecialchars((string) $s, ENT_QUOTES, 'UTF-8');
}

function initials(string $name): string {
  $words = array_values(array_filter(preg_split('/\s+/', $name), function ($w) {
    return preg_match('/[A-Za-z]/', $w[0] ?? '');
  }));
  $words = array_slice($words, 0, 2);
  return strtoupper(implode('', array_map(function ($w) { return $w[0]; }, $words)));
}

function avatar_color(string $category): string {
  if ($category === 'estate-sale') return 'var(--navy)';
  if ($category === 'junk-removal') return 'var(--sage)';
  return 'var(--rust)';
}

function website_href(string $w): string {
  return preg_match('#^https?://#i', $w) ? $w : "https://$w";
}

function website_label(string $w): string {
  return preg_replace('#/$#', '', preg_replace('#^https?://#i', '', $w));
}

function parse_city_state(string $cityStr): array {
  $parts = array_map('trim', explode(',', $cityStr));
  return [$parts[0] ?? '', $parts[1] ?? ''];
}

function sort_for_display(array $list): array {
  usort($list, function ($a, $b) {
    if ($a['category_sponsor'] && !$b['category_sponsor']) return -1;
    if (!$a['category_sponsor'] && $b['category_sponsor']) return 1;
    if ($a['featured'] && !$b['featured']) return -1;
    if (!$a['featured'] && $b['featured']) return 1;
    return strcasecmp($a['name'], $b['name']);
  });
  return $list;
}

function card_html(array $b, array $categories): string {
  $cat = $categories[$b['category']];

  $pill = '';
  if (!empty($b['category_sponsor'])) {
    $pill = '<div class="featured-pill sponsor">★ Sponsor</div>';
  } elseif (!empty($b['featured'])) {
    $pill = '<div class="featured-pill">★ Featured</div>';
  }
  $verifiedCheck = !empty($b['verified']) ? '<span class="verified-check" title="Verified listing">✓</span>' : '';

  return '
  <a href="/listing/' . h($b['id']) . '/" class="card" style="text-decoration:none;">
    ' . $pill . '
    <div class="folder-tab ' . h($b['category']) . '">' . h($cat['short']) . '</div>
    <div class="card-body">
      <div class="card-top">
        <div class="avatar" style="background:' . avatar_color($b['category']) . '">' . h(initials($b['name'])) . '</div>
        <div>
          <h3>' . h($b['name']) . $verifiedCheck . '</h3>
          <div class="loc">' . h($b['city']) . '</div>
        </div>
      </div>
      <div class="desc">' . h($b['oneliner']) . '</div>
      <div class="phone"><span>' . h($b['phone']) . '</span><span style="color:var(--ink-soft);">view →</span></div>
    </div>
  </a>';
}

function site_schema_graph(): array {
  $siteUrl = 'https://clearinghousecharlotte.com/';
  return [
    [
      '@type' => 'Organization',
      '@id' => $siteUrl . '#organization',
      'name' => 'The Clearing House',
      'url' => $siteUrl,
      'description' => 'A directory of estate sale cleanout, junk removal, and hoarding/biohazard cleanup companies serving the Charlotte, NC metro area.',
      'areaServed' => ['@type' => 'City', 'name' => 'Charlotte'],
    ],
    [
      '@type' => 'WebSite',
      '@id' => $siteUrl . '#website',
      'url' => $siteUrl,
      'name' => 'The Clearing House',
      'publisher' => ['@id' => $siteUrl . '#organization'],
    ],
  ];
}
