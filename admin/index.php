<?php
/**
 * Minimal password-protected admin panel for editing the businesses table
 * without touching SQL directly. Single file, plain HTML forms (no JS
 * framework) — POST/redirect/GET on every write to avoid resubmission.
 *
 * Put this behind HTTPS in production so the password isn't sent in the
 * clear (cPanel's AutoSSL provides free HTTPS on most plans).
 */
session_start();
require __DIR__ . '/../api/db.php';

const CATEGORIES = [
  'estate-sale' => 'Estate Sale & Cleanout',
  'junk-removal' => 'Junk Removal',
  'hoarding-biohazard' => 'Hoarding & Biohazard Cleanup',
];

// Kept in sync with partials/markets.php (that file also carries each
// market's blurb, which this admin panel doesn't need).
const MARKETS = [
  'charlotte-nc' => 'Charlotte, NC',
  'concord-nc' => 'Concord, NC',
  'cornelius-nc' => 'Cornelius, NC',
  'gastonia-nc' => 'Gastonia, NC',
  'fort-mill-sc' => 'Fort Mill, SC',
  'rock-hill-sc' => 'Rock Hill, SC',
  'indian-land-sc' => 'Indian Land, SC',
];

function csrfToken(): string {
  if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(32));
  return $_SESSION['csrf'];
}
function checkCsrf(?string $token): bool {
  return !empty($_SESSION['csrf']) && is_string($token) && hash_equals($_SESSION['csrf'], $token);
}
function h(string $s): string {
  return htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
}
function sponsorTaken(PDO $pdo, string $category, ?string $excludeId): bool {
  $sql = 'SELECT id FROM businesses WHERE category = ? AND category_sponsor = 1';
  $params = [$category];
  if ($excludeId !== null) {
    $sql .= ' AND id != ?';
    $params[] = $excludeId;
  }
  $stmt = $pdo->prepare($sql . ' LIMIT 1');
  $stmt->execute($params);
  return (bool) $stmt->fetch();
}

$error = '';

if (($_GET['logout'] ?? '') === '1') {
  session_destroy();
  header('Location: index.php');
  exit;
}

// --- Login ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'login') {
  if (($_SESSION['login_attempts'] ?? 0) >= 5) {
    sleep(2); // slow down brute-forcing without a full lockout mechanism
  }
  if (password_verify($_POST['password'] ?? '', $config['admin_password_hash'])) {
    session_regenerate_id(true);
    $_SESSION['admin_authed'] = true;
    $_SESSION['login_attempts'] = 0;
    header('Location: index.php');
    exit;
  }
  $_SESSION['login_attempts'] = ($_SESSION['login_attempts'] ?? 0) + 1;
  $error = 'Incorrect password.';
}

$authed = !empty($_SESSION['admin_authed']);

// --- Write actions (only when logged in) ---
if ($authed && $_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') !== 'login') {
  if (!checkCsrf($_POST['csrf'] ?? null)) {
    $error = 'Your session expired. Please try again.';
  } else {
    $action = $_POST['action'] ?? '';

    if ($action === 'save') {
      $id = strtolower(trim($_POST['id'] ?? ''));
      $isNew = ($_POST['is_new'] ?? '') === '1';
      $name = trim($_POST['name'] ?? '');
      $category = $_POST['category'] ?? '';
      $phone = trim($_POST['phone'] ?? '');
      $city = trim($_POST['city'] ?? '');
      $website = trim($_POST['website'] ?? '') ?: null;
      $oneliner = trim($_POST['oneliner'] ?? '');
      $description = trim($_POST['description'] ?? '');
      $verified = isset($_POST['verified']) ? 1 : 0;
      $featured = isset($_POST['featured']) ? 1 : 0;
      $categorySponsor = isset($_POST['category_sponsor']) ? 1 : 0;
      $markets = implode(',', array_intersect((array) ($_POST['markets'] ?? []), array_keys(MARKETS)));

      if (!preg_match('/^[a-z0-9-]+$/', $id)) {
        $error = 'ID must be lowercase letters, numbers, and hyphens only.';
      } elseif ($name === '' || !isset(CATEGORIES[$category]) || $phone === '' || $city === '') {
        $error = 'Name, category, phone, and city are required.';
      } elseif ($categorySponsor && sponsorTaken($pdo, $category, $isNew ? null : $id)) {
        $error = 'Another business already sponsors this category. Remove their sponsorship first.';
      } else {
        if ($isNew) {
          $stmt = $pdo->prepare('INSERT INTO businesses (id, name, category, phone, city, website, oneliner, description, verified, featured, category_sponsor, markets) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
          $stmt->execute([$id, $name, $category, $phone, $city, $website, $oneliner, $description, $verified, $featured, $categorySponsor, $markets]);
        } else {
          $stmt = $pdo->prepare('UPDATE businesses SET name=?, category=?, phone=?, city=?, website=?, oneliner=?, description=?, verified=?, featured=?, category_sponsor=?, markets=? WHERE id=?');
          $stmt->execute([$name, $category, $phone, $city, $website, $oneliner, $description, $verified, $featured, $categorySponsor, $markets, $id]);
        }
        header('Location: index.php');
        exit;
      }
    } elseif ($action === 'delete') {
      $stmt = $pdo->prepare('DELETE FROM businesses WHERE id = ?');
      $stmt->execute([$_POST['id'] ?? '']);
      header('Location: index.php');
      exit;
    }
  }
}

// --- Data for rendering ---
$editing = null;
if ($authed && isset($_GET['edit'])) {
  $stmt = $pdo->prepare('SELECT * FROM businesses WHERE id = ?');
  $stmt->execute([$_GET['edit']]);
  $editing = $stmt->fetch();
}
$adding = $authed && isset($_GET['add']);

$businesses = $authed ? $pdo->query('SELECT * FROM businesses ORDER BY category, name')->fetchAll() : [];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Admin — The Clearing House</title>
<style>
  body{ font-family: system-ui, sans-serif; max-width: 960px; margin: 40px auto; padding: 0 20px; color: #211C16; }
  table{ width: 100%; border-collapse: collapse; margin-top: 20px; }
  th, td{ text-align: left; padding: 8px 10px; border-bottom: 1px solid #ddd; font-size: 14px; vertical-align: top; }
  th{ background: #f4f1ea; }
  form.inline{ display: inline; }
  input[type=text], input[type=tel], select, textarea{ width: 100%; padding: 6px 8px; margin-bottom: 10px; box-sizing: border-box; }
  textarea{ height: 70px; }
  .field-row{ display: flex; gap: 16px; }
  .field-row > div{ flex: 1; }
  .btn{ padding: 8px 16px; border: none; background: #2B3A4A; color: #fff; cursor: pointer; border-radius: 3px; }
  .btn.danger{ background: #A6432A; }
  .btn.link{ background: none; color: #2B3A4A; text-decoration: underline; padding: 0; }
  .error{ background: #fbe4e0; border: 1px solid #A6432A; padding: 10px 14px; border-radius: 3px; margin-bottom: 16px; }
  .topbar{ display: flex; justify-content: space-between; align-items: center; }
  .status-badge{ display:inline-block; font-size:11px; font-weight:600; padding:2px 7px; border-radius:10px; margin:0 4px 4px 0; }
  .status-badge.verified{ background:#e3ecf3; color:#2B3A4A; }
  .status-badge.featured{ background:#f5e9d3; color:#A6782C; }
  .status-badge.sponsor{ background:#f6dcd4; color:#A6432A; }
</style>
</head>
<body>

<?php if ($error): ?><div class="error"><?= h($error) ?></div><?php endif; ?>

<?php if (!$authed): ?>

  <h1>Admin login</h1>
  <form method="post" style="max-width:320px;">
    <input type="hidden" name="action" value="login">
    <input type="password" name="password" placeholder="Password" required autofocus>
    <button class="btn" type="submit">Log in</button>
  </form>

<?php elseif ($adding || $editing): ?>

  <h1><?= $editing ? 'Edit listing' : 'Add listing' ?></h1>
  <form method="post">
    <input type="hidden" name="action" value="save">
    <input type="hidden" name="csrf" value="<?= h(csrfToken()) ?>">
    <input type="hidden" name="is_new" value="<?= $editing ? '0' : '1' ?>">

    <label>ID (URL slug, lowercase-with-hyphens, cannot be changed later)</label>
    <input type="text" name="id" value="<?= h($editing['id'] ?? '') ?>" <?= $editing ? 'readonly' : 'required' ?>>

    <label>Name</label>
    <input type="text" name="name" value="<?= h($editing['name'] ?? '') ?>" required>

    <div class="field-row">
      <div>
        <label>Category</label>
        <select name="category" required>
          <?php foreach (CATEGORIES as $key => $label): ?>
            <option value="<?= h($key) ?>" <?= (($editing['category'] ?? '') === $key) ? 'selected' : '' ?>><?= h($label) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div>
        <label>Phone</label>
        <input type="tel" name="phone" value="<?= h($editing['phone'] ?? '') ?>" required>
      </div>
      <div>
        <label>City</label>
        <input type="text" name="city" value="<?= h($editing['city'] ?? '') ?>" required>
      </div>
    </div>

    <label>Website (optional — domain or full URL)</label>
    <input type="text" name="website" value="<?= h($editing['website'] ?? '') ?>" placeholder="example.com">

    <label>One-liner (shown on listing cards)</label>
    <input type="text" name="oneliner" value="<?= h($editing['oneliner'] ?? '') ?>">

    <label>Description (shown on the listing page)</label>
    <textarea name="description"><?= h($editing['description'] ?? '') ?></textarea>

    <label><input type="checkbox" name="verified" <?= !empty($editing['verified']) ? 'checked' : '' ?> style="width:auto;"> Verified ($49/mo)</label>
    <label><input type="checkbox" name="featured" <?= !empty($editing['featured']) ? 'checked' : '' ?> style="width:auto;"> Featured (+$49/mo, expects Verified)</label>
    <label><input type="checkbox" name="category_sponsor" <?= !empty($editing['category_sponsor']) ? 'checked' : '' ?> style="width:auto;"> Category Sponsor ($250/mo, 1 per category)</label>

    <?php $editingMarkets = explode(',', $editing['markets'] ?? ''); ?>
    <label>Markets served</label>
    <?php foreach (MARKETS as $key => $label): ?>
      <label><input type="checkbox" name="markets[]" value="<?= h($key) ?>" <?= in_array($key, $editingMarkets, true) ? 'checked' : '' ?> style="width:auto;"> <?= h($label) ?></label>
    <?php endforeach; ?>

    <p>
      <button class="btn" type="submit">Save</button>
      <a href="index.php" class="btn link">Cancel</a>
    </p>
  </form>

<?php else: ?>

  <div class="topbar">
    <h1>Listings</h1>
    <div>
      <a href="?add=1" class="btn" style="text-decoration:none; display:inline-block;">+ Add listing</a>
      <a href="?logout=1">Log out</a>
    </div>
  </div>

  <table>
    <tr><th>Name</th><th>Category</th><th>City</th><th>Markets</th><th>Website</th><th>Status</th><th></th></tr>
    <?php foreach ($businesses as $b): ?>
      <tr>
        <td><?= h($b['name']) ?></td>
        <td><?= h(CATEGORIES[$b['category']] ?? $b['category']) ?></td>
        <td><?= h($b['city']) ?></td>
        <td style="font-size:12px;"><?= h(implode(', ', array_map(function ($slug) { return MARKETS[$slug] ?? $slug; }, array_filter(explode(',', $b['markets'] ?? ''))))) ?: '—' ?></td>
        <td><?= $b['website'] ? '<a href="' . h((preg_match('/^https?:\/\//i', $b['website']) ? '' : 'https://') . $b['website']) . '" target="_blank" rel="noopener noreferrer">' . h($b['website']) . '</a>' : '—' ?></td>
        <td>
          <?php if ($b['category_sponsor']): ?><span class="status-badge sponsor">Sponsor</span><?php endif; ?>
          <?php if ($b['featured']): ?><span class="status-badge featured">Featured</span><?php endif; ?>
          <?php if ($b['verified']): ?><span class="status-badge verified">Verified</span><?php endif; ?>
          <?php if (!$b['verified'] && !$b['featured'] && !$b['category_sponsor']): ?>—<?php endif; ?>
        </td>
        <td>
          <a href="?edit=<?= urlencode($b['id']) ?>">Edit</a>
          &nbsp;·&nbsp;
          <form class="inline" method="post" onsubmit="return confirm('Delete this listing?')">
            <input type="hidden" name="action" value="delete">
            <input type="hidden" name="csrf" value="<?= h(csrfToken()) ?>">
            <input type="hidden" name="id" value="<?= h($b['id']) ?>">
            <button class="btn link" type="submit">Delete</button>
          </form>
        </td>
      </tr>
    <?php endforeach; ?>
  </table>

<?php endif; ?>

</body>
</html>
