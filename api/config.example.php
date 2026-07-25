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

  // Contact form (api/contact.php). contact_from_email should be a mailbox
  // on your own domain (e.g. noreply@yourdomain.com) — most cPanel mail
  // setups will flag or reject mail sent "from" an address on another
  // domain. contact_to_email is where messages actually get delivered.
  'contact_to_email'   => 'info@dominatewithbrand.com',
  'contact_from_email' => 'noreply@yourdomain.com',
];
