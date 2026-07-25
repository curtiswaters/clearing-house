<?php
/**
 * Copy this file to config.php (same folder) and fill in the credentials
 * for the MySQL database created in cPanel → MySQL® Databases.
 * config.php is gitignored — never commit real credentials.
 */

return [
  'host' => 'localhost',
  'db'   => 'cpaneluser_clearinghouse',
  'user' => 'cpaneluser_dbuser',
  'pass' => 'change-me',

  // From your Stripe Dashboard → Developers → API keys / Webhooks.
  // Use test-mode keys (sk_test_.../pk_test_...) until you're ready to go live.
  'stripe_secret_key'      => 'sk_test_...',
  'stripe_publishable_key' => 'pk_test_...',
  'stripe_webhook_secret'  => 'whsec_...',
];
