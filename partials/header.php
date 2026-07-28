<?php
if (!defined('CH_APP')) { http_response_code(403); exit; }
/** Expects in scope: $categories (array). Optional: $activeCategorySlug (string|null). */
$activeCategorySlug = $activeCategorySlug ?? null;
?>
<header class="site">
  <div class="header-inner">
    <a class="logo" href="/">
      <div class="logo-mark">CH</div>
      <div class="logo-text">
        <div class="name">The Clearing House</div>
        <div class="tag">Charlotte Estate Sale Pros Directory</div>
      </div>
    </a>
    <nav class="cats">
      <?php foreach ($categories as $slug => $c): ?>
        <a href="/category/<?= h($slug) ?>/" class="<?= $slug === $activeCategorySlug ? 'active' : '' ?>"><?= h($c['short']) ?></a>
      <?php endforeach; ?>
      <a href="/guides/">Guides</a>
      <a href="/markets/">Markets</a>
      <span style="width:1px; background:var(--line); margin:0 4px;"></span>
      <a href="/#/about" style="text-transform:capitalize;">about</a>
      <a href="/#/pricing" style="text-transform:capitalize;">pricing</a>
      <a href="/#/contact" style="text-transform:capitalize;">contact</a>
      <a href="/#/faq" style="text-transform:capitalize;">faq</a>
    </nav>
    <div class="header-ctas">
      <a class="ghost" href="/#/pricing">Get Verified</a>
      <a class="solid" href="/#/pricing">Advertise</a>
    </div>
  </div>
</header>
